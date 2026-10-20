<?php

namespace App\Exports;

use App\Helpers\InvoiceNumber;
use App\Models\PurchaseInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * A purchase invoice as PDF — for download, the Share button and the
 * supplier email. The same template renders the on-screen preview.
 */
class PurchaseInvoicePdf
{
    public function __construct(private PurchaseInvoice $invoice)
    {
        $this->invoice->loadMissing('shop', 'supplier', 'items.product.unit');
    }

    public function filename(): string
    {
        return 'Purchase_Invoice_' . InvoiceNumber::fileSafe($this->invoice) . '.pdf';
    }

    /** Raw PDF bytes (used as the email attachment). */
    public function output(): string
    {
        return Pdf::loadView('pages.purchases.invoice_pdf', ['invoice' => $this->invoice])
            ->setPaper('a4')
            ->output();
    }

    /** HTTP response; $inline shows it in the browser instead of downloading. */
    public function response(bool $inline = false)
    {
        return response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $this->filename() . '"',
        ]);
    }
}
