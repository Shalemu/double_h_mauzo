<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditPayment;
use App\Models\PurchaseInvoice;

class CreditPaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $invoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);

        if ($request->amount > $invoice->remaining_credit) {
            return back()->with('error', 'Amount exceeds remaining credit.');
        }

        // Create credit payment
        CreditPayment::create([
            'purchase_invoice_id' => $request->purchase_invoice_id,
            'supplier_id' => $request->supplier_id,
            'amount' => $request->amount,
            'paid_at' => now()
        ]);

        // Update invoice remaining credit and amount paid
        $invoice->remaining_credit -= $request->amount;
        $invoice->amount_paid += $request->amount;
        $invoice->save();

        return back()->with('success', 'Deposit applied successfully.');
    }
}