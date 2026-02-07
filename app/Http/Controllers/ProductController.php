<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductsTemplateExport;  
use App\Imports\ProductsImport;   




class ProductController extends Controller
{

public function index()
{
    $products   = Products::all();
    $categories = ProductCategory::whereNull('parent_id')->get();
    $units      = Unit::all();

    return view('dashboard.products.index', compact(
        'products',
        'categories',
        'units'
    ));
}
    /**
     * Show create product form
     */
public function create()
{
    $categories = ProductCategory::whereNull('parent_id')->get();
    $units = Unit::all();

    return view('products.create', compact('categories', 'units'));
}

    /**
     * Store product
     */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'unit_id' => 'required|integer',
        'selling_price' => 'nullable|numeric|min:0',
        'barcode' => 'nullable|string',
        'expire_date' => 'nullable|date',
        'image' => 'nullable|image|max:2048',
    ]);

    $data = $request->only([
        'name',
        'item_code',
        'barcode',
        'brand',
        'category_id',
        'subcategory_id',
        'unit_id',
        'quantity',
        'min_quantity',
        'purchase_price',
        'selling_price',
        'invoice_number',
        'expire_date',
        'size',
        'color',
    ]);

    $admin = Auth::user();

    if (!$admin->shop) {
    return response()->json([
        'error' => 'This user has no shop assigned'
    ], 422);
}

$data['shop_id'] = $admin->shop->id;

    //  IMPORTANT PART
    $data['admin_id'] = $admin->id;
    $data['shop_id']  = $admin->shop->id; 
    $data['sync_status'] = 0;

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')
            ->store('products', 'public');
    }

    Products::create($data);

    return response()->json(['success' => 'Product created successfully']);
}


public function show($id)
{
    $product = Products::findOrFail($id);
    return view('dashboard.products.show', compact('product'));
}

public function edit($id)
{
    $product = Products::findOrFail($id);
    $categories = ProductCategory::whereNull('parent_id')->get();
    $units = Unit::all();

    return view('dashboard.products.edit', compact('product', 'categories', 'units'));
}

public function update(Request $request, $id)
{
    $product = Products::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'brand' => 'nullable|string|max:255',
        'category_id' => 'nullable|integer',
        'subcategory_id' => 'nullable|integer',
        'unit_id' => 'required|integer',
        'quantity' => 'nullable|integer|min:0',
        'min_quantity' => 'nullable|integer|min:0',
        'purchase_price' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'invoice_number' => 'nullable|string',
        'barcode' => 'nullable|string',
        'expire_date' => 'nullable|date',
        'size' => 'nullable|string',
        'color' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
    ]);

    $data = $request->all();

    // Handle image upload
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $path = $file->store('products', 'public');
        $data['image'] = $path;
    }

    $product->update($data);

    return response()->json(['success' => 'Product updated successfully']);
}

    /**
     * Export products to Excel
     */


public function exportExcel()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    /**
     * Export products to PDF
     */
    public function exportPDF()
    {
        $products = Products::all();
        $pdf = PDF::loadView('dashboard.products.pdf', compact('products'));
        return $pdf->download('products.pdf');
    }

  public function downloadTemplate()
    {
        return Excel::download(new ProductsTemplateExport, 'products_template.xlsx');
    }

/**
 * Import products from Excel
 */
 public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new ProductsImport, $request->file('excel_file'));

        return redirect()->back()->with('success', 'Products imported successfully!');
    }

}
