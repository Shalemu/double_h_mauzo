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

<<<<<<< Updated upstream:app/Http/Controllers/PurchasesController.php
        return view('pages.purchases.new_product', compact(
            'units', 'categories', 'shops', 'suppliers'
=======
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
            'brand'           => $product->brand,
            'size'            => $product->size,
            'category_id'     => $product->category_id,
            'unit_id'         => $product->unit_id,
            // Y-m-d; pre-fills the expiry on New Item Purchase (can be changed for this delivery).
            'expire_date'     => $product->expire_date ? \Carbon\Carbon::parse($product->expire_date)->toDateString() : null,
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
        $brands = \App\Models\Brand::orderBy('name')->pluck('name');
        $activeShopId = $request->query('shop') ?: session('active_shop_id');

        return view('pages.purchases.new_items', compact(
            'units', 'categories', 'brands', 'shops', 'suppliers', 'activeShopId'
>>>>>>> Stashed changes:app/Http/Controllers/Web/PurchasesController.php
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
<<<<<<< Updated upstream:app/Http/Controllers/PurchasesController.php
            'image' => 'nullable|image|max:2048',
=======
            'invoice_number' => 'nullable|string|max:100',
            'items' => 'required|array|min:1|max:100',
            // Set when the barcode matched a product the shop already has: that product is
            // ordered (stock added on receive) instead of a new one being created.
            'items.*.product_id' => 'nullable|integer',
            'items.*.name' => 'required|string|max:255',
            'items.*.category_id' => 'nullable|exists:product_categories,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
            'items.*.wholesale_price' => 'nullable|numeric|min:0',
            'items.*.barcode' => 'nullable|string|max:100',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.size' => 'nullable|string|max:100',
            'items.*.min_quantity' => 'nullable|integer|min:0',
            'items.*.expire_date' => 'nullable|date|after_or_equal:today',
            'items.*.image' => 'nullable|image|max:2048',
        ], [
            'items.*.min_quantity.integer' => 'Item :position: min stock must be a whole number.',
            'items.*.expire_date.after_or_equal' => 'Item :position: the expiry date is already past.',
            'items.required' => 'Add at least one item.',
            'items.*.name.required' => 'Item :position: enter the product name.',
            'items.*.unit_id.required' => 'Item :position: choose a unit.',
            'items.*.quantity.required' => 'Item :position: enter the quantity.',
            'items.*.quantity.min' => 'Item :position: the quantity must be at least 1.',
            'items.*.purchase_price.required' => 'Item :position: enter the buying price.',
            'items.*.image.max' => 'Item :position: the photo must be 2 MB or smaller.',
>>>>>>> Stashed changes:app/Http/Controllers/Web/PurchasesController.php
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

<<<<<<< Updated upstream:app/Http/Controllers/PurchasesController.php
            // Same as an ordinary purchase: a pending order, stocked when received.
            $invoice = $placeOrder->execute(PurchaseData::fromArray([
=======
        // Rows for products the shop already has (found by barcode) vs. brand-new ones.
        $existingRows = array_filter($items, fn ($item) => !empty($item['product_id']));
        $newRows = array_diff_key($items, $existingRows);

        $existingIds = array_map(fn ($item) => (int) $item['product_id'], $existingRows);
        if (count($existingIds) !== count(array_unique($existingIds))) {
            return back()->withInput()->with('error', 'The same existing product is on the order twice. Combine them into one row.');
        }
        $existingProducts = Products::where('shop_id', $request->shop_id)->whereIn('id', $existingIds)->get()->keyBy('id');
        foreach ($existingRows as $item) {
            if (!$existingProducts->has((int) $item['product_id'])) {
                return back()->withInput()->with('error', "\"{$item['name']}\" is not a product of this shop (was the shop changed after scanning?). Remove it and scan it again.");
            }
        }

        // The same new product (name + brand + size) twice on one order, or already in this shop, is a mistake.
        // "Lotion 200ml" and "Lotion 400ml" are different products.
        $norm = fn ($value) => mb_strtolower(trim((string) $value));
        $identities = array_map(fn ($item) => $norm($item['name']) . '|' . $norm($item['brand'] ?? '') . '|' . $norm($item['size'] ?? ''), $newRows);
        if (count($identities) !== count(array_unique($identities))) {
            return back()->withInput()->with('error', 'The same product (name, brand and size) is on the order twice. Combine them into one row.');
        }
        $existing = [];
        foreach ($newRows as $item) {
            $found = Products::where('shop_id', $request->shop_id)
                ->whereRaw('LOWER(TRIM(name)) = ?', [$norm($item['name'])])
                ->whereRaw("LOWER(TRIM(COALESCE(brand, ''))) = ?", [$norm($item['brand'] ?? '')])
                ->whereRaw("LOWER(TRIM(COALESCE(size, ''))) = ?", [$norm($item['size'] ?? '')])
                ->value('name');
            if ($found) {
                $existing[] = $found . (!empty($item['size']) ? ' ' . $item['size'] : '');
            }
        }
        if ($existing) {
            return back()->withInput()->with('error', 'Already in this shop: ' . implode(', ', $existing)
                . '. Scan its barcode to order the existing product, or use New Purchase Order.');
        }

        // A new product's barcode: not twice on this order, not already used in the shop.
        $barcodes = array_filter(array_map(fn ($item) => trim((string) ($item['barcode'] ?? '')), $newRows));
        if (count($barcodes) !== count(array_unique($barcodes))) {
            $repeated = array_unique(array_diff_assoc($barcodes, array_unique($barcodes)));
            return back()->withInput()->with('error', 'Barcode ' . implode(', ', $repeated) . ' is on more than one item. Each product needs its own barcode.');
        }
        foreach ($barcodes as $key => $barcode) {
            if ($owner = Products::barcodeOwner($request->shop_id, $barcode)) {
                return back()->withInput()->with('error', "Barcode {$barcode} (\"{$items[$key]['name']}\") is already used by \"{$owner->name}\" in this shop. Each product needs its own barcode.");
            }
        }

        $invoice = DB::transaction(function () use ($request, $items, $existingProducts, $placeOrder) {
            $lines = [];

            foreach ($items as $key => $item) {
                if (!empty($item['product_id'])) {
                    // Existing product: nothing new is created; it is ordered like any other line
                    // and receiving adds to its stock. A new min stock level applies now.
                    $product = $existingProducts[(int) $item['product_id']];
                    if (isset($item['min_quantity']) && $item['min_quantity'] !== '' && $item['min_quantity'] !== null) {
                        $product->update(['min_quantity' => (int) $item['min_quantity']]);
                    }
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
                        'expire_date' => $item['expire_date'] ?? null,
                    ];
                    continue;
                }

                $image = $request->file("items.{$key}.image");
                $product = Products::create([
                    'name' => trim($item['name']),
                    'shop_id' => $request->shop_id,
                    'category_id' => $item['category_id'] ?? null,
                    'unit_id' => $item['unit_id'],
                    'barcode' => ($item['barcode'] ?? null) ?: null,
                    'brand' => ($item['brand'] ?? null) ?: null,
                    'size' => ($item['size'] ?? null) ?: null,
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'] ?? 0,
                    'wholesale_price' => $item['wholesale_price'] ?? 0,
                    'min_quantity' => isset($item['min_quantity']) && $item['min_quantity'] !== '' ? (int) $item['min_quantity'] : 0,
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
                    'expire_date' => $item['expire_date'] ?? null, // pre-filled on the Receive page
                ];
            }

            // Same as an ordinary purchase: one pending order, stocked when received.
            return $placeOrder->execute(PurchaseData::fromArray([
>>>>>>> Stashed changes:app/Http/Controllers/Web/PurchasesController.php
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

<<<<<<< Updated upstream:app/Http/Controllers/PurchasesController.php
        return response()->json([
            'success' => 'New product created and purchase order placed. Receive it when the goods arrive.',
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);
=======
        [$emailed, $emailNote] = $emailInvoice->execute($invoice);
        $parts = array_filter([
            count($newRows) ? count($newRows) . ' new product(s) created' : null,
            count($existingRows) ? count($existingRows) . ' existing product(s) added' : null,
        ]);

        return redirect()->route('purchases.new_product')
            ->with('success', implode(' and ', $parts) . ' on one order, assigned to the supplier. Receive the order when the goods arrive to add the stock. ' . $emailNote)
            ->with('purchase_invoice_id', $invoice->id)
            ->with('purchase_invoice_emailed', $emailed)
            ->with('purchase_invoice_email_note', $emailNote);
>>>>>>> Stashed changes:app/Http/Controllers/Web/PurchasesController.php
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