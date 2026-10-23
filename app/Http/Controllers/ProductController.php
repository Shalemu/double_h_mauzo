<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Actions\Products\CreateProduct;
use App\DataTables\ProductsExportTable;
use App\DTO\Products\ProductData;
use App\Enums\StockFilter;
use App\Exports\ProductsExcelExport;
use App\Exports\ProductsPdfExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductsTemplateExport;  
use App\Imports\ProductsImport;   
use Carbon\Carbon;
use App\Models\ProductTrash;
use App\Models\Shops;




class ProductController extends Controller
{

public function index()
{
    $stockFilter = StockFilter::fromInput(request()->route('filter') ?? request()->query('filter'));
    $filter = $stockFilter->value;
    $applyInventoryFilter = fn ($query) => $stockFilter->apply($query);

    if (Auth::guard('staff')->check()) {
        $staff = Auth::guard('staff')->user();

        // A shop can hold tens of thousands of products — loading every
        // row (and filtering it in PHP) crashed this page. Cap each list
        // and do the filtering in SQL instead.
        $displayLimit = 500;
        $productsQuery = Products::where('shop_id', $staff->shop_id);
        $products = $applyInventoryFilter(clone $productsQuery)->orderByDesc('quantity')->orderBy('name')
            ->limit($displayLimit)->get();

        $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        $today = Carbon::today();

        $runningOutProducts = (clone $productsQuery)->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->limit($displayLimit)->get();

        $expiringProducts = (clone $productsQuery)->whereNotNull('expire_date')
            ->whereBetween('expire_date', [$today, $today->copy()->addDays(7)])
            ->limit($displayLimit)->get();

        $expiredProducts = (clone $productsQuery)->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->limit($displayLimit)->get();

        $finishedProducts = (clone $productsQuery)->where('quantity', 0)
            ->limit($displayLimit)->get();

        // Note: products has no "disposed" column — always empty, matching
        // prior behavior.
        $disposedProducts = collect();

        return view('pages.staff.products.index', compact(
            'products',
            'categories',
            'units',
            'runningOutProducts',
            'expiringProducts',
            'expiredProducts',
            'finishedProducts',
            'disposedProducts'
        ));
    }

    // Admin view
    $admin = Auth::user();
    $isAdminTier = $admin->isAdminTier();
    $productsTotal = null;
    $productsDisplayLimit = 500;
    $activeShop = ($isAdminTier && session('active_shop_id'))
        ? Shops::find(session('active_shop_id'))
        : null;

    if ($activeShop) {
        // Admin is focused on one shop via the active-shop switcher —
        // behaves like a regular shop-scoped admin (no shop picker needed,
        // it's implied), but still capped/sorted safely.
        $productsQuery = $applyInventoryFilter(Products::where('shop_id', $activeShop->id));
        $productsTotal = (clone $productsQuery)->count();
        $products = (clone $productsQuery)
            ->orderByDesc('quantity')->orderBy('name')
            ->limit($productsDisplayLimit)->get();
    } elseif ($isAdminTier) {
        // No active shop chosen — global view across every shop, including
        // any product still missing a shop assignment. Loading every row
        // (a shop can hold tens of thousands) crashes the page, so the
        // list is capped for display; the bulk-assign tool still operates
        // on the full table regardless of what's shown here.
        $productsQuery = $applyInventoryFilter(Products::query());
        $productsTotal = (clone $productsQuery)->count();
        $products = $productsQuery->with('shop')->latest()->limit($productsDisplayLimit)->get();
    } else {
        $products = $admin->shop
            ? $applyInventoryFilter(Products::where('shop_id', $admin->shop->id))->get()
            : collect();
    }

    $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
    $units = Unit::orderBy('name')->get();
    $shops = $isAdminTier ? Shops::orderBy('name')->get() : collect();
    // Global mode = admin-tier with no active shop chosen: sees every shop
    // at once and needs the shop picker/bulk-assign tooling. Once a shop is
    // active, the page behaves like a regular single-shop admin.
    $globalMode = $isAdminTier && !$activeShop;
    $productFilter = $filter;

    return view('pages.products.page', compact(
        'products',
        'categories',
        'units',
        'shops',
        'isAdminTier',
        'productsTotal',
        'productsDisplayLimit',
        'activeShop',
        'globalMode',
        'productFilter'
    ));
}

    /**
     * Bulk-assign a shop to many products at once (admin-tier only) —
     * either a chosen set of product IDs, or every currently unassigned
     * product in one shot.
     */
    public function bulkAssignShop(Request $request)
    {
        $admin = Auth::user();

        if (!$admin->isAdminTier()) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'assign_all_unassigned' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $shop = Shops::findOrFail($request->shop_id);

        $query = $request->boolean('assign_all_unassigned')
            ? Products::whereNull('shop_id')
            : Products::whereIn('id', $request->product_ids ?? []);

        $count = $query->count();
        $query->update([
            'shop_id' => $shop->id,
            'admin_id' => $shop->admin_id,
        ]);

        return redirect()->route('products.index')
            ->with('success', "Assigned {$count} product(s) to \"{$shop->name}\".");
    }




