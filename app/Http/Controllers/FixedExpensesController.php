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

    return back()->with('success', 'Fixed expense added successfully!');
}


    // Update fixed expense
    public function update(Request $request,$id)
    {
        $expense = FixedExpense::findOrFail($id);

        $request->validate([
            'title'=>'required|string|max:255',
            'amount'=>'required|numeric|min:0',
            'note'=>'nullable|string'
        ]);

        $expense->update([
            'title'=>$request->title,
            'amount'=>$request->amount,
            'note'=>$request->note
        ]);

    return back()->with('success','Expense updated successfully');
    }
    // Delete fixed expense
    public function destroy($id)
{
    $expense = FixedExpense::findOrFail($id);

    $expense->delete();

    return back()->with('success', 'Fixed expense deleted successfully!');
}

}
