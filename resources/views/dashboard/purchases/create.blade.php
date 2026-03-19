<div class="mb-3 d-flex gap-2">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-circle"></i> New Item
    </button>

    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="bi bi-person-plus"></i> Add Supplier
    </button>

    <button type="button" id="cancel-add-purchase" class="btn btn-danger">
        Cancel
    </button>
</div>

<section class="card mb-5 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Add New Purchase</h5>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <!-- Shop & Supplier -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Shop</label>
                    <select name="shop_id" class="form-select" required>
                        <option value="">Select Shop</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>
            <h6>Add Items</h6>

            <!-- Item Input -->
            <div class="row g-2">
    <div class="col-md-3">
        <select id="product" class="form-select">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                        <option 
                            value="{{ $product->id }}"
                            data-buy="{{ $product->purchase_price }}"
                            data-sell="{{ $product->selling_price }}"
                        >
                            {{ $product->name }}
                        </option>
                        @endforeach
                    </select>
    </div>
    <div class="col-md-2">
        <input type="number" id="quantity" class="form-control" placeholder="Qty" min="1">
    </div>
        <div class="col-md-2">
            <input type="number" id="purchase_price" class="form-control" placeholder="Buy Price">
        </div>
        <div class="col-md-2">
            <input type="number" id="selling_price" class="form-control" placeholder="Sell Price">
        </div>
        <div class="col-md-2">
            <select id="saleType" class="form-select">
                <option value="retail">Retail</option>
                <option value="wholesale">Wholesale</option>
                <option value="both">Both</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" id="addItemBtn" class="btn btn-primary w-100">Add</button>
        </div>
    </div>

            <!-- Items Table -->
            <div class="mt-4">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Buy</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <h5 class="text-end">Total: Tsh <span id="grandTotal">0</span></h5>

          
            <!-- PAYMENT -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <select name="payment_type" id="paymentType" class="form-select" required>
                        <option value="">Payment Type</option>
                        <option value="cash">Cash</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <!-- UNIQUE amountPaidBox -->
                <div class="col-md-6" id="amountPaidBox" style="display:none;">
                    <input type="number" name="amount_paid" id="amountPaid" class="form-control" placeholder="Amount Paid">
                    <small id="remainingCredit" class="text-danger mt-1 d-block">Remaining Credit: 0</small>
                </div>
            </div>

            <!-- Hidden -->
            <input type="hidden" name="items" id="itemsInput">

            <div class="text-end mt-4">
                <button class="btn btn-success">Submit</button>
            </div>

        </form>

    </div>
</section>

