<?php

namespace App\Helpers;

use App\Models\PurchaseInvoice;

/**
 * Purchase invoice number: the one typed in at purchase, otherwise PI-00010.
 */
class InvoiceNumber
{
    public static function for(PurchaseInvoice $invoice): string
    {
        return $invoice->invoice_number ?: 'PI-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT);
    }

    /** Safe for file names: "INV 2026/014" → "INV_2026_014". */
    public static function fileSafe(PurchaseInvoice $invoice): string
    {
        return preg_replace('/[^A-Za-z0-9\-]+/', '_', self::for($invoice));
    }
}
