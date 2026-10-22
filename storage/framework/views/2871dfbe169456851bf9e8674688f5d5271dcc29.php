<?php $__env->startSection('title', 'Request Stock'); ?>

<?php $__env->startSection('content'); ?>

<style>
.order-product-search { position:relative; }
.order-product-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1050; max-height:320px; overflow:auto; display:none; }
.order-product-results.is-open { display:block; }
</style>

<div class="container-fluid mt-4">
    <section class="card mb-5 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Request Stock</h5>
            <small class="text-muted">Admin is notified as soon as you submit, and you'll get a notification when it's approved or rejected.</small>
        </div>

        <div class="card-body">
            <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
            <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

            <form method="POST" action="<?php echo e(route('staff.orders.store')); ?>" id="orderForm">
                <?php echo csrf_field(); ?>

                <!-- Shop -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Shop</label>
                        <input type="text" class="form-control"
                            value="<?php echo e(auth()->guard('staff')->user()->shop->name ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <hr>
                <h6>Add Items</h6>

                <!-- Add Item Row -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4 order-product-search">
                        <input type="search" id="productSearch" class="form-control" autocomplete="off"
                               placeholder="Search product by name or code..." aria-label="Search product">
                        <input type="hidden" id="productId">
                        <div class="list-group shadow order-product-results" id="productResults" role="listbox"></div>
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="quantity" class="form-control" placeholder="Qty" min="1" aria-label="Quantity">
                    </div>

                    <div class="col-md-3">
                        <input type="number" id="selling_price" class="form-control" placeholder="Selling Price" min="0" aria-label="Selling price">
                    </div>

                    <div class="col-md-2">
                        <select id="sale_type" class="form-select" aria-label="Sale type">
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="button" id="addItemBtn" class="btn btn-primary w-100">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Selling Price</th>
                                <th>Total</th>
                                <th>Sale Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Total -->
                <h5 class="text-end mt-3">
                    Total: Tsh <span id="grandTotal">0.00</span>
                </h5>

                <div class="mb-3">
                    <label class="form-label">Note to admin (optional)</label>
                    <textarea name="note" class="form-control" rows="2" maxlength="1000"
                              placeholder="e.g. customers keep asking for this, stock finishes Friday"><?php echo e(old('note')); ?></textarea>
                </div>

                <!-- Hidden input -->
                <input type="hidden" name="items" id="itemsInput">

                <div class="text-end mt-4">
                    <button class="btn btn-success">Submit Order</button>
                </div>
            </form>
        </div>
    </section>
</div>

<?php echo $__env->make('pages.staff.orders._create_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/staff/orders/create.blade.php ENDPATH**/ ?>