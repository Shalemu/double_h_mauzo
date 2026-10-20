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

        $units = Unit::with('baseUnit')->withCount('products', 'multiples')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('short_name', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Units that can be picked as "multiple of" (base units only).
        $baseUnits = Unit::baseUnits()->orderBy('name')->get(['id', 'name', 'short_name']);

        return view('pages.units.index', compact('units', 'search', 'baseUnits'));
    }

    /**
     * Store unit
     */
    public function store(Request $request)
    {
        Unit::create($this->validated($request) + [
            'shop_id'  => $request->shop_id,
            'admin_id' => Auth::id(),
        ]);

        // stay on the same page with success message
        return redirect()->back()->with('success', 'Unit created successfully');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $this->validated($request, $unit);

        // A unit other units are built on can't itself become a multiple.
        if ($data['base_unit_id'] && $unit->multiples()->exists()) {
            return redirect()->back()->withInput()
                ->with('error', "\"{$unit->name}\" is the base for other units, so it can't be a multiple of another unit.");
        }

        $unit->update($data);

        return redirect()->back()->with('success', 'Unit updated successfully');
    }

    /**
     * Name, short name, decimals, and — when "register as a multiple" is
     * on — which base unit it's made of and how many.
     */
    private function validated(Request $request, ?Unit $unit = null): array
    {
        $isMultiple = $request->boolean('is_multiple');

        $request->validate([
            'name'          => 'required|string|max:100',
            'short_name'    => 'required|string|max:10',
            // Optional so the quick-add unit forms elsewhere keep working (defaults to No).
            'allow_decimal' => 'nullable|boolean',
            'base_unit_id'  => [$isMultiple ? 'required' : 'nullable', 'integer',
                \Illuminate\Validation\Rule::exists('units', 'id')->whereNull('base_unit_id')->whereNull('deleted_at'),
                \Illuminate\Validation\Rule::notIn([$unit?->id])],
            'multiplier'    => [$isMultiple ? 'required' : 'nullable', 'integer', 'min:2'],
        ], [
            'base_unit_id.required' => 'Choose which unit this is a multiple of.',
            'base_unit_id.exists'   => 'Pick a base unit (one that is not itself a multiple).',
            'base_unit_id.not_in'   => 'A unit cannot be a multiple of itself.',
            'multiplier.required'   => 'Enter how many base units make one of this unit.',
            'multiplier.min'        => 'The multiplier must be at least 2.',
        ]);

        return [
            'name'          => $request->name,
            'short_name'    => $request->short_name,
            'allow_decimal' => $request->boolean('allow_decimal'),
            'base_unit_id'  => $isMultiple ? (int) $request->base_unit_id : null,
            'multiplier'    => $isMultiple ? (int) $request->multiplier : null,
        ];
    }

    public function destroy(Unit $unit)
    {
        $inUse = $unit->products()->count();
        if ($inUse) {
            return redirect()->back()->with('error', "\"{$unit->name}\" is used by {$inUse} product(s) and cannot be deleted.");
        }

        $multiples = $unit->multiples()->pluck('name');
        if ($multiples->isNotEmpty()) {
            return redirect()->back()->with('error', "\"{$unit->name}\" is the base for {$multiples->implode(', ')} and cannot be deleted.");
        }

        $unit->delete();

        return redirect()->back()->with('success', 'Unit deleted successfully');
    }
}
