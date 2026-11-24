<?php

namespace App\Http\Controllers\Api;

use App\Actions\Sales\CheckoutSale;
use App\DTO\Sales\SaleData;
use App\DTO\Sales\SaleItemData;
use App\Models\PaymentMethod as PaymentMethodSetting;
use App\Http\Controllers\Api\Concerns\ResolvesShop;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SaleResource;
use App\Models\Customer;
use App\Models\Distribution;
use App\Models\Products;
use App\Models\Sale;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/**
 * Selling from the mobile app, for staff and admins alike.
 *
 *   staff → sell in their own shop; see only the sales they made.
 *   admin → sell in the shop they name (shop_id); see every shop's sales.
 *
 * Checkout goes through the same CheckoutSale action as the web POS, so the
 * rules are identical (stock, no selling below the buying price, credit).
 * On top of that the API never trusts prices from the phone, and a sale sent
 * twice (same client_reference) is only recorded once.
 */
class SaleController extends Controller
{
    use ResolvesShop;

    private const PER_PAGE = 30;
    private const MAX_PER_PAGE = 100;

    /**
     * POST /api/sales — checkout.
     *
     * {
     *   "client_reference": "8f14e45f-...",     unique per sale, made by the app
     *   "distribution_id": 7,                   distributor selling from their trip (GET /api/trip)
     *   "shop_id": 3,                           admin only
     *   "customer_id": 12,                      required for credit
     *   "payment_method_id": 4,                 from GET /api/payment-methods (preferred)
     *   "payment_method": "cash|mobile|bank|credit",   only when no payment_method_id
     *   "payment_type": "M-Pesa",               optional label, only when no payment_method_id
     *   "received_amount": 20000,               cash: at least the total
     *   "bill_discount": 0, "shipping": 0,
     *   "items": [{"product_id": 5, "qty": 2, "discount": 0, "sale_type": "retail"}]
     * }
     * The price of each item is taken from the product, never from the app.
     */
    public function store(Request $request, CheckoutSale $checkout): JsonResponse
    {
        $user = $request->user();

        // A distributor selling from their trip (GET /api/trip): the sale is from the
        // trip's store, and stock is what they carry.
        $trip = null;
        if ($request->filled('distribution_id')) {
            $trip = Distribution::find($request->integer('distribution_id'));
            if (!$trip || !$user->isStaff() || (int) $trip->staff_id !== (int) $user->id) {
                throw ValidationException::withMessages(['distribution_id' => 'This trip is not yours.']);
            }
            if (!$trip->isOut()) {
                throw ValidationException::withMessages(['distribution_id' => 'This trip has been settled. Ask your supervisor for a new one.']);
            }
        } elseif ($user->isStaff()) {
            // Out on a trip but the app didn't say so (e.g. an older app version):
            // they can only sell what they carry, so it's a trip sale.
            $trip = Distribution::openFor($user->id);
        }
        $shopId = $trip ? (int) $trip->store_id : $this->requiredShopIdFor($request);

        $data = $request->validate([
            'distribution_id'    => 'nullable|integer',
            'client_reference'   => 'nullable|string|max:64',
            'customer_id'        => 'nullable|integer',
            'payment_method_id'  => 'nullable|integer',
            'payment_method'     => 'required_without:payment_method_id|nullable|in:cash,mobile,bank,credit',
            'payment_type'       => 'nullable|string|max:50',
            'received_amount'    => 'nullable|numeric|min:0',
            'bill_discount'      => 'nullable|numeric|min:0',
            'shipping'           => 'nullable|numeric|min:0',
            'items'              => 'required|array|min:1|max:200',
            // The same product may appear twice: once retail, once wholesale
            // (checked below). CheckoutSale locks and re-reads the product for
            // every line, so the stock check covers both lines together.
            'items.*.product_id' => 'required|integer',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'items.*.sale_type'  => 'nullable|in:retail,wholesale',
        ]);
        $lines = [];
        foreach ($data['items'] as $i => $item) {
            $key = $item['product_id'] . ':' . ($item['sale_type'] ?? 'retail');
            if (isset($lines[$key])) {
                throw ValidationException::withMessages(["items.$i.product_id" => 'The same product is listed twice at the same price type.']);
            }
            $lines[$key] = true;
        }

        $reference = $data['client_reference'] ?? null;

        // Sent again (e.g. the connection dropped before the app got our
        // answer)? Hand back the sale already recorded — don't sell twice.
        if ($reference && ($existing = Sale::where('client_reference', $reference)->first())) {
            return $this->alreadyRecorded($existing, $request);
        }

        // On a trip the customer may be the store's or the distributor's own shop's.
        $customerShops = $trip ? array_filter([$shopId, $user->shop_id]) : [$shopId];
        if (!empty($data['customer_id'])
            && !Customer::whereKey($data['customer_id'])->whereIn('shop_id', $customerShops)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'This customer does not belong to this shop.']);
        }

        [$paymentCategory, $paymentLabel, $paymentMethodId] = PaymentMethodSetting::resolveForSale($data);

        $sale = new SaleData(
            shopId: $shopId,
            items: $this->pricedItems($data['items'], $shopId),
            paymentMethod: $paymentCategory,
            received: (float) ($data['received_amount'] ?? 0),
            billDiscount: (float) ($data['bill_discount'] ?? 0),
            shipping: (float) ($data['shipping'] ?? 0),
            customerId: !empty($data['customer_id']) ? (int) $data['customer_id'] : null,
            paymentType: $paymentLabel,
            staffId: $user->isStaff() ? $user->id : null,
            adminId: $user->isStaff() ? null : $user->id,
            clientReference: $reference,
            paymentMethodId: $paymentMethodId,
        );
        if ($trip) {
            $sale = $sale->onDistribution($trip->id);
        }

        try {
            $recorded = $checkout->execute($sale);
        } catch (\DomainException $e) {
            // A sales rule said no (stock, loss, cash short, no customer…).
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (QueryException $e) {
            // Two copies of the same sale arrived at the same moment: the
            // unique client_reference stopped the second one (nothing of it
            // was saved — the whole checkout was rolled back).
            if ($reference && ($e->errorInfo[1] ?? null) === 1062
                && ($existing = Sale::where('client_reference', $reference)->first())) {
                return $this->alreadyRecorded($existing, $request);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Sale recorded.',
            'data'    => new SaleResource($this->forReceipt($recorded)),
        ], 201);
    }

    /**
     * GET /api/sales?date=2026-11-18 | from=…&to=… &page=1 [&shop_id= admin]
     * Newest first, plus a summary of everything matching the filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'date'     => 'nullable|date',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        $query = $this->visibleSales($request)
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date('date')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')));

        $summary = (clone $query)->toBase()
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total, COALESCE(SUM(received_amount),0) as received, COALESCE(SUM(remaining_amount),0) as balance')
            ->first();

        $sales = $query->with(['customer', 'staff', 'admin'])
            ->latest()
            ->paginate($request->integer('per_page', self::PER_PAGE))
            ->withQueryString();

        return SaleResource::collection($sales)->additional(['summary' => [
            'count'    => (int) $summary->count,
            'total'    => (float) $summary->total,
            'received' => (float) $summary->received,
            'balance'  => (float) $summary->balance,
        ]]);
    }

    /**
     * GET /api/sales/{id} — one sale with its items: everything for a receipt.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $sale = $this->visibleSales($request)->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found.'], 404);
        }

        return response()->json(['data' => new SaleResource($this->forReceipt($sale))]);
    }

    /**
     * The cart with prices from the products themselves. Every product must
     * belong to the shop, and have a price for the chosen sale type.
     *
     * @return SaleItemData[]
     */
    private function pricedItems(array $items, int $shopId): array
    {
        $products = Products::where('shop_id', $shopId)
            ->whereIn('id', array_column($items, 'product_id'))
            ->get()
            ->keyBy('id');

        return array_map(function ($item, $i) use ($products) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                throw ValidationException::withMessages(["items.$i.product_id" => 'This product is not in this shop.']);
            }

            $type = $item['sale_type'] ?? 'retail';
            $price = (float) ($type === 'wholesale' ? $product->wholesale_price : $product->selling_price);
            if ($price <= 0) {
                throw ValidationException::withMessages(["items.$i.product_id" => "{$product->name} has no {$type} price set."]);
            }

            return SaleItemData::fromArray([
                'product_id' => $product->id,
                'qty'        => $item['qty'],
                'price'      => $price,
                'discount'   => $item['discount'] ?? 0,
                'sale_type'  => $type,
            ]);
        }, $items, array_keys($items));
    }

    /**
     * Sales this person may see: staff → only the ones they made, in their
     * shop; admin → the chosen shop, or every shop.
     */
    private function visibleSales(Request $request)
    {
        $user = $request->user();
        $request->validate(['distribution_id' => 'nullable|integer']);

        if ($user->isStaff()) {
            // Their own sales: in their shop, plus the ones made on their distributor trips
            // (those are from the trip's store). ?distribution_id= narrows to one trip.
            return Sale::query()
                ->where('staff_id', $user->id)
                ->where(function ($q) use ($user) {
                    // A distributor may have no shop of their own: then only trip sales.
                    $user->shop_id
                        ? $q->where('shop_id', $user->shop_id)->orWhereNotNull('distribution_id')
                        : $q->whereNotNull('distribution_id');
                })
                ->when($request->filled('distribution_id'), fn ($q) => $q->where('distribution_id', $request->integer('distribution_id')));
        }

        $shopId = $this->shopIdFor($request);

        return Sale::query()
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($request->filled('distribution_id'), fn ($q) => $q->where('distribution_id', $request->integer('distribution_id')));
    }

    /** A retried sale: answer with the original, if it is this person's to see. */
    private function alreadyRecorded(Sale $sale, Request $request): JsonResponse
    {
        if (!$this->visibleSales($request)->whereKey($sale->id)->exists()) {
            return response()->json(['message' => 'This client_reference was already used for another sale.'], 409);
        }

        return response()->json([
            'message' => 'This sale was already recorded.',
            'data'    => new SaleResource($this->forReceipt($sale)),
        ], 200);
    }

    private function forReceipt(Sale $sale): Sale
    {
        return $sale->load([
            'items.product' => fn ($q) => $q->withTrashed(), // receipts keep deleted products' names
            'customer', 'shop', 'staff', 'admin',
        ]);
    }
}
