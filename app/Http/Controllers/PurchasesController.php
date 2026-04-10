<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Supplier;
use App\Models\Shops;
use App\Models\ProductCategory;
use App\Models\Units;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseItem;
use App\Models\CreditPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchasesController extends Controller
{
    /**
     * Show form to create a purchase for existing products
     */
    public function create()
    {
        $products = Products::all();
        $suppliers = Supplier::all();
        $shops = Shops::all();
        $categories = ProductCategory::all();
        $units = Units::all();

        return view('dashboard.purchases.create', compact(
            'products', 'suppliers', 'shops', 'categories', 'units'
        ));
    }

    /**
     * Show form to create a new product and purchase it
     */
    public function newProduct()
    {
        $suppliers = Supplier::all();
        $shops = Shops::all();
        $categories = ProductCategory::all();
        $units = Units::all();

        return view('dashboard.purchases.new_product', compact(
            'units', 'categories', 'shops', 'suppliers'
        ));
    }

    /**
     * Store a purchase for existing products
     */
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

        $items = json_decode($request->items, true);

        if (empty($items)) {
            return back()->with('error', 'Add at least one item.');
        }

        $totalAmount = collect($items)->sum(fn($i) => $i['quantity'] * $i['price']);
        $amountPaid = floatval($request->amount_paid ?? 0);
        $remainingAmount = $request->payment_type === 'credit' ? max(0, $totalAmount - $amountPaid) : 0;

        DB::transaction(function() use ($request, $items, $totalAmount, $amountPaid, $remainingAmount) {
            /** @var PurchaseInvoice $invoice */
            $invoice = PurchaseInvoice::create([
                'shop_id' => $request->shop_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number ?? null,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $remainingAmount,
                'payment_type' => $request->payment_type,
                'purchased_at' => now(),
            ]);

            foreach ($items as $item) {
                /** @var Products $product */
                $product = Products::findOrFail($item['product_id']);

                PurchaseItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'sale_type' => $item['sale_type'] ?? 'retail',
                ]);

                // Update product stock
                $product->quantity += $item['quantity'];
                $product->purchase_price = $item['price'];
                $product->save();
            }

            // Initial credit payment
            if ($invoice->payment_type === 'credit' && $amountPaid > 0) {
                CreditPayment::create([
                    'purchase_invoice_id' => $invoice->id,
                    'supplier_id' => $invoice->supplier_id,
                    'amount' => $amountPaid,
                    'paid_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Purchase invoice created successfully.');
    }

    /**
     * Store a new product and purchase it
     */
    public function storeNewProduct(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'amount_paid' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        /** @var PurchaseInvoice|null $invoice */
        $invoice = null;
        /** @var Products|null $product */
        $product = null;

        DB::transaction(function() use ($request, &$invoice, &$product) {
            // Create new product
        $product = Products::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price ?? 0,
            'quantity' => 0,
            'image' => $request->file('image') 
                ? $request->file('image')->store('products', 'public') 
                : null,
            'admin_id' => auth()->id(), // <-- add this line
        ]);

            $totalAmount = $request->quantity * $request->purchase_price;
            $amountPaid = floatval($request->amount_paid ?? 0);
            $remainingAmount = $request->payment_type === 'credit' 
                ? max(0, $totalAmount - $amountPaid) 
                : 0;

            // Create purchase invoice
            $invoice = PurchaseInvoice::create([
                'shop_id' => $request->shop_id,
                'supplier_id' => $request->supplier_id,
                'invoice_number' => null,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $remainingAmount,
                'payment_type' => $request->payment_type,
                'purchased_at' => now(),
            ]);

            // Create purchase item
            PurchaseItem::create([
                'purchase_invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'purchase_price' => $request->purchase_price,
                'total' => $totalAmount,
                'sale_type' => $request->sale_type ?? 'retail',
            ]);

            // Update product stock
            $product->quantity += $request->quantity;
            $product->save();

            // Initial credit payment
            if ($invoice->payment_type === 'credit' && $amountPaid > 0) {
                CreditPayment::create([
                    'purchase_invoice_id' => $invoice->id,
                    'supplier_id' => $invoice->supplier_id,
                    'amount' => $amountPaid,
                    'paid_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => 'New product purchased successfully',
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);
    }

    /**
     * List all purchases grouped by date
     */
    public function index()
    {
        $shop = Shops::where('user_id', auth()->id())->firstOrFail();

        $purchases = PurchaseInvoice::with('supplier', 'shop', 'items')
            ->where('shop_id', $shop->id)
            ->latest()
            ->get();

        $purchasesByDate = $purchases
            ->groupBy(fn($p) => Carbon::parse($p->purchased_at)->format('Y-m-d'))
            ->map(fn($items, $date) => [
                'date' => $date,
                'total' => $items->sum(fn($p) => $p->total_amount),
                'items' => $items,
            ]);

        return view('dashboard.purchases.index', compact('shop', 'purchases', 'purchasesByDate'));
    }

    /**
     * Show purchases for a specific date
     */
    public function detail(Shops $shop, $date)
    {
        $purchases = $shop->purchaseItems()
            ->whereHas('invoice', fn($q) => $q->whereDate('purchased_at', $date))
            ->with(['product', 'invoice.supplier'])
            ->get();

        return view('dashboard.purchases.detail', compact('purchases', 'date'));
    }

    /**
     * Add deposit for a credit invoice
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $invoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);

        if ($request->amount > $invoice->remaining_amount) {
            return back()->with('error', 'Amount exceeds remaining credit.');
        }

        DB::transaction(function() use ($invoice, $request) {
            $invoice->remaining_amount -= $request->amount;
            $invoice->amount_paid += $request->amount;
            $invoice->save();

            CreditPayment::create([
                'purchase_invoice_id' => $invoice->id,
                'supplier_id' => $invoice->supplier_id,
                'amount' => $request->amount,
                'paid_at' => now(),
            ]);
        });

        return back()->with('success', 'Deposit applied successfully.');
    }

    /**
     * Show invoice payment history
     */
    public function history($invoiceId)
    {
        $invoice = PurchaseInvoice::with('payments')->findOrFail($invoiceId);

        return view('dashboard.purchases.history', compact('invoice'));
    }

    /**
     * Print invoice
     */
    public function print($invoiceId)
    {
        $invoice = PurchaseInvoice::with('items.product', 'supplier', 'shop')->findOrFail($invoiceId);

        return view('dashboard.purchases.print', compact('invoice'));
    }
}