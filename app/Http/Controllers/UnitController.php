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
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $units = Unit::withCount('products')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('short_name', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.units.index', compact('units', 'search'));
    }

    /**
     * Store unit
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
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

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'short_name' => 'required|string|max:10',
        ]);

        $unit->update($request->only('name', 'short_name'));

        return redirect()->back()->with('success', 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        $inUse = $unit->products()->count();
        if ($inUse) {
            return redirect()->back()->with('error', "\"{$unit->name}\" is used by {$inUse} product(s) and cannot be deleted.");
        }

        $unit->delete();

        return redirect()->back()->with('success', 'Unit deleted successfully');
    }
}
