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
use Carbon\Carbon;

class PurchasesController extends Controller
{
    // Show form to create a purchase
    public function create()
    {
        $products = Products::all();
        $suppliers = Supplier::all();
        $shops = Shops::all(); 
        $categories = ProductCategory::all(); 
        $units = Units::all(); 
      
        return view('dashboard.purchases.create', compact(
            'products',
            'suppliers',
            'shops',
            'categories',
            'units'
        ));
    }

    // Store purchase and update product stock
    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|json',
            'amount_paid' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string',
        ]);

        // ✅ Decode items FIRST
        $items = json_decode($request->items, true);

        // ✅ Get amount paid
        $amountPaid = floatval($request->amount_paid ?? 0);

        // ✅ Calculate TOTAL purchase amount
        $totalPurchaseAmount = 0;
        foreach ($items as $item) {
            $totalPurchaseAmount += $item['quantity'] * $item['price'];
        }

        // ✅ Calculate remaining credit
        $remainingCredit = 0;
        if ($request->payment_type === 'credit') {
            $remainingCredit = max($totalPurchaseAmount - $amountPaid, 0);
        }

        // ✅ Save each item
        foreach ($items as $item) {
            $product = Products::findOrFail($item['product_id']);

            Purchases::create([
                'product_id' => $product->id,
                'shop_id' => $request->shop_id,
                'supplier_id' => $request->supplier_id,
                'quantity' => $item['quantity'],
                'purchase_price' => $item['price'],
                'sale_type' => $item['sale_type'] ?? 'retail',

                'payment_type' => $request->payment_type,
                'amount_paid' => $amountPaid,
                'remaining_credit' => $remainingCredit,
                'total_amount' => $totalPurchaseAmount,

                'invoice_number' => $request->invoice_number ?? null,
                'purchased_at' => now(),
            ]);

            // ✅ Update stock
            $product->quantity += $item['quantity'];
            $product->purchase_price = $item['price'];
            $product->save();
        }

        return back()->with('success', 'Purchase added successfully.');
    }

    // List all purchases
    public function index()
    {
        $shop = Shops::where('user_id', auth()->id())->firstOrFail();

        $purchases = Purchases::with('product', 'supplier', 'shop')
            ->where('shop_id', $shop->id)
            ->latest()
            ->get();

        $purchasesByDate = $purchases
            ->groupBy(fn($purchase) => Carbon::parse($purchase->purchased_at)->format('Y-m-d'))
            ->map(fn($items, $date) => [
                'date' => $date,
                'total' => $items->sum(fn($p) => $p->quantity * $p->purchase_price),
                'items' => $items,
            ]);

        return view('dashboard.purchases.index', compact(
            'shop',
            'purchases',
            'purchasesByDate'
        ));
    }

    // Show purchases for a specific date
    public function detail(Shops $shop, $date)
    {
        $purchases = $shop->purchases()
            ->whereDate('purchased_at', $date)
            ->with(['product', 'supplier'])
            ->get();

        return view('dashboard.purchases.detail', compact('purchases', 'date'));
    }
}