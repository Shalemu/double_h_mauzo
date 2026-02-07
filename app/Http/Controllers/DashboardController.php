<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;

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

        $products = Products::where('shop_id', $staff->shop_id)->get();

        return view('dashboard.staff.index', compact('products'));
    }

    // Shops list
    public function shopDashboard()
    {
        $shops = Shops::with('staff')->get();
        return view('dashboard.shops.shop', compact('shops'));
    }

    // Single shop dashboard
    public function showShop(Request $request, $id)
    {
        $shop = Shops::with('staff')->findOrFail($id);
        return view('dashboard.dashboard', compact('shop'));
    }
}
