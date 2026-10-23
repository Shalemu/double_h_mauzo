<?php

namespace App\Mail;

use App\Models\PurchaseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Purchase invoice sent to the supplier, with the PDF attached.
 */
class PurchaseInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseInvoice $invoice, public string $pdf, public string $filename)
    {
    }

    public function build()
    {
        $number = \App\Helpers\InvoiceNumber::for($this->invoice);

        $kind = $this->invoice->isPending() ? 'Purchase Order' : 'Purchase Invoice';

        return $this->subject("{$kind} {$number} — Double H Cosmetics")
            ->view('emails.purchase_invoice', ['invoice' => $this->invoice, 'number' => $number])
            ->attachData($this->pdf, $this->filename, ['mime' => 'application/pdf']);
    }
}
