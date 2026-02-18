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
use Carbon\Carbon;




class ProductController extends Controller
{

public function index()
{
    // Check if staff
    if (Auth::guard('staff')->check()) {
        $staff = Auth::guard('staff')->user();
        $products = Products::where('shop_id', $staff->shop_id)->get();
        $categories = ProductCategory::whereNull('parent_id')->get();
        $units = Unit::all();

        // Staff view
        return view('dashboard.staff.products.index', compact(
            'products',
            'categories',
            'units'
        ));
    }

    // Admin view
    $admin = Auth::user();
    $products = Products::where('shop_id', $admin->shop->id)->get();
    $categories = ProductCategory::whereNull('parent_id')->get();
    $units = Unit::all();

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


    public function filterByStatus($status)
{
    $today = Carbon::today();

    $query = Products::query();

    switch ($status) {

        // Running Out (quantity <= min_quantity but > 0)
        case 'running':
            $query->whereColumn('quantity', '<=', 'min_quantity')
                  ->where('quantity', '>', 0);
        break;

        // Expiring (expire_date within next 7 days)
        case 'expiring':
            $query->whereNotNull('expire_date')
                  ->whereBetween('expire_date', [$today, $today->copy()->addDays(7)]);
        break;

        // Finished (quantity = 0)
        case 'finished':
            $query->where('quantity', 0);
        break;

        // Expired (expire_date < today)
        case 'expired':
            $query->whereNotNull('expire_date')
                  ->where('expire_date', '<', $today);
        break;

        // Disposed (you must have disposed column OR table)
        case 'disposed':
            $query->where('disposed', 1); // only if you have this column
        break;
    }

    $products = $query->get();

    return response()->json($products);
}


   public function runningOut()
    {
        $products = Products::withTrashed() // include soft-deleted products if needed
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->where('quantity', '>', 0)
            ->get();

        return view('dashboard.products.running_out', compact('products'));
    }

    /**
     * Expiring (expire_date within next 7 days)
     */
    public function expiring()
    {
        $today = Carbon::today();

        $products = Products::withTrashed()
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [$today, $today->copy()->addDays(7)])
            ->get();

        return view('dashboard.products.expiring', compact('products'));
    }

    /**
     * Finished (quantity = 0)
     */
public function finished()
{
    $admin = Auth::user();

    $products = Products::where('shop_id', $admin->shop->id)
                        ->where('quantity', 0)
                        ->get();

    return view('dashboard.products.finished', compact('products'));
}



    /**
     * Expired (expire_date < today)
     */
    public function expired()
    {
        $today = Carbon::today();

        $products = Products::withTrashed()
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->get();

        return view('dashboard.products.expired', compact('products'));
    }

    /**
     * Disposed (disposed = 1)
     */
    public function disposed()
    {
        $products = Products::withTrashed()
            ->where('disposed', 1)
            ->get();

        return view('dashboard.products.disposed', compact('products'));
    }

}
