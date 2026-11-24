{{-- New Purchase Order (inside pages.purchases.create_page). Same look as Add Product.
     The element ids are used by initPurchaseForm() in components.dashboard_scripts. --}}
@include('pages.products._create_styles')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <p class="text-muted mb-0">
        Order products you already have from a supplier. Stock is added when the order is <strong>received</strong>.
    </p>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Purchases</a>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-person-plus"></i> Add Supplier
        </button>
        <a href="{{ route('purchases.new_product', array_filter(['shop' => $activeShopIdForView ?? null])) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> New Item Purchase
        </a>
    </div>
</div>

<form method="POST" action="{{ route('purchases.store') }}">
    @csrf

    <!-- Order details -->
    <div class="card product-form-section accent-primary mb-4">
        <div class="product-form-section-header">
            <div class="product-form-section-icon icon-primary"><i class="bi bi-truck"></i></div>
            <div>
                <p class="product-form-section-title">Order Details</p>
                <p class="product-form-section-subtitle">Which shop it's for, who supplies it, and their invoice number</p>
            </div>
        </div>
        <div class="product-form-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="purchaseShop" class="form-label">Shop / Store <span class="text-danger">*</span></label>
                    <select name="shop_id" id="purchaseShop" class="form-select" required>
                        <option value="">-- select shop or store --</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ (isset($activeShopIdForView) && (int) $activeShopIdForView === $shop->id) ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="purchaseSupplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="purchaseSupplier" class="form-select" required
                            data-products-url="{{ url('purchases/supplier/__ID__/products') }}">
                        <option value="">-- select supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="purchaseInvoice" class="form-label">Invoice Number <span class="text-muted">(optional)</span></label>
                    <input type="text" name="invoice_number" id="purchaseInvoice" class="form-control" placeholder="Supplier's invoice number">
                </div>
            </div>
        </div>
    </div>

    <!-- Item entry -->
    <div class="card product-form-section accent-success mb-4">
        <div class="product-form-section-header">
            <div class="product-form-section-icon icon-success"><i class="bi bi-upc-scan"></i></div>
            <div>
                <p class="product-form-section-title">Add Items</p>
                <p class="product-form-section-subtitle">Scan a barcode or pick the product, enter quantity and prices, then Add</p>
            </div>
        </div>
        <div class="product-form-section-body">
            <div class="row g-3">
                {{-- Scan or type a barcode + Enter: finds the product in the chosen shop and fills the row. --}}
                <div class="col-md-5">
                    <label for="purchaseBarcode" class="form-label">Barcode</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc"></i></span>
                        <input type="text" id="purchaseBarcode" class="form-control" placeholder="Scan or type barcode, then Enter"
                               autocomplete="off" data-lookup-url="{{ route('purchases.product_by_barcode') }}">
                        <button type="button" class="btn btn-outline-secondary" id="purchaseBarcodeFind" aria-label="Find product by barcode">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-7">
                    <label for="product" class="form-label">Product <span class="text-danger">*</span></label>
                    <select id="product" class="form-select">
                        <option value="">-- select product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-buy="{{ $product->purchase_price }}"
                                    data-retail="{{ $product->selling_price }}"
                                    data-wholesale="{{ $product->wholesale_price }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mt-1">
                    {{-- Filled by the purchase script: the scanned product's details, and which supplier's products the list shows. --}}
                    <div class="small" id="purchaseBarcodeInfo" aria-live="polite"></div>
                    <div class="small" id="supplierProductsHint" aria-live="polite"></div>
                </div>

                <div class="col-md-3 col-6">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="quantity" class="form-control" min="1" step="1" placeholder="0">
                </div>
                <div class="col-md-3 col-6">
                    <label for="purchase_price" class="form-label">Buying Price <span class="text-danger">*</span></label>
                    <input type="number" id="purchase_price" class="form-control" min="0" step="any" placeholder="Tsh">
                </div>
                <div class="col-md-3 col-6">
                    <label for="selling_price" class="form-label">Retail Price</label>
                    <input type="number" id="selling_price" class="form-control" min="0" step="any" placeholder="Tsh">
                </div>
                <div class="col-md-3 col-6">
                    <label for="wholesale_price" class="form-label">Wholesale Price</label>
                    <input type="number" id="wholesale_price" class="form-control" min="0" step="any" placeholder="Tsh">
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <div class="text-muted">Line total: <strong class="text-body">Tsh <span id="entryLineTotal">0</span></strong></div>
                <button type="button" id="addItemBtn" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-1"></i> Add to order</button>
            </div>
        </div>
    </div>

    <!-- Items on the order -->
    <div class="card shadow-sm mb-4" style="border-radius:1rem; overflow:hidden;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Items on this order <span class="badge bg-secondary" id="itemCount">0</span></h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0" id="itemsTable">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width:40px">#</th>
                        <th class="text-start">Product</th>
                        <th>Qty</th>
                        <th class="text-end">Buy</th>
                        <th class="text-end">Retail</th>
                        <th class="text-end">Wholesale</th>
                        <th class="text-end">Line total</th>
                        <th style="width:70px">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="6" class="text-end">Order total</th>
                        <th class="text-end">Tsh <span id="grandTotal">0.00</span></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payment -->
    <div class="card product-form-section accent-warning mb-4">
        <div class="product-form-section-header">
            <div class="product-form-section-icon icon-warning"><i class="bi bi-cash-coin"></i></div>
            <div>
                <p class="product-form-section-title">Payment</p>
                <p class="product-form-section-subtitle">Cash is paid in full; credit records a deposit and what's still owed</p>
            </div>
        </div>
        <div class="product-form-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="paymentType" class="form-label">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="paymentType" class="form-select" required>
                        <option value="">-- select payment type --</option>
                        <option value="cash">Cash</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="col-md-4" id="amountPaidBox" style="display:none;">
                    <label for="amountPaid" class="form-label">Initial Deposit</label>
                    <input type="number" name="amount_paid" id="amountPaid" class="form-control" min="0" step="any" placeholder="0">
                    <small id="remainingCredit" class="text-danger mt-1 d-block">Remaining Credit: 0</small>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="items" id="itemsInput">

    <div class="d-flex justify-content-end flex-wrap gap-2 mb-5">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
        <button class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i> Place order</button>
    </div>
</form>

@include('pages.purchases._add_supplier_modal')
