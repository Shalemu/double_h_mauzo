<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Settings › Financial Years. Exactly one year is current: the one the
 * business is operating in, shown in the sidebar and offered on reports.
 */
class FinancialYearController extends Controller
{
    public function index()
    {
        $years = FinancialYear::orderByDesc('start_date')->get();
        $current = $years->firstWhere('is_current', true);

        // Suggest the year after the newest one for "New Financial Year".
        $latest = $years->first();
        $suggested = $latest
            ? ['start' => $latest->end_date->copy()->addDay(), 'end' => $latest->end_date->copy()->addYear()]
            : ['start' => now()->startOfYear(), 'end' => now()->endOfYear()];

        return view('pages.settings.financial_years', compact('years', 'current', 'suggested'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $year = FinancialYear::create($data + ['is_current' => false]);
            if ($request->boolean('is_current') || !FinancialYear::where('is_current', true)->exists()) {
                $year->makeCurrent();
            }
        });

        return redirect()->route('settings.financial-years.index')->with('success', "Financial year \"{$data['name']}\" created.");
    }

    public function update(Request $request, FinancialYear $financialYear)
    {
        $data = $this->validated($request, $financialYear);

        DB::transaction(function () use ($data, $request, $financialYear) {
            $financialYear->update($data);
            if ($request->boolean('is_current')) {
                $financialYear->makeCurrent();
            }
        });

        return redirect()->route('settings.financial-years.index')->with('success', "Financial year \"{$financialYear->name}\" saved.");
    }

    public function makeCurrent(FinancialYear $financialYear)
    {
        $financialYear->makeCurrent();

        return back()->with('success', "\"{$financialYear->name}\" is now the current financial year.");
    }

    public function destroy(FinancialYear $financialYear)
    {
        if ($financialYear->is_current) {
            return back()->with('error', 'The current financial year can\'t be deleted. Make another year current first.');
        }

        $financialYear->delete();

        return back()->with('success', "Financial year \"{$financialYear->name}\" deleted.");
    }

    private function validated(Request $request, ?FinancialYear $year = null): array
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:50', Rule::unique('financial_years', 'name')->ignore($year?->id)],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ], [
            'name.unique'    => 'A financial year with this name already exists.',
            'end_date.after' => 'The end date must be after the start date.',
        ]);

        // Years may not overlap: every date belongs to one financial year.
        $overlap = FinancialYear::where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>=', $data['start_date'])
            ->when($year, fn ($q) => $q->whereKeyNot($year->id))
            ->first();
        if ($overlap) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => "These dates overlap {$overlap->name} ({$overlap->period()}).",
            ]);
        }

        if (\Carbon\Carbon::parse($data['start_date'])->diffInDays(\Carbon\Carbon::parse($data['end_date'])) > 550) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_date' => 'A financial year can be at most 18 months long.',
            ]);
        }

        $data['name'] = trim($data['name']);

        return $data;
    }
}
