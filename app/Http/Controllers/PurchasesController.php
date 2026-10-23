<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Supplier;
use App\Models\Shops;
use App\Models\ProductCategory;
use App\Models\Units;
use App\Models\PurchaseInvoice;
use App\Actions\Purchases\EmailInvoiceToSupplier;
use App\Actions\Purchases\PaySupplierCredit;
use App\Actions\Purchases\PlacePurchaseOrder;
use App\Actions\Purchases\ReceivePurchaseOrder;
use App\Enums\PaymentType;
use App\DTO\Purchases\PurchaseData;
use App\Exports\PurchaseInvoicePdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchasesController extends Controller
{
    /**
     * Show form to create a purchase for existing products
     */
    public function create()
    {
        $activeShopId = session('active_shop_id');

        $products = Products::when($activeShopId, fn($q) => $q->where('shop_id', $activeShopId))
            ->orderByDesc('quantity')
            ->limit(500)
            ->get();

        $suppliers = Supplier::all();
        $shops = Shops::all();
        $categories = ProductCategory::all();
        $units = Units::all();
        $activeShopIdForView = $activeShopId;

        return view('pages.purchases.create', compact(
            'products', 'suppliers', 'shops', 'categories', 'units', 'activeShopIdForView'
        ));
    }

    /**
     * Products assigned to a supplier (Supplier Products), for the purchase
     * form's product list — limited to the chosen shop, priced at the
     * supplier's price.
     */
    public function supplierProducts(Request $request, Supplier $supplier)
    {
        return \App\Models\SupplierProduct::query()
            ->join('products', 'products.id', '=', 'supplier_products.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->where('supplier_products.supplier_id', $supplier->id)
            ->when($request->query('shop'), fn ($q, $shop) => $q->where('products.shop_id', $shop))
            ->orderBy('products.name')
            ->get([
                'products.id', 'products.name', 'products.quantity', 'units.short_name as unit',
                'supplier_products.supplier_price', 'products.purchase_price',
                'products.selling_price', 'products.wholesale_price',
            ]);
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

        return view('pages.purchases.new_product', compact(
            'units', 'categories', 'shops', 'suppliers'
        ));
    }

    /**
     * Place a purchase order for existing products. Stock comes in when the
     * order is received.
     */
    public function store(Request $request, PlacePurchaseOrder $placeOrder, EmailInvoiceToSupplier $emailInvoice)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|json',
            'amount_paid' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string',
        ]);

        $data = PurchaseData::fromArray($validated);
        if (empty($data->items)) {
            return back()->with('error', 'Add at least one item.');
        }

        $invoice = $placeOrder->execute($data);

        // Email is a bonus — the order is already saved even if mail fails.
        [$emailed, $emailNote] = $emailInvoice->execute($invoice);

        return back()
            ->with('success', 'Purchase order placed. Receive it when the goods arrive to add them to stock. ' . $emailNote)
            ->with('purchase_invoice_id', $invoice->id)
            ->with('purchase_invoice_emailed', $emailed)
            ->with('purchase_invoice_email_note', $emailNote);
    }

    /**
     * The purchase invoice as a PDF — downloaded, or shown in the browser
     * with ?inline=1 (used by the Share button to attach the file).
     */
    public function invoicePdf(Request $request, PurchaseInvoice $invoice)
    {
        return (new PurchaseInvoicePdf($invoice))->response($request->boolean('inline'));
    }

    /**
     * The invoice template as a web page — embedded on the invoice page so
     * "View" shows exactly what the PDF and the supplier's email contain.
     */
    public function invoicePreview(PurchaseInvoice $invoice)
    {
        $invoice->loadMissing('shop', 'supplier', 'items.product.unit');

        return view('pages.purchases.invoice_pdf', ['invoice' => $invoice, 'screen' => true]);
    }

    /**
     * Send (again) the invoice PDF to the supplier's email.
     */
    public function emailInvoice(PurchaseInvoice $invoice, EmailInvoiceToSupplier $emailInvoice)
    {
        [$emailed, $note] = $emailInvoice->execute($invoice);

        return back()->with($emailed ? 'success' : 'error', $note);
    }

    /**
     * Create a new product and place a purchase order for it
     */
    public function storeNewProduct(Request $request, PlacePurchaseOrder $placeOrder)
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
            'wholesale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'amount_paid' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        [$invoice, $product] = DB::transaction(function () use ($request, $recordPurchase) {
            // The new product belongs to the shop it is bought into (it was being created without a shop).
            $product = Products::create([
                'name' => $request->name,
                'shop_id' => $request->shop_id,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'purchase_price' => $request->purchase_price,
                'selling_price' => $request->selling_price ?? 0,
                'wholesale_price' => $request->wholesale_price ?? 0,
                'quantity' => 0,
                'image' => $request->file('image')
                    ? $request->file('image')->store('products', 'public')
                    : null,
                'admin_id' => auth()->id(),
            ]);

            // Same as an ordinary purchase: a pending order, stocked when received.
            $invoice = $placeOrder->execute(PurchaseData::fromArray([
                'shop_id' => $request->shop_id,
                'supplier_id' => $request->supplier_id,
                'payment_type' => $request->payment_type,
                'amount_paid' => $request->amount_paid,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price' => $request->purchase_price,
                    'selling_price' => $request->selling_price,
                    'wholesale_price' => $request->wholesale_price,
                ]],
            ]));

            return [$invoice, $product->fresh()];
        });

        return response()->json([
            'success' => 'New product created and purchase order placed. Receive it when the goods arrive.',
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
        $activeShopId = session('active_shop_id');

        if (!$activeShopId) {
            return redirect()->route('dashboard.shop')->with('error', 'Please select an active shop first to view its purchases.');
        }

        $shop = Shops::findOrFail($activeShopId);

        $purchases = PurchaseInvoice::with('supplier', 'shop', 'items')
            ->where('shop_id', $shop->id)
            ->latest()
            ->get();

        $purchasesByDate = $purchases
            ->groupBy(fn($p) => Carbon::parse($p->purchased_at)->format('Y-m-d'))
            // Same shape ShopsController@show builds — pages.purchases.index
            // is shared by both and reads these keys.
            ->map(fn($items, $date) => [
                'date' => $date,
                'total_amount' => $items->sum('total_amount'),
                'total_paid' => $items->sum('amount_paid'),
                'remaining' => $items->sum('remaining_amount'),
                'items' => $items,
            ])
            ->sortKeysDesc();

        // Orders waiting for delivery — not in stock or totals yet.
        $pendingOrders = PurchaseInvoice::pending()
            ->with('supplier')
            ->withCount('items')
            ->withSum('items as ordered_units', 'ordered_quantity')
            ->where('shop_id', $shop->id)
            ->oldest('ordered_at')
            ->get();

        return view('pages.purchases.page', compact('shop', 'purchases', 'purchasesByDate', 'pendingOrders'));
    }

    /**
     * Receiving form for a pending order: each line as ordered, to confirm
     * the quantity that arrived and the actual prices.
     */
    public function receive(PurchaseInvoice $invoice)
    {
        if (!$invoice->isPending()) {
            return redirect()->route('purchases.history', $invoice->id)->with('error', 'This order has already been ' . $invoice->status . '.');
        }

        $invoice->load('shop', 'supplier', 'items.product.unit');

        return view('pages.purchases.receive', compact('invoice'));
    }

    /**
     * Confirm a delivery: stock goes into the order's shop/store and the
     * purchase is recorded at the received quantities and prices.
     */
    public function confirmReceive(Request $request, PurchaseInvoice $invoice, ReceivePurchaseOrder $receiveOrder)
    {
        $request->validate([
            'payment_type' => 'required|in:cash,credit',
            'amount_paid' => 'nullable|numeric|min:0',
            'lines' => 'required|array',
            'lines.*.quantity' => 'required|integer|min:0',
            'lines.*.purchase_price' => 'required|numeric|min:0',
            'lines.*.selling_price' => 'nullable|numeric|min:0',
            'lines.*.wholesale_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $receiveOrder->execute(
                $invoice,
                $request->input('lines'),
                PaymentType::from($request->payment_type),
                (float) $request->input('amount_paid', 0),
                auth()->id(),
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('error', $e->validator->errors()->first());
        }

        return redirect()->route('purchases.history', $invoice->id)
            ->with('success', 'Order received. Stock has been added to ' . ($invoice->shop->name ?? 'the shop') . '.');
    }

    /**
     * Cancel an order that will not be delivered. Nothing was stocked or paid.
     */
    public function cancel(PurchaseInvoice $invoice)
    {
        if (!$invoice->isPending()) {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $invoice->update(['status' => PurchaseInvoice::CANCELLED]);

        return back()->with('success', 'Purchase order cancelled.');
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

        // One row per invoice of the day, for View / PDF.
        $invoices = PurchaseInvoice::with('supplier')
            ->withSum('items as units', 'quantity')
            ->where('shop_id', $shop->id)
            ->whereDate('purchased_at', $date)
            ->latest('purchased_at')
            ->get();

        return view('pages.purchases.detail', compact('purchases', 'date', 'invoices'));
    }

    /**
     * Add deposit for a credit invoice
     */
    public function deposit(Request $request, PaySupplierCredit $payCredit)
    {
        $request->validate([
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $payCredit->execute(PurchaseInvoice::findOrFail($request->purchase_invoice_id), (float) $request->amount);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Deposit applied successfully.');
    }

    /**
     * Show invoice payment history
     */
    public function history($invoiceId)
    {
        // Loaded into the Credit page's modal by AJAX = bare fragment.
        if (request()->ajax()) {
            $invoice = PurchaseInvoice::withOrders()->with('payments')->findOrFail($invoiceId);

            return view('pages.purchases.history', compact('invoice'));
        }

        // Opened directly = full invoice page in the app layout.
        $invoice = PurchaseInvoice::withOrders()->with('shop', 'supplier', 'items.product', 'payments', 'receivedBy')->findOrFail($invoiceId);
        $returns = \App\Models\PurchaseReturn::with('product', 'processedBy')
            ->whereIn('purchase_item_id', $invoice->items->pluck('id'))
            ->latest()
            ->get();
        $returnedQty = $returns->groupBy('purchase_item_id')->map->sum('quantity');

        return view('pages.purchases.show', compact('invoice', 'returns', 'returnedQty'));
    }

    /**
     * Print invoice
     */
    public function print($invoiceId)
    {
        $invoice = PurchaseInvoice::withOrders()->with('items.product', 'supplier', 'shop')->findOrFail($invoiceId);

        return view('pages.purchases.print', compact('invoice'));
    }
}