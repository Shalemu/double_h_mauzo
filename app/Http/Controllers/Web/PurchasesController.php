<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Supplier;
use App\Models\Shops;
use App\Models\ProductCategory;
use App\Models\Units;
use App\Models\PurchaseInvoice;
use App\Models\SupplierProduct;
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
     * Purchases › New Purchase Order: order existing products from a supplier.
     * ?shop= preselects the shop (e.g. coming from a shop's page).
     */
    public function create(Request $request)
    {
        $activeShopId = $request->query('shop') ?: session('active_shop_id');

        $products = Products::when($activeShopId, fn($q) => $q->where('shop_id', $activeShopId))
            ->orderByDesc('quantity')
            ->limit(500)
            ->get();

        $suppliers = Supplier::all();
        $shops = Shops::all();
        $categories = ProductCategory::all();
        $units = Units::all();
        $activeShopIdForView = $activeShopId;

        return view('pages.purchases.create_page', compact(
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
     * New Purchase Order: find a product in the shop by its barcode (scanned
     * or typed), with everything the item row needs. When a supplier is
     * chosen, their price is used if the product is assigned to them.
     */
    public function productByBarcode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'shop' => 'required|integer',
            'supplier' => 'nullable|integer',
        ], ['shop.required' => 'Choose the shop first.']);

        $product = Products::with('unit:id,name,short_name')
            ->where('shop_id', $request->shop)
            ->where('barcode', trim($request->code))
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'No product with barcode ' . trim($request->code) . ' in this shop. If it is new, use New Item Purchase.',
            ], 404);
        }

        $assignment = $request->supplier
            ? SupplierProduct::where('supplier_id', $request->supplier)->where('product_id', $product->id)->first()
            : null;

        // When it was last received, and at what price.
        $last = \App\Models\PurchaseItem::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->latest('id')
            ->first(['purchase_price', 'created_at']);

        return response()->json([
            'id'              => $product->id,
            'name'            => $product->name,
            'barcode'         => $product->barcode,
            'unit'            => $product->unit->short_name ?? $product->unit->name ?? null,
            'quantity'        => (int) $product->quantity,
            'min_quantity'    => (int) $product->min_quantity,
            'purchase_price'  => (float) $product->purchase_price,
            'selling_price'   => (float) $product->selling_price,
            'wholesale_price' => (float) $product->wholesale_price,
            'supplier_price'  => $assignment ? (float) $assignment->supplier_price : null,
            'assigned'        => (bool) $assignment,
            'last_price'      => $last ? (float) $last->purchase_price : null,
            'last_date'       => $last ? $last->created_at->format('d M Y') : null,
        ]);
    }

    /**
     * Purchases › New Item Purchase: products that aren't in the system yet,
     * bought from one supplier. "Add item" repeats a row per product.
     */
    public function newProduct(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $shops = Shops::orderBy('name')->get();
        $categories = ProductCategory::whereNull('parent_id')->orderBy('name')->get();
        $units = Units::orderBy('name')->get();
        $activeShopId = $request->query('shop') ?: session('active_shop_id');

        return view('pages.purchases.new_items', compact(
            'units', 'categories', 'shops', 'suppliers', 'activeShopId'
        ));
    }

    /**
     * Purchases › Receive Orders: every order still waiting for delivery,
     * across all shops, ready to be received.
     */
    public function receiving(Request $request)
    {
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');
        $supplierId = $request->query('supplier');

        $orders = PurchaseInvoice::pending()
            ->with('supplier', 'shop')
            ->withCount('items')
            ->withSum('items as ordered_units', 'ordered_quantity')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->oldest('ordered_at')
            ->paginate(20)
            ->withQueryString();

        $shops = Shops::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('pages.purchases.receiving', compact('orders', 'shops', 'suppliers', 'shopId', 'supplierId'));
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
     * New Item Purchase: create each new product in the chosen shop, assign it
     * to the supplier (Supplier Products, at the buying price), and place one
     * purchase order for all of them. Stock comes in when the order is received.
     */
    public function storeNewProduct(Request $request, PlacePurchaseOrder $placeOrder, EmailInvoiceToSupplier $emailInvoice)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'amount_paid' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:100',
            'items' => 'required|array|min:1|max:100',
            'items.*.name' => 'required|string|max:255',
            'items.*.category_id' => 'nullable|exists:product_categories,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.wholesale_price' => 'nullable|numeric|min:0',
            'items.*.barcode' => 'nullable|string|max:100',
            'items.*.image' => 'nullable|image|max:2048',
        ], [
            'items.required' => 'Add at least one item.',
            'items.*.name.required' => 'Item :position: enter the product name.',
            'items.*.unit_id.required' => 'Item :position: choose a unit.',
            'items.*.quantity.required' => 'Item :position: enter the quantity.',
            'items.*.quantity.min' => 'Item :position: the quantity must be at least 1.',
            'items.*.purchase_price.required' => 'Item :position: enter the buying price.',
            'items.*.image.max' => 'Item :position: the photo must be 2 MB or smaller.',
        ]);

        // Keep the form's own keys (rows can be removed, leaving gaps like 0, 2, 5):
        // each row's photo is found under the same key.
        $items = $request->input('items');

        // The same name twice on one order, or a product this shop already has, is a mistake.
        $names = array_map(fn ($item) => mb_strtolower(trim($item['name'])), $items);
        if (count($names) !== count(array_unique($names))) {
            return back()->withInput()->with('error', 'The same product name is on the order twice. Combine them into one row.');
        }
        $existing = Products::where('shop_id', $request->shop_id)
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $names)
            ->pluck('name');
        if ($existing->isNotEmpty()) {
            return back()->withInput()->with('error', 'Already in this shop: ' . $existing->implode(', ')
                . '. Order existing products from New Purchase Order instead.');
        }

        // A barcode belongs to one product per shop: not twice on this order, not already in the shop.
        $barcodes = array_filter(array_map(fn ($item) => trim((string) ($item['barcode'] ?? '')), $items));
        if (count($barcodes) !== count(array_unique($barcodes))) {
            $repeated = array_unique(array_diff_assoc($barcodes, array_unique($barcodes)));
            return back()->withInput()->with('error', 'Barcode ' . implode(', ', $repeated) . ' is on more than one item. Each product needs its own barcode.');
        }
        foreach ($barcodes as $key => $barcode) {
            if ($owner = Products::barcodeOwner($request->shop_id, $barcode)) {
                return back()->withInput()->with('error', "Barcode {$barcode} (\"{$items[$key]['name']}\") is already used by \"{$owner->name}\" in this shop. Each product needs its own barcode.");
            }
        }

        $invoice = DB::transaction(function () use ($request, $items, $placeOrder) {
            $lines = [];

            foreach ($items as $key => $item) {
                $image = $request->file("items.{$key}.image");
                $product = Products::create([
                    'name' => trim($item['name']),
                    'shop_id' => $request->shop_id,
                    'category_id' => $item['category_id'] ?? null,
                    'unit_id' => $item['unit_id'],
                    'barcode' => ($item['barcode'] ?? null) ?: null,
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'] ?? 0,
                    'wholesale_price' => $item['wholesale_price'] ?? 0,
                    'quantity' => 0,
                    'image' => $image ? $image->store('products', 'public') : null,
                    'admin_id' => auth()->id(),
                ]);

                // Bought from this supplier → it is one of the supplier's products.
                SupplierProduct::updateOrCreate(
                    ['supplier_id' => $request->supplier_id, 'product_id' => $product->id],
                    ['supplier_price' => $item['purchase_price'], 'assigned_by' => auth()->id()]
                );

                $lines[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'] ?? null,
                    'wholesale_price' => $item['wholesale_price'] ?? null,
                ];
            }

            // Same as an ordinary purchase: one pending order, stocked when received.
            return $placeOrder->execute(PurchaseData::fromArray([
                'shop_id' => $request->shop_id,
                'supplier_id' => $request->supplier_id,
                'payment_type' => $request->payment_type,
                'amount_paid' => $request->amount_paid,
                'invoice_number' => $request->invoice_number,
                'items' => $lines,
            ]));
        });

        [$emailed, $emailNote] = $emailInvoice->execute($invoice);
        $count = count($items);

        return redirect()->route('purchases.new_product')
            ->with('success', "{$count} new product(s) created, assigned to the supplier, and ordered. Receive the order when the goods arrive. {$emailNote}")
            ->with('purchase_invoice_id', $invoice->id)
            ->with('purchase_invoice_emailed', $emailed)
            ->with('purchase_invoice_email_note', $emailNote);
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
            'lines.*.min_quantity' => 'nullable|integer|min:0',
            'lines.*.expire_date' => 'nullable|date|after_or_equal:today',
        ], [
            'lines.*.min_quantity.min' => 'Min stock can\'t be negative.',
            'lines.*.expire_date.after_or_equal' => 'An expiry date is already past. Goods that have expired can\'t be received into stock.',
            'lines.*.expire_date.date' => 'Enter a valid expiry date.',
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