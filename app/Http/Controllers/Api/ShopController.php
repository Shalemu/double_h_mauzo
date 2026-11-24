<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shops;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shops the signed-in person can work in, e.g. for "Shop" pickers in the
 * app (Add expense). Same list as the web admin pages:
 *   staff → only their own shop;  admin → every shop, by name.
 */
class ShopController extends Controller
{
    /** GET /api/shops → { data: [ { id, name, location, status } ] } */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $shops = Shops::query()
            ->when($user->isStaff(), fn ($q) => $q->whereKey($user->shop_id))
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'status']);

        return response()->json(['data' => $shops]);
    }
}
