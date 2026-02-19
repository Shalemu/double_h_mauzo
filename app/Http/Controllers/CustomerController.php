<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class CustomerController extends Controller
{
    // Return all customers for the dashboard
    public function index()
    {
        $customers = Customer::all();
        return view('dashboard.staff.index', compact('customers'));
    }

    public function manage()
    {
        $staff = Auth::guard('staff')->user();

        $customers = Customer::where('shop_id', $staff->shop_id)
            ->latest()
            ->get();

        return view('dashboard.staff.customers.index', compact('customers'));
    }

    // Store new customer
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'shop_id' => Auth::guard('staff')->user()->shop_id,
        ]);

        return redirect()->back()->with('success', 'Customer added successfully!');
    }

    // Show customer detail and purchases
    public function show(Customer $customer)
    {
        // Load sales for this customer
        $sales = Sale::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
      $totalPurchases = $sales->sum('total');            // total amount of all sales
        $totalPaid      = $sales->sum('received_amount');  // total paid
        $totalDebt      = $sales->sum('remaining_amount'); // remaining debt

                return view('dashboard.staff.customers.detail', compact(
            'customer',
            'sales',
            'totalPurchases',
            'totalPaid',
            'totalDebt'
        ));
    }

    public function recordPayment(Request $request, Customer $customer)
{
    $validator = Validator::make($request->all(), [
        'amount_paid' => 'required|numeric|min:1'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        $totalDebt = $customer->sales()->sum('remaining_amount');
        $paidAmount = min($request->amount_paid, $totalDebt);

        foreach ($customer->sales()->where('remaining_amount', '>', 0)->orderBy('created_at')->get() as $sale) {
            if ($paidAmount <= 0) break;

            $apply = min($sale->remaining_amount, $paidAmount);
            $sale->received_amount += $apply; // make sure using the correct column
            $sale->remaining_amount -= $apply;

            // Optional: calculate fine
            $dueDate = Carbon::parse($sale->due_date ?? $sale->created_at);
            $today = Carbon::now();
            if ($today->greaterThan($dueDate) && $sale->remaining_amount > 0) {
                $daysLate = $today->diffInDays($dueDate);
                $fine = $daysLate * 100; // 100 per day
                $sale->fine_amount = $fine;
            }

            $sale->save();
            $paidAmount -= $apply;
        }

        $totalPaid = $customer->sales()->sum('received_amount');
        $remainingDebt = $customer->sales()->sum('remaining_amount');

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully!',
            'total_paid' => $totalPaid,
            'remaining_debt' => $remainingDebt
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error processing payment: ' . $e->getMessage()
        ], 500);
    }
}
}