<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpensesController extends Controller
{

    public function index($shopId)
    {
        $shop = Shops::findOrFail($shopId);

        $query = Expenses::where('shop_id', $shop->id);

        // Staff → only their expenses
        if (Auth::guard('staff')->check()) {
            $query->where('staff_id', Auth::guard('staff')->user()->id);
        }
        // User → only their expenses
        elseif (Auth::guard('web')->check()) {
            $query->where('user_id', Auth::guard('web')->user()->id);
        }

        $expensesByDate = $query->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($expense) => $expense->created_at->format('Y-m-d'))
            ->map(fn($expenses, $date) => [
                'date'  => $date,
                'total' => $expenses->sum('amount'),
                'items' => $expenses,
            ]);

        return view('dashboard.staff.expenses.index', compact('shop', 'expensesByDate'));
    }

    /**
     * Show expenses for a specific date.
     */
public function details(Request $request, $shopId)
{
    $shop = Shops::findOrFail($shopId);
    $date = $request->query('date');

    $query = Expenses::where('shop_id', $shop->id)
                     ->whereDate('created_at', $date);

    // Staff → only their own expenses
    if (Auth::guard('staff')->check()) {
        $query->where('staff_id', Auth::guard('staff')->user()->id);
    }
 

    $expenses = $query->orderBy('created_at', 'desc')->get();

    return view('dashboard.staff.expenses.detail', compact('expenses', 'date', 'shop'));
}

 
    public function create($shopId)
    {
        $shop = Shops::findOrFail($shopId);
        return view('dashboard.expenses.create', compact('shop'));
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request, $shopId)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
            'note'    => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $shop = Shops::findOrFail($shopId);

        // Handle receipt upload
        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('receipts', 'public')
            : null;

        // Correctly get numeric IDs for staff or user
        $staffId = Auth::guard('staff')->check() ? Auth::guard('staff')->user()->id : null;
        $userId  = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : null;

        Expenses::create([
            'shop_id'  => $shop->id,
            'title'    => $request->title,
            'amount'   => $request->amount,
            'note'     => $request->note,
            'receipt'  => $receiptPath,
            'staff_id' => $staffId,
            'user_id'  => $userId,
        ]);

        return redirect()->route('dashboard.shop.show', $shop->id)
                         ->with('success', 'Expense added successfully!');
    }

    /**
     * Show form to edit an expense.
     */
    public function edit($id)
    {
        $expense = Expenses::findOrFail($id);
        $shop = $expense->shop;

        return view('dashboard.expenses.edit', compact('expense', 'shop'));
    }

    /**
     * Update an existing expense.
     */
    public function update(Request $request, $id)
    {
        $expense = Expenses::findOrFail($id);

        $request->validate([
            'title'   => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
            'note'    => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Replace receipt if a new file uploaded
        if ($request->hasFile('receipt')) {
            if ($expense->receipt && Storage::disk('public')->exists($expense->receipt)) {
                Storage::disk('public')->delete($expense->receipt);
            }
            $expense->receipt = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update([
            'title'   => $request->title,
            'amount'  => $request->amount,
            'note'    => $request->note,
            'receipt' => $expense->receipt,
        ]);

        return redirect()->route('dashboard.shop.show', $expense->shop_id)
                         ->with('success', 'Expense updated successfully!');
    }

    /**
     * Delete an expense.
     */
    public function destroy($id)
    {
        $expense = Expenses::findOrFail($id);

        if ($expense->receipt && Storage::disk('public')->exists($expense->receipt)) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $shopId = $expense->shop_id;
        $expense->delete();

        return redirect()->route('dashboard.shop.show', $shopId)
                         ->with('success', 'Expense deleted successfully!');
    }
}
