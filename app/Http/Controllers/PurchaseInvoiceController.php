<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseInvoice;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with('supplier', 'shop')
            ->latest()
            ->get();

        return view('dashboard.invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $invoice = PurchaseInvoice::with('items', 'payments', 'supplier')
            ->findOrFail($id);

        return view('dashboard.invoices.show', compact('invoice'));
    }
}