<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // List all transactions
    public function index()
    {
        $transactions = Transaction::with(['seller', 'supplier', 'customer'])->get();
        return view('dashboard.transactions.index', compact('transactions'));
    }

    // Show form to create transaction
    public function create()
    {
        return view('dashboard.transactions.create');
    }

    // Store a new transaction
    public function store(Request $request)
    {
        $request->validate([
            'total_bill' => 'required|numeric',
            'payment_mode' => 'required|string',
            'transaction_id' => 'required|string|unique:transactions,transaction_id',
        ]);

        Transaction::create([
            'total_bill' => $request->total_bill,
            'payment_mode' => $request->payment_mode,
            'phone' => $request->phone,
            'delivery_mode' => $request->delivery_mode,
            'description' => $request->description,
            'discount_value' => $request->discount_value,
            'discount' => $request->discount,
            'tax_percentage' => $request->tax_percentage,
            'gst_tax' => $request->gst_tax,
            'shipping_value' => $request->shipping_value,
            'shipping' => $request->shipping,
            'due' => $request->due,
            'amount_change' => $request->amount_change,
            'total_payable' => $request->total_payable,
            'sub_total' => $request->sub_total,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status ?? 'pending',
            'sold_by' => Auth::id(),
            'supplied_by' => $request->supplied_by,
            'sold_to' => $request->sold_to,
            'sync_status' => $request->sync_status ?? false,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction added successfully!');
    }
}
