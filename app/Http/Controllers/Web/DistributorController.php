<?php

namespace App\Http\Controllers\Web;

use App\Actions\Sales\CheckoutSale;
use App\DTO\Sales\SaleData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SaleResource;
use App\Models\Distribution;
use App\Models\Sale;
use App\Models\Staff;
use Illuminate\Http\Request;

/**
 * The distributor's own side of a trip (staff login):
 *   - checkout from the POS while out with stock (each sale recorded at once,
 *     taken from what they carry, not from the store again);
 *   - "Trip": what they carry, what they've sold, and past trips.
 * Issuing and settling trips stay with supervisors (DistributionController).
 */
class DistributorController extends Controller
{
    /** POST /dashboard/staff/trip/{distribution}/checkout — a sale on the trip. */
    public function checkout(Request $request, Distribution $distribution, CheckoutSale $checkoutSale)
    {
        $staff = Staff::current();
        abort_unless($staff && (int) $distribution->staff_id === (int) $staff->id, 403, 'This trip is not yours.');

        if (!$distribution->isOut()) {
            return response()->json(['success' => false, 'message' => 'This trip has been settled. Ask your supervisor for a new one.'], 422);
        }
        if (empty($request->input('cart', []))) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.']);
        }

        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method_id' => 'nullable|integer',
            'payment_method' => 'required_without:payment_method_id|nullable|string',
            'payment_type' => 'nullable|string',
            'received_amount' => 'nullable|numeric|min:0',
            'bill_discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
        ]);

        // The sale is from the trip's store (where the stock came from), made by the distributor.
        $saleData = SaleData::fromArray((int) $distribution->store_id, $validated, $staff->id)->onDistribution($distribution->id);

        try {
            $sale = $checkoutSale->execute($saleData);
        } catch (\Throwable $e) {
            if (!$e instanceof \DomainException) {
                report($e);
            }
            return response()->json(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }

        $sale->load(['items.product' => fn ($q) => $q->withTrashed(), 'customer', 'shop', 'staff', 'admin']);

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'message' => 'Sale completed successfully!',
            'data' => (new SaleResource($sale))->resolve($request),
        ]);
    }

    /** GET /dashboard/staff/trip — what I carry, what I've sold, past trips. */
    public function mine()
    {
        $staff = Staff::current();
        abort_unless($staff, 403);

        $trip = Distribution::openFor($staff->id);
        $trip?->load('store', 'items');

        $sales = $trip
            ? Sale::with('customer', 'items')->where('distribution_id', $trip->id)
                ->where('payment_method', '!=', \App\Enums\PaymentMethod::Distribution->value)
                ->latest()->get()
            : collect();

        $pastTrips = Distribution::with('store')->withCount('posSales')
            ->where('staff_id', $staff->id)
            ->where('status', '!=', \App\Enums\DistributionStatus::Out->value)
            ->latest('settled_at')->limit(20)->get();

        return view('pages.staff.trip', compact('trip', 'sales', 'pastTrips'));
    }
}