    /**
     * Show create product form
     */
public function create()
{
    $admin = Auth::user();
    $isAdminTier = $admin->isAdminTier();
    $activeShop = ($isAdminTier && session('active_shop_id'))
        ? Shops::find(session('active_shop_id'))
        : null;

    $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
    $units = Unit::with('baseUnit')->orderBy('name')->get();
    $brands = \App\Models\Brand::orderBy('name')->pluck('name');
    // Admin with no active shop picks where the product goes.
    $shops = ($isAdminTier && !$activeShop) ? Shops::orderBy('type')->orderBy('name')->get() : collect();
    $targetShop = $activeShop ?? ($isAdminTier ? null : $admin->shop);

    return view('pages.products.create', compact('categories', 'units', 'brands', 'shops', 'targetShop'));
}

    /**
     * Store product
     */
public function store(Request $request, CreateProduct $createProduct)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'unit_id' => 'required|integer',
        'purchase_price' => 'required|numeric|min:0',
        'quantity' => 'nullable|integer|min:0',
        'min_quantity' => 'nullable|integer|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'wholesale_price' => 'nullable|numeric|min:0',
        'barcode' => 'nullable|string',
        'expire_date' => 'nullable|date',
        'image' => 'nullable|image|max:2048',
    ]);

    // Which shop the product goes into: the active shop, the one picked on the form, or the user's own.
    $admin = Auth::user();
    $activeShopId = session('active_shop_id');

    if ($admin->isAdminTier() && $activeShopId) {
        $shopId = $activeShopId;
    } elseif ($admin->isAdminTier()) {
        $request->validate(['shop_id' => 'required|exists:shops,id']);
        $shopId = $request->shop_id;
    } elseif ($admin->shop) {
        $shopId = $admin->shop->id;
    } elseif ($request->expectsJson()) {
        return response()->json([
            'error' => 'This user has no shop assigned'
        ], 422);
    } else {
        return back()->withInput()->with('error', 'This user has no shop assigned.');
    }

    $product = $createProduct->execute(ProductData::fromArray((int) $shopId, $request->all(), $request->file('image'), $admin->id));

    if ($request->expectsJson()) {
        return response()->json(['success' => 'Product created successfully']);
    }

    // "Save & add another" stays on the form; plain save goes back to the list.
    return redirect()->route($request->boolean('add_another') ? 'products.create' : 'products.index')
        ->with('success', "Product \"{$product->name}\" created successfully.");
}


public function show($id)
{
    $product = Products::findOrFail($id);
    return view('pages.products.show', compact('product'));
}

public function edit($id)
{
    $product = Products::findOrFail($id);
    $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
    $units = Unit::orderBy('name')->get();

    return view('pages.products.edit', compact('product', 'categories', 'units'));
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
        'shop_id' => 'nullable|exists:shops,id',
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


    /**
     * Export products to Excel (App\Exports\ProductsExcelExport).
     */
    public function exportExcel(Request $request)
    {
        return (new ProductsExcelExport(ProductsExportTable::make($request)))->download();
    }

    /**
     * Export products to PDF; long lists are refused with a clear message.
     */
    public function exportPDF(Request $request)
    {
        $export = new ProductsPdfExport(ProductsExportTable::make($request));

        if ($export->tooLarge()) {
            return redirect()->back()->with('error', 'PDF export is limited to ' . number_format(ProductsPdfExport::ROW_LIMIT)
                . ' products, but this list has ' . number_format($export->count()) . '. Pick a shop or a stock filter first, or use Export Excel for the full list.');
        }

        return $export->download();
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
        $admin = Auth::user();
        $activeShopId = session('active_shop_id');

        $rules = ['excel_file' => 'required|file|mimes:xlsx,xls'];
        if ($admin->isAdminTier() && !$activeShopId) {
            $rules['shop_id'] = 'required|exists:shops,id';
        }
        $request->validate($rules);

        $targetShopId = $activeShopId ?: ($request->shop_id ? (int) $request->shop_id : null);
        $import = new ProductsImport($targetShopId);
        Excel::import($import, $request->file('excel_file'));

        $summary = "{$import->created} product(s) added, {$import->updated} existing product(s) updated.";

        if (!empty($import->errors)) {
            return redirect()->back()
                ->with('import_errors', $import->errors)
                ->with('warning', 'Import finished: ' . $summary . ' ' . count($import->errors) . ' note(s) — see details below.');
        }

        return redirect()->back()->with('success', 'Import finished: ' . $summary);
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

        return view('pages.products.running_out', compact('products'));
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

            return view('pages.products.expiring', compact('products'));
        }

    /**
     * Finished (quantity = 0)
     */
public function finished()
{
    $admin = Auth::user();

    $query = Products::where('quantity', 0);

    if (!$admin->isAdminTier()) {
        $query->where('shop_id', $admin->shop?->id);
    }

    $products = $query->get();

    return view('pages.products.finished', compact('products'));
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

        return view('pages.products.expired', compact('products'));
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
    $trashedProducts = ProductTrash::with(['shop', 'category', 'unit'])->latest()->get();
    return view('pages.products.trash', compact('trashedProducts'));
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
