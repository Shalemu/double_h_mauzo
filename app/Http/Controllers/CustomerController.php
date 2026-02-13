<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    // Return all customers for the dashboard
    public function index()
    {
        $customers = Customer::all(); // Fetch all customers
        return view('dashboard.staff.index', compact('customers'));
    }

    // Store new customer
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Customer::create([
            'name'  => $request->name,
            'phone' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'Customer added successfully!');
    }
}

