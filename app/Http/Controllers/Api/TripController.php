<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Distribution;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * GET /api/trip — the distributor's open trip (staff token), or data: null.
     *
     * products: the trip's products in the usual product shape, where `stock`
     * is what the distributor still carries. Sell them with POST /api/sales
     * and "distribution_id": <trip id>. summary: what they've sold so far.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $trip = $user->isStaff() ? Distribution::openFor($user->id) : null;

        if (!$trip) {
            return response()->json(['data' => null, 'message' => 'No trip out right now.']);
        }

        $trip->load('store', 'items');
        $left = $trip->items->mapWithKeys(fn ($item) => [$item->product_id => $item->quantity_left]);
        $products = Products::with('unit')->whereIn('id', $left->keys())->get()
            ->each(fn ($p) => $p->setAttribute('quantity', $left[$p->id] ?? 0)) // display only, never saved
            ->sortByDesc('quantity')->values();

        $sales = $trip->posSales();

        return response()->json(['data' => [
            'id'        => $trip->id,
            'store'     => ['id' => $trip->store_id, 'name' => $trip->store->name ?? null],
            'out_since' => $trip->created_at?->toIso8601String(),
            'summary'   => [
                'sales_count'    => (int) (clone $sales)->count(),
                'sold_total'     => (float) (clone $sales)->sum('total'),
                'cash_collected' => $trip->posCashCollected(),
                'on_credit'      => (float) (clone $sales)->sum('remaining_amount'),
                'units_left'     => (int) $left->sum(),
            ],
            'products'  => ProductResource::collection($products)->resolve($request),
        ]]);
    }
}
