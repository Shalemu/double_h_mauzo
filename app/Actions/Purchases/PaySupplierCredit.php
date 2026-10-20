<?php

namespace App\Actions\Purchases;

use App\Models\CreditPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pay part (or all) of what is still owed on a credit purchase.
 */
class PaySupplierCredit
{
    public function execute(PurchaseInvoice $invoice, float $amount): CreditPayment
    {
        if ($amount > (float) $invoice->remaining_amount) {
            throw ValidationException::withMessages(['amount' => 'Amount exceeds remaining credit.']);
        }

        return DB::transaction(function () use ($invoice, $amount) {
            $invoice->remaining_amount -= $amount;
            $invoice->amount_paid += $amount;
            $invoice->save();

            return CreditPayment::create([
                'purchase_invoice_id' => $invoice->id,
                'supplier_id' => $invoice->supplier_id,
                'amount' => $amount,
                'paid_at' => now(),
            ]);
        });
    }
}
