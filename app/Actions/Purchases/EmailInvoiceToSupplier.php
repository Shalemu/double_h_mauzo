<?php

namespace App\Actions\Purchases;

use App\Exports\PurchaseInvoicePdf;
use App\Mail\PurchaseInvoiceMail;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email the invoice PDF to the supplier. Never throws: email is a bonus,
 * so a missing address or a mail failure just comes back as a message.
 */
class EmailInvoiceToSupplier
{
    /**
     * @return array{0: bool, 1: string} sent?, message for the user
     */
    public function execute(PurchaseInvoice $invoice): array
    {
        $invoice->loadMissing('supplier');
        $email = $invoice->supplier->email ?? null;
        $name = $invoice->supplier->name ?? 'the supplier';

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, "Invoice not emailed — {$name} has no valid email address."];
        }

        try {
            $pdf = new PurchaseInvoicePdf($invoice);
            Mail::to($email)->send(new PurchaseInvoiceMail($invoice, $pdf->output(), $pdf->filename()));

            return [true, "Invoice emailed to {$name} ({$email})."];
        } catch (\Throwable $e) {
            Log::warning("Purchase invoice #{$invoice->id} email failed: " . $e->getMessage());

            return [false, "Invoice could not be emailed to {$email} — please check the mail settings or try again."];
        }
    }
}
