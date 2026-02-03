<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;

class DashboardController extends Controller
{
    // Main summary dashboard
    public function index()
    {
        // This is dashboard.index
        return view('dashboard.index'); 
    }

    // Show all shops (My Business)
    public function shopDashboard()
    {
        $shops = Shops::with('staff')->get(); // fetch all shops
        return view('dashboard.shops.shop', compact('shops'));
    }

    // Show specific shop dashboard
    public function showShop(Request $request, $id)
    {
        $shop = Shops::with('staff')->findOrFail($id); 
        return view('dashboard.dashboard', compact('shop')); // dashboard.dashboard includes show.blade.php
    }
}
