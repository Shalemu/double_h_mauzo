<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockFilter;
use App\Http\Controllers\Api\Concerns\ResolvesShop;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Products, for staff and admins alike.
 *
 * What each person gets depends on who they are (see ResolvesShop and
 * ProductResource):
 *   staff → their own shop only; never the buying price.
 *   admin → any shop (?shop_id=) or all; buying price with 'prices.view'.
 * A product outside the person's reach answers 404, as if it didn't exist.
 */
class ProductController extends Controller
{
    use ResolvesShop;

    private const PER_PAGE = 30;
    private const MAX_PER_PAGE = 100;

    /**
     * GET /api/products?search=soap&filter=running_out&page=1&per_page=30[&shop_id=3 admin]
     *
     * search    name, brand, barcode, "brand name" or id (same as web POS)
     * filter    all | in_stock | out_of_stock | running_out | expired | expiring
     *           (same rules as the web Product Management lists — StockFilter)
     * in_stock  1 = only products that can be sold now (same as filter=in_stock)
     * Best-stocked first, then by name; expiry lists soonest-expiring first.
     * Paginated: data + links + meta.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'filter'   => ['nullable', Rule::enum(StockFilter::class)],
            'in_stock' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        $filter = StockFilter::fromInput($request->query('filter'));

        $products = $filter->apply($this->visibleProducts($request))
            ->search($request->query('search'))
            ->when($request->boolean('in_stock'), fn ($q) => $q->where('quantity', '>', 0))
            ->when(
                in_array($filter, [StockFilter::Expired, StockFilter::Expiring], true),
                fn ($q) => $q->orderBy('expire_date'),
            )
            ->orderByDesc('quantity')
            ->orderBy('name')
            ->paginate($request->integer('per_page', self::PER_PAGE))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * GET /api/products/summary[?shop_id=3 admin]
     *
     * How many products fall in each stock list, for the app's inventory
     * cards: { data: { all, in_stock, out_of_stock, running_out, expired, expiring } }.
     * Each count uses StockFilter, so it always matches ?filter= and the web.
     */
    public function summary(Request $request): JsonResponse
    {
        $counts = [];
        foreach (StockFilter::cases() as $filter) {
            $counts[$filter->value] = $filter->apply($this->visibleProducts($request))->count();
        }

        return response()->json(['data' => $counts]);
    }

    /**
     * GET /api/products/barcode/{code} — exact barcode, for the camera scanner.
     * Admins must say which shop (?shop_id=): one barcode can exist in several.
     */
    public function barcode(Request $request, string $code): ProductResource|JsonResponse
    {
        if (!$request->user()->isStaff()) {
            $request->validate(['shop_id' => 'required|integer|exists:shops,id'], [
                'shop_id.required' => 'Choose a shop: the same barcode can exist in several shops.',
            ]);
        }

        $product = $this->visibleProducts($request)->where('barcode', $code)->first();

        if (!$product) {
            return response()->json(['message' => 'No product with this barcode in this shop.'], 404);
        }

        return new ProductResource($product);
    }

    /**
     * GET /api/products/{id} — one product, e.g. to refresh its stock.
     */
    public function show(Request $request, int $id): ProductResource|JsonResponse
    {
        $product = $this->visibleProducts($request)->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return new ProductResource($product);
    }

    /**
     * Starting point of every query: only products this person may see.
     * Admins also get the shop on each product, since they see many shops.
     */
    private function visibleProducts(Request $request)
    {
        $user = $request->user();

        // A distributor out on a trip sees only the trip's products, and their
        // stock is what they still carry (never the shop's or store's stock).
        if ($user->isStaff() && ($trip = \App\Models\Distribution::openFor($user->id))) {
            return $this->tripProducts($trip)->with(['unit']);
        }

        $shopId = $this->shopIdFor($request);

        return Products::query()
            ->with($request->user()->isStaff() ? ['unit'] : ['unit', 'shop:id,name'])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));
    }

    /**
     * The trip's products as an ordinary products query, with `quantity`
     * replaced by what the distributor still carries. Built as a sub-table
     * named "products", so search, stock filters and sorting work unchanged.
     */
    private function tripProducts(\App\Models\Distribution $trip)
    {
        $columns = collect(\Illuminate\Support\Facades\Schema::getColumnListing('products'))
            ->reject(fn ($c) => $c === 'quantity')
            ->map(fn ($c) => 'products.' . $c)
            ->all();

        $carried = \Illuminate\Support\Facades\DB::table('products')
            ->join('distribution_items', 'distribution_items.product_id', '=', 'products.id')
            ->where('distribution_items.distribution_id', $trip->id)
            ->select($columns)
            // Signed maths: unsigned columns would error instead of going below zero.
            ->selectRaw('GREATEST(CAST(distribution_items.quantity_taken AS SIGNED) - CAST(distribution_items.quantity_recorded AS SIGNED)'
                . ' - CAST(distribution_items.quantity_returned AS SIGNED), 0) AS quantity');

        return Products::query()->fromSub($carried, 'products');
    }
}
