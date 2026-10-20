<?php

namespace App\Http\Controllers;

use App\Actions\Distribution\IssueDistribution;
use App\Actions\Distribution\RecordDistributionPayment;
use App\Actions\Distribution\SettleDistribution;
use App\DataTables\DistributionsDataTable;
use App\DTO\Distribution\DistributionData;
use App\DTO\Distribution\SettlementData;
use App\Enums\DistributionStatus;
use App\Models\Distribution;
use App\Models\Products;
use App\Models\Shops;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Distributor trips. Issuing takes stock out of a store; settling puts the
 * returned units back and records the rest as a sale from that store, with
 * any cash the distributor didn't hand in kept as their balance.
 */
class DistributionController extends Controller
{
    public function index(Request $request)
    {
        $table = DistributionsDataTable::make($request);

        return view('pages.distributions.index', [
            'distributions' => $table->paginate(),
            'stores' => Shops::stores()->orderBy('name')->get(['id', 'name']),
            'staff' => Staff::with('shop:id,name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'shop_id']),
            'storeId' => $table->storeId(),
            'status' => $table->status(),
            'totals' => [
                'out' => Distribution::where('status', DistributionStatus::Out->value)->count(),
                'owing' => (float) Distribution::sum('balance'),
            ],
        ]);
    }

    public function show(Distribution $distribution)
    {
        $distribution->load('store', 'staff', 'issuedBy', 'settledBy', 'items', 'payments.receivedBy');

        return view('pages.distributions.show', compact('distribution'));
    }

    public function productSearch(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2 || !$request->query('store')) {
            return response()->json([]);
        }

        return Products::where('shop_id', $request->query('store'))
            ->where('quantity', '>', 0)
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('barcode', $term))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'barcode', 'quantity', 'selling_price']);
    }

    /**
     * Issue stock to a distributor: it leaves the store now.
     */
    public function store(Request $request, IssueDistribution $issue)
    {
        $items = json_decode((string) $request->items, true);
        $request->merge(['items' => is_array($items) ? $items : []]);

        $validated = $request->validate([
            'store_id' => 'required|exists:shops,id',
            'staff_id' => 'required|exists:staff,id',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'Add at least one product for the distributor to take.',
        ]);

        $distribution = $issue->execute(DistributionData::fromArray($validated), Auth::id());

        return redirect()->route('distributions.show', $distribution)
            ->with('success', "Distribution #{$distribution->id} issued: {$distribution->items()->sum('quantity_taken')} unit(s) left the store.");
    }

    /**
     * Distributor is back: returned units go into the store, the rest is a sale.
     */
    public function settle(Request $request, Distribution $distribution, SettleDistribution $settle)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.returned' => 'required|integer|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'amount_received' => 'required|numeric|min:0',
        ]);

        $distribution = $settle->execute($distribution, SettlementData::fromArray($validated), Auth::id());

        $message = "Distribution #{$distribution->id} settled. Sold: TZS " . number_format($distribution->total_value, 2)
            . ($distribution->balance > 0 ? ' — still owed by distributor: TZS ' . number_format($distribution->balance, 2) : '.');

        return redirect()->route('distributions.show', $distribution)->with('success', $message);
    }

    /**
     * Cash the distributor hands in later against their balance.
     */
    public function pay(Request $request, Distribution $distribution, RecordDistributionPayment $recordPayment)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $recordPayment->execute($distribution, (float) $data['amount'], $data['note'] ?? null, Auth::id());

        return redirect()->route('distributions.show', $distribution)
            ->with('success', 'Payment of TZS ' . number_format($data['amount'], 2) . ' recorded.');
    }
}
