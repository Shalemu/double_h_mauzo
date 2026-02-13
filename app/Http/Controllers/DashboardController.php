<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class DashboardController extends Controller
{
    // Admin dashboard
    public function index()
    {
        return view('dashboard.admin.index'); 
    }

    // Staff dashboard (POS)
    public function staff()
    {
        $staff = Auth::guard('staff')->user();

        $customers = Customer::all();  
        $products = Products::where('shop_id', $staff->shop_id)->get();

        return view('dashboard.staff.index', compact('products', 'customers'));
    }
}
