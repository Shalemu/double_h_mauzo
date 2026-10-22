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
        <h5 class="mb-0">New Purchase Order</h5>
        <small class="text-muted">Stock is added when the order is received and confirmed.</small>
    </div>

    <div class="card-body">

        <form method="POST" action="<?php echo e(route('purchases.store')); ?>">
            <?php echo csrf_field(); ?>

            <!-- Shop & Supplier -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Shop</label>
                    <select name="shop_id" id="purchaseShop" class="form-select" required>
                        <option value="">Select Shop</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($shop->id); ?>" <?php echo e((isset($activeShopIdForView) && (int) $activeShopIdForView === $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Supplier</label>
                    <select name="supplier_id" id="purchaseSupplier" class="form-select" required
                            data-products-url="<?php echo e(url('purchases/supplier/__ID__/products')); ?>">
                        <option value="">Select Supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <div class="row g-2 align-items-end">
    <div class="col-md-2">
        <label class="form-label small mb-1" for="product">Product</label>
        <select id="product" class="form-select">
                        <option value="">Select Product</option>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option
                            value="<?php echo e($product->id); ?>"
                            data-buy="<?php echo e($product->purchase_price); ?>"
                            data-retail="<?php echo e($product->selling_price); ?>"
                            data-wholesale="<?php echo e($product->wholesale_price); ?>"
                        >
                            <?php echo e($product->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1" for="quantity">Quantity</label>
        <input type="number" id="quantity" class="form-control" placeholder="Qty" min="1">
    </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="purchase_price">Buying Price (Tsh)</label>
            <input type="number" id="purchase_price" class="form-control" placeholder="Buy Price">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="selling_price">Retail Price (Tsh)</label>
            <input type="number" id="selling_price" class="form-control" placeholder="Retail Price">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1" for="wholesale_price">Wholesale Price (Tsh)</label>
            <input type="number" id="wholesale_price" class="form-control" placeholder="Wholesale Price">
        </div>
        <div class="col-md-2">
            <button type="button" id="addItemBtn" class="btn btn-primary w-100">Add</button>
        </div>
    </div>

            
            <div class="small mt-2" id="supplierProductsHint" aria-live="polite"></div>

            <!-- Items Table -->
            <div class="mt-4">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Buy</th>
                            <th>Retail</th>
                            <th>Wholesale</th>
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
                <button class="btn btn-success">Place order</button>
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

           <form id="supplierForm" method="POST" action="<?php echo e(route('suppliers.store')); ?>">
                <?php echo csrf_field(); ?>

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

<?php echo $__env->make('pages.purchases._create_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\create.blade.php ENDPATH**/ ?>