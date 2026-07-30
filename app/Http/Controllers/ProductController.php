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
use App\Models\ProductTrash;




class ProductController extends Controller
{

public function index()
{
    if (Auth::guard('staff')->check()) {
        $staff = Auth::guard('staff')->user();
        $products = Products::where('shop_id', $staff->shop_id)->get();
        $categories = ProductCategory::whereNull('parent_id')->get();
        $units = Unit::all();

        $today = Carbon::today();

        $runningOutProducts = $products->filter(function($p) {
            return $p->quantity > 0 && $p->quantity <= $p->min_quantity;
        });

        $expiringProducts = $products->filter(function($p) use ($today) {
            return $p->expire_date
                   && Carbon::parse($p->expire_date)->between($today, $today->copy()->addDays(7));
        });

        $expiredProducts = $products->filter(function($p) use ($today) {
            return $p->expire_date && Carbon::parse($p->expire_date)->lt($today);
        });

        $disposedProducts = $products->filter(function($p) {
            return $p->disposed == 1;
        });

        return view('dashboard.staff.products.index', compact(
            'products',
            'categories',
            'units',
            'runningOutProducts',
            'expiringProducts',
            'expiredProducts',
            'disposedProducts'
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
        'wholesale_price' => 'nullable|numeric|min:0',
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
        'wholesale_price',
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
        'wholesale_price' => 'nullable|numeric|min:0',
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
    $product->load('unit');

    return response()->json([
        'success' => 'Product updated successfully',
        'product' => [
            'name' => $product->name,
            'quantity' => $product->quantity,
            'unit_name' => $product->unit->name ?? '-',
            'purchase_price' => $product->purchase_price ? number_format($product->purchase_price) : '-',
            'selling_price' => $product->selling_price ? number_format($product->selling_price) : '-',
            'wholesale_price' => $product->wholesale_price ? number_format($product->wholesale_price) : '-',
            'expire_date' => $product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->format('Y-m-d') : '-',
            'image' => $product->image ? asset('storage/' . $product->image) : null,
        ],
    ]);
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

        $import = new ProductsImport;
        Excel::import($import, $request->file('excel_file'));

        if (!empty($import->errors)) {
            return redirect()->back()
                ->with('import_errors', $import->errors)
                ->with('warning', 'Import finished with ' . count($import->errors) . ' issue(s). See details below.');
        }

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

        // Expiring (expire_date within next 1 year)
        case 'expiring':
            $query->whereNotNull('expire_date')
                  ->whereBetween('expire_date', [$today, $today->copy()->addYear()]);
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

        // Disposed (disposed = 1)
        case 'disposed':
            $query->where('disposed', 1);
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
/**
 * Expiring (expire_date within next 1 year)
 */
        public function expiring()
        {
            $today = Carbon::today();

            // Get products with expire_date in the future, within the next year
            $products = Products::withTrashed()
                ->whereNotNull('expire_date')
                ->where('expire_date', '>', $today) // future only
                ->where('expire_date', '<=', $today->copy()->addYear()) // within next 1 year
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
    public function destroy($id)
{
    $product = Products::findOrFail($id);

    // Move to trash
    ProductTrash::create([
        'original_id' => $product->id,
        'shop_id' => $product->shop_id,
        'name' => $product->name,
        'brand' => $product->brand,
        'category_id' => $product->category_id,
        'unit_id' => $product->unit_id,
        'quantity' => $product->quantity,
        'min_quantity' => $product->min_quantity,
        'purchase_price' => $product->purchase_price,
        'selling_price' => $product->selling_price,
        'wholesale_price' => $product->wholesale_price,
        'invoice_number' => $product->invoice_number,
        'barcode' => $product->barcode,
        'expire_date' => $product->expire_date,
        'size' => $product->size,
        'color' => $product->color,
        'image' => $product->image,
        'admin_id' => $product->admin_id,
    ]);

    // Delete the original product
    $product->delete();

    return response()->json(['success' => 'Product moved to trash successfully.']);
}


public function trash()
{
    $trashedProducts = ProductTrash::all();
    return view('dashboard.products.trash', compact('trashedProducts'));
}

public function restore($id)
{
    $trashed = ProductTrash::findOrFail($id);

    // Create new product (let Laravel handle ID automatically)
    $product = Products::create([
        'shop_id' => $trashed->shop_id,
        'name' => $trashed->name,
        'brand' => $trashed->brand,
        'category_id' => $trashed->category_id,
        'unit_id' => $trashed->unit_id,
        'quantity' => $trashed->quantity,
        'min_quantity' => $trashed->min_quantity,
        'purchase_price' => $trashed->purchase_price,
        'selling_price' => $trashed->selling_price,
        'wholesale_price' => $trashed->wholesale_price,
        'invoice_number' => $trashed->invoice_number,
        'barcode' => $trashed->barcode,
        'expire_date' => $trashed->expire_date,
        'size' => $trashed->size,
        'color' => $trashed->color,
        'image' => $trashed->image,
        'admin_id' => $trashed->admin_id,
    ]);

    $trashed->delete();

    return response()->json([
        'success' => 'Product restored successfully!',
        'product_id' => $product->id
    ]);
}


public function forceDelete($id)
{
    $trashed = ProductTrash::findOrFail($id);
    $trashed->delete();

    return response()->json([
        'success' => 'Product permanently deleted!'
    ]);
}

}
