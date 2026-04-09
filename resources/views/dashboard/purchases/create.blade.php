<div class="mb-3 d-flex gap-2">
<button type="button" id="btnNewItem" class="btn btn-info"> <i class="bi bi-plus-circle"></i> New Item </button>

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
                <div class="col-md-6 mt-2">
        <label>Invoice Number</label>
        <input type="text" name="invoice_number" class="form-control" placeholder="Enter Invoice Number">
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
                <label>Payment Type</label>
                <select name="payment_type" id="paymentType" class="form-select" required>
                    <option value="">Select Payment Type</option>
                    <option value="cash">Cash</option>
                    <option value="credit">Credit</option>
                </select>
            </div>

            <div class="col-md-6" id="amountPaidBox" style="display:none;">
                <label>Initial Deposit</label>
                <input type="number" name="amount_paid" id="amountPaid" class="form-control" placeholder="Initial Deposit">
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="bi bi-person-plus me-2"></i> Add New Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

           <form id="supplierForm" method="POST" action="{{ route('suppliers.store') }}">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter supplier name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Supplier address">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Supplier
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const supplierForm = document.getElementById('supplierForm');

    supplierForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch("{{ route('suppliers.store') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Supplier added successfully'
            });

            this.reset();

            let modal = bootstrap.Modal.getInstance(document.getElementById('addSupplierModal'));
            modal.hide();
        })
        .catch(error => {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save supplier'
            });
        });
    });
});
</script>