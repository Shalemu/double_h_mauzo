<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesShop;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ExpenseResource;
use App\Models\Expenses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Shop expenses from the mobile app — same rules as the web Expenses page:
 *
 *   staff → their own shop, and only the expenses they recorded.
 *   admin → the shop asked for with ?shop_id=, or every shop.
 *
 * Who recorded it is kept the web's way: staff_id for staff, user_id for
 * admins (both point at users.id).
 */
class ExpenseController extends Controller
{
    use ResolvesShop;

    private const PER_PAGE = 30;
    private const MAX_PER_PAGE = 100;

    /**
     * GET /api/expenses?date=2026-11-19 | from=…&to=… &page=1 [&shop_id= admin]
     * Newest first, plus { summary: { count, total } } for the whole filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'date'     => 'nullable|date',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        $query = $this->visibleExpenses($request)
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date('date')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')));

        $summary = (clone $query)->toBase()
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as total')
            ->first();

        $expenses = $query->with(['shop:id,name', 'user', 'staff'])
            ->latest()
            ->paginate($request->integer('per_page', self::PER_PAGE))
            ->withQueryString();

        return ExpenseResource::collection($expenses)->additional(['summary' => [
            'count' => (int) $summary->count,
            'total' => (float) $summary->total,
        ]]);
    }

    /**
     * POST /api/expenses — { title, amount, note?, shop_id (admin only) }.
     * A receipt photo may be sent as multipart "receipt" (jpg/png/pdf, 2 MB).
     */
    public function store(Request $request): JsonResponse
    {
        $shopId = $this->requiredShopIdFor($request);
        $user = $request->user();

        // Sent again by the app (connection dropped before it got our
        // answer)? Hand back the expense already saved — no duplicate.
        $reference = $request->input('client_reference');
        if (is_string($reference) && $reference !== ''
            && ($existing = $this->visibleExpenses($request)->where('client_reference', $reference)->first())) {
            return response()->json([
                'message' => 'Expense already recorded.',
                'data'    => new ExpenseResource($existing->load(['shop:id,name', 'user', 'staff'])),
            ], 200);
        }

        $data = $request->validate([
            'client_reference' => 'nullable|string|max:64',
            'title'   => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0.01',
            'note'    => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $expense = new Expenses([
            'shop_id'  => $shopId,
            'title'    => $data['title'],
            'amount'   => $data['amount'],
            'note'     => $data['note'] ?? null,
            'receipt'  => $request->hasFile('receipt')
                ? $request->file('receipt')->store('receipts', 'public')
                : null,
            'staff_id' => $user->isStaff() ? $user->id : null,
            'user_id'  => $user->isStaff() ? null : $user->id,
        ]);
        // Not in $fillable on purpose: only the API sets it.
        $expense->client_reference = $data['client_reference'] ?? null;
        $expense->save();

        return response()->json([
            'message' => 'Expense recorded.',
            'data'    => new ExpenseResource($expense->load(['shop:id,name', 'user', 'staff'])),
        ], 201);
    }

    /** Expenses this person may see. */
    private function visibleExpenses(Request $request)
    {
        $user = $request->user();
        $shopId = $this->shopIdFor($request);

        return Expenses::query()
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($user->isStaff(), fn ($q) => $q->where('staff_id', $user->id));
    }
}
