<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $brands = Brand::withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.brands.index', compact('brands', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:brands,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Brand::create($data);

        return redirect()->back()->with('success', 'Brand added successfully');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('brands', 'name')->ignore($brand->id)],
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($brand, $data) {
            // Products store the brand as text, so a rename carries over to them.
            if ($data['name'] !== $brand->name) {
                Products::where('brand', $brand->name)->update(['brand' => $data['name']]);
            }
            $brand->update($data);
        });

        return redirect()->back()->with('success', 'Brand updated successfully');
    }

    public function destroy(Brand $brand)
    {
        $inUse = $brand->products()->count();
        if ($inUse) {
            return redirect()->back()->with('error', "\"{$brand->name}\" is used by {$inUse} product(s) and cannot be deleted.");
        }

        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }
}
