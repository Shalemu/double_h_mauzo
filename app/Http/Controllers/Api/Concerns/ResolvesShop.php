<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;

/**
 * Which shop an API request works on — the one rule shared by every API
 * controller that staff and admins both use (products, customers, sales…):
 *
 *   staff  → always their own shop, taken from the token. A shop_id sent by
 *            the app is ignored, so a staff member can never reach another shop.
 *   admin  → the shop asked for with ?shop_id=, or every shop (null).
 */
trait ResolvesShop
{
    protected function shopIdFor(Request $request): ?int
    {
        $user = $request->user();

        if ($user->isStaff()) {
            // Never fall through to "every shop": a staff member whose shop
            // was removed after they signed in gets nothing at all.
            abort_unless($user->shop_id, 403, 'Your account is not assigned to a shop. Contact your supervisor.');

            return (int) $user->shop_id;
        }

        $request->validate(['shop_id' => 'nullable|integer|exists:shops,id']);

        return $request->filled('shop_id') ? $request->integer('shop_id') : null;
    }

    /**
     * For creating something (a customer, a sale) there must be exactly one
     * shop: staff → their own; admin → must send shop_id.
     */
    protected function requiredShopIdFor(Request $request): int
    {
        if (!$request->user()->isStaff()) {
            $request->validate(['shop_id' => 'required|integer|exists:shops,id'], [
                'shop_id.required' => 'Choose the shop this is for.',
            ]);
        }

        return $this->shopIdFor($request);
    }
}
