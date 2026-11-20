<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesShop;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerResource;
use App\Http\Resources\Api\SaleResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Customers, for staff and admins alike (see ResolvesShop):
 *   staff → customers of their own shop; admin → any shop (?shop_id=) or all.
 * A customer outside the person's reach answers 404.
 */
class CustomerController extends Controller
{
    use ResolvesShop;

    private const PER_PAGE = 30;
    private const MAX_PER_PAGE = 100;

    /**
     * GET /api/customers?search=asha&has_debt=1&page=1[&shop_id= admin]
     * search: name or phone. has_debt=1: only customers who still owe.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'has_debt' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        $search = trim((string) $request->query('search'));

        $customers = $this->withAccount($this->visibleCustomers($request))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($request->boolean('has_debt'), fn ($q) => $q->whereHas('sales', fn ($s) => $s->where('remaining_amount', '>', 0)))
            ->orderBy('name')
            ->paginate($request->integer('per_page', self::PER_PAGE))
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    /**
     * POST /api/customers — add a customer to the shop
     * (staff: their shop; admin: shop_id required).
     */
    public function store(Request $request): JsonResponse
    {
        $shopId = $this->requiredShopIdFor($request);

        // Sent again by the app (connection dropped before it got our
        // answer)? Hand back the customer already saved — no duplicate.
        $reference = $request->input('client_reference');
        if (is_string($reference) && $reference !== ''
            && ($existing = $this->withAccount($this->visibleCustomers($request))
                ->where('client_reference', $reference)->first())) {
            return response()->json([
                'message' => 'Customer already added.',
                'data'    => new CustomerResource($existing),
            ], 200);
        }

        $data = $request->validate([
            'client_reference' => 'nullable|string|max:64',
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            // Optional — used for automatic birthday SMS wishes.
            'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
            'email'         => 'nullable|email|max:255|unique:customers,email',
            'address'       => 'nullable|string|max:500',
        ]);

        $customer = new Customer($data + ['shop_id' => $shopId]);
        $customer->client_reference = $data['client_reference'] ?? null;
        $customer->save();

        return response()->json([
            'message' => 'Customer added.',
            'data'    => new CustomerResource($customer->load('shop')),
        ], 201);
    }

    /**
     * GET /api/customers/{id} — the customer, their account (purchases,
     * paid, balance) and their latest 20 sales.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $this->withAccount($this->visibleCustomers($request))->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $sales = $customer->sales()->latest()->limit(20)->get();

        return response()->json([
            'data' => new CustomerResource($customer),
            'recent_sales' => SaleResource::collection($sales),
        ]);
    }

    private function visibleCustomers(Request $request)
    {
        $shopId = $this->shopIdFor($request);

        return Customer::query()
            ->with('shop:id,name')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));
    }

    /** Totals of the customer's sales, as on the web customer pages. */
    private function withAccount($query)
    {
        return $query
            ->withSum('sales as total_purchases', 'total')
            ->withSum('sales as total_paid', 'received_amount')
            ->withSum('sales as balance', 'remaining_amount');
    }
}
