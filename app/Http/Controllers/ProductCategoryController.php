<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductCategoryController extends Controller
{
    /**
     * Show categories list
     */
    public function index()
    {
        $categories = ProductCategory::whereNull('parent_id')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show create category form
     */
    public function create()
    {
        $parentCategories = ProductCategory::whereNull('parent_id')->get();
        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Store category or subcategory
     */
  public function store(Request $request)
{
    $request->validate([
        'name'      => 'required|string',
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


}
