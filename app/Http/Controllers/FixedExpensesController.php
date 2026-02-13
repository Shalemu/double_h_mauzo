<?php

namespace App\Http\Controllers;

use App\Models\FixedExpense;
use App\Models\Shops;
use Illuminate\Http\Request;

class FixedExpensesController extends Controller
{
    // Show all fixed expenses for a shop
    public function index($shopId)
    {
        $shop = Shops::findOrFail($shopId);
        $fixedExpenses = FixedExpense::where('shop_id', $shop->id)->get();

        return view('dashboard.fixed_expenses.index', compact('shop', 'fixedExpenses'));
    }

    // Show form to create a new fixed expense
    public function create($shopId)
    {
        $shop = Shops::findOrFail($shopId);
        return view('dashboard.fixed_expenses.create', compact('shop'));
    }

    // Store new fixed expense
    public function store(Request $request, $shopId)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'note'   => 'nullable|string',
        ]);

        FixedExpense::create([
            'shop_id' => $shopId,
            'title'   => $request->title,
            'amount'  => $request->amount,
            'note'    => $request->note,
        ]);

                 
        return redirect()->route('dashboard.shop.show', $shopId)
             ->with('success', 'Fixed expense added successfully!');
    }

    // Show form to edit fixed expense
    public function edit($id)
    {
        $expense = FixedExpense::findOrFail($id);
        $shop = $expense->shop;

        return view('dashboard.fixed_expenses.edit', compact('expense', 'shop'));
    }

    // Update fixed expense
    public function update(Request $request, $id)
    {
        $expense = FixedExpense::findOrFail($id);

        $request->validate([
            'title'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'note'   => 'nullable|string',
        ]);

        $expense->update([
            'title'  => $request->title,
            'amount' => $request->amount,
            'note'   => $request->note,
        ]);

        return redirect()->route('fixed-expenses.index', $expense->shop_id)
                         ->with('success', 'Fixed expense updated successfully!');
    }

    // Delete fixed expense
    public function destroy($id)
    {
        $expense = FixedExpense::findOrFail($id);
        $shopId = $expense->shop_id;
        $expense->delete();

     return redirect()->route('dashboard.shop.show', $shopId)
                         ->with('success', 'Fixed expense deleted successfully!');
    }
}
