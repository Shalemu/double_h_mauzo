<?php

namespace App\Actions\Distribution;

use App\Models\Distribution;
use App\Models\DistributionPayment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cash a distributor hands in later against what they still owe; the sale's
 * credit moves with it so Sales/Credit reports agree.
 */
class RecordDistributionPayment
{
    public function execute(Distribution $distribution, float $amount, ?string $note = null, ?int $receivedBy = null): DistributionPayment
    {
        return DB::transaction(function () use ($distribution, $amount, $note, $receivedBy) {
            $distribution = Distribution::lockForUpdate()->findOrFail($distribution->id);

            if ($amount > (float) $distribution->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment is more than the balance (TZS ' . number_format($distribution->balance, 2) . ').',
                ]);
            }

            $payment = $distribution->payments()->create([
                'amount' => $amount,
                'received_by' => $receivedBy,
                'note' => $note,
            ]);

            $distribution->update([
                'amount_received' => $distribution->amount_received + $amount,
                'balance' => $distribution->balance - $amount,
            ]);

            if ($sale = Sale::lockForUpdate()->find($distribution->sale_id)) {
                $sale->update([
                    'received_amount' => $sale->received_amount + $amount,
                    'remaining_amount' => max(0, $sale->remaining_amount - $amount),
                ]);
            }

            return $payment;
        });
    }
}
