<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('invoices')->latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
    ]);

    $supplier = Supplier::create($request->all());

    if ($request->ajax()) {
        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name
        ]);
    }

    return back()->with('success', 'Supplier added successfully.');
}


    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $supplier->update($request->only('name', 'email', 'phone', 'address', 'description'));

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // purchase_invoices cascade-delete with their supplier, so a supplier
        // with history must stay — deleting it would wipe those purchases.
        $invoices = $supplier->invoices()->count();
        if ($invoices) {
            return redirect()->route('suppliers.index')
                ->with('error', "\"{$supplier->name}\" has {$invoices} purchase invoice(s) and cannot be deleted.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier deleted successfully.');
    }
}
