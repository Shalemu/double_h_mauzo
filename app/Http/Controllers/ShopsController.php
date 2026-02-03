<?php

namespace App\Http\Controllers;

use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopsController extends Controller
{
    /**
     * Display all shops with staff.
     */
    public function index()
    {
        $shops = Shops::with('staff')->orderBy('name')->get();
        return view('dashboard.shops.shop', compact('shops'));
    }

    /**
     * Store a new shop.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capital'  => 'nullable|numeric|min:0',
        ]);

        Shops::create([
            'name'     => $request->name,
            'location' => $request->location,
            'capital'  => $request->capital ?? 0,
            'admin_id' => Auth::id(),
        ]);

        return redirect()->route('shops.index')
                         ->with('success', 'Shop added successfully!');
    }

    public function show(Shops $shop)
{
    $shop->load('staff'); // eager load staff

    return view('dashboard.shops.show', compact('shop'));
}

}
