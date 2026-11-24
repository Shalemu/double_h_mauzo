<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An expense as the mobile app sees it.
 *
 * @mixin \App\Models\Expenses
 */
class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $creator = $this->staff_id ? $this->staff : $this->user;

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'amount'      => (float) $this->amount,
            'note'        => $this->note,
            'receipt_url' => $this->receipt ? asset('storage/' . $this->receipt) : null,
            'date'        => $this->created_at?->toIso8601String(),
            'created_by'  => $creator ? trim($creator->first_name . ' ' . $creator->last_name) : null,
            // Admins see expenses of many shops.
            'shop'        => $this->when(
                $user && !$user->isStaff(),
                fn () => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name] : null,
            ),
        ];
    }
}
