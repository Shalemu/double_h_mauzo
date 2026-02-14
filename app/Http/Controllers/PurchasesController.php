<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Purchases;
use App\Models\Supplier;
use App\Models\Shops;
use App\Models\Units;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class PurchasesController extends Controller
{
    // Show form to create a purchase
    public function create()
    {
        $products = Products::all(); // optionally filter by shop
        $suppliers = Supplier::all();
        $shops = Shops::all(); 
        $categories = ProductCategory::all(); 
        $units = Units::all(); 
      
    return view('dashboard.purchases.create', compact('products', 'suppliers', 'shops','categories','units'));
    }

    // Store purchase and update product stock
public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'shop_id' => 'required|exists:shops,id',
        'quantity' => 'required|numeric|min:1',
        'purchase_price' => 'required|numeric|min:0',
    ]);

    $product = Products::findOrFail($request->product_id);

    Purchases::create([
        'product_id' => $product->id,
        'shop_id' => $request->shop_id,
        'supplier_id' => $request->supplier_id,
        'quantity' => $request->quantity,
        'purchase_price' => $request->purchase_price,
        'invoice_number' => $request->invoice_number ?? null,
        'purchased_at' => now(),
    ]);

    // Update stock
    $product->quantity += $request->quantity;
    $product->purchase_price = $request->purchase_price;
    $product->save();

    //  stay on same page
    return back()->with('success', 'Purchase added successfully.');
}


    // List all purchases
public function index()
{
    $shop = \App\Models\Shops::where('user_id', auth()->id())->firstOrFail();

    $purchases = Purchases::with('product', 'supplier', 'shop')
        ->where('shop_id', $shop->id)
        ->latest()
        ->get();

    $purchasesByDate = $purchases
        ->groupBy(fn($purchase) => \Carbon\Carbon::parse($purchase->purchased_at)->format('Y-m-d'))
        ->map(fn($items, $date) => [
            'date' => $date,
            'total' => $items->sum(fn($p) => $p->quantity * $p->purchase_price),
            'items' => $items,
        ]);

    return view('dashboard.purchases.index', compact('shop', 'purchases', 'purchasesByDate'));
}



    public function detail(Shops $shop, $date)
{
    $purchases = $shop->purchases
        ->whereDate('purchased_at', $date)
        ->load('product', 'supplier');

    return view('dashboard.purchases.detail', compact('purchases', 'date'));
}

}
