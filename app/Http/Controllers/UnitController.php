<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * List units
     */
    public function index()
    {
        $units = Unit::all();
        return view('units.index', compact('units'));
    }

    /**
     * Show create unit form
     */
    public function create()
    {
        return view('units.create');
    }

    /**
     * Store unit
     */
   public function store(Request $request)
{
    $request->validate([
        'name'       => 'required|string',
        'short_name' => 'required|string|max:10',
        'shop_id'    => 'nullable|integer',
    ]);

    Unit::create([
        'name'       => $request->name,
        'short_name' => $request->short_name,
        'shop_id'    => $request->shop_id,
        'admin_id'   => Auth::id(),
    ]);

    // stay on the same page with success message
    return redirect()->back()->with('success', 'Unit created successfully');
}

}
