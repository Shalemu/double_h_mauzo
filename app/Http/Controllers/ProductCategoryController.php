<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Show categories list (main categories with their subcategories)
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $categories = ProductCategory::whereNull('parent_id')
            ->withCount('products')
            ->with(['children' => fn ($q) => $q->withCount('subcategoryProducts')->orderBy('name')])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhereHas('children', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $parentCategories = ProductCategory::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);

        return view('pages.categories.index', compact('categories', 'parentCategories', 'search'));
    }

    /**
     * Store category or subcategory
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:product_categories,id',
            'shop_id'   => 'nullable|integer',
        ]);

        ProductCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'parent_id'   => $request->parent_id,
            'shop_id'     => $request->shop_id,
            'admin_id'    => Auth::id(),
        ]);

        // Stay on the same page and show success message
        return redirect()->back()->with('success', 'Category saved successfully');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            // A category can't become its own parent, and a main category
            // that already has subcategories stays a main category.
            'parent_id'   => ['nullable', 'integer', 'exists:product_categories,id', Rule::notIn([$category->id])],
        ]);

        if ($request->parent_id && $category->children()->exists()) {
            return redirect()->back()->with('error', 'A category that has subcategories cannot be moved under another category.');
        }

        $category->update($request->only('name', 'description', 'parent_id'));

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroy(ProductCategory $category)
    {
        $inUse = $category->products()->count() + $category->subcategoryProducts()->count();
        if ($inUse) {
            return redirect()->back()->with('error', "\"{$category->name}\" is used by {$inUse} product(s) and cannot be deleted.");
        }
        if ($category->children()->exists()) {
            return redirect()->back()->with('error', "Delete or move the subcategories of \"{$category->name}\" first.");
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}
