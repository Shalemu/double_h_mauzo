<form id="newProductForm" enctype="multipart/form-data" novalidate>
    
    <?php echo csrf_field(); ?>

    <div class="row g-3">

        <!-- Shop Selection -->
        <div class="col-md-6">
            <label>Shop</label>
            <select name="shop_id" class="form-select" required>
                <option value="">Select Shop</option>
                <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($shop->id); ?>"><?php echo e($shop->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <!-- Product Name -->
        <div class="col-md-6">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- Category -->
        <div class="col-md-6">
            <label>Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select Category</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <!-- Unit -->
        <div class="col-md-6">
            <label>Unit</label>
            <select name="unit_id" class="form-select" required>
                <option value="">Select Unit</option>
                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <!-- Quantity -->
        <div class="col-md-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
        </div>

        <!-- Purchase Price -->
        <div class="col-md-3">
            <label>Purchase Price</label>
            <input type="number" name="purchase_price" class="form-control" min="0" step="0.01" required>
        </div>

        <!-- Selling Price -->
        <div class="col-md-3">
            <label>Selling Price</label>
            <input type="number" name="selling_price" class="form-control" min="0" step="0.01">
        </div>

        <!-- Supplier -->
        <div class="col-md-6">
            <label>Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Select Supplier</option>
                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <!-- Payment Type -->
        <div class="col-md-6">
            <label>Payment Type</label>
            <select name="payment_type" id="paymentType" class="form-select" required>
                <option value="">Select Payment Type</option>
                <option value="cash">Cash</option>
                <option value="credit">Credit</option>
            </select>
        </div>

        <!-- Initial Deposit -->
        <div class="col-md-6" id="amountPaidBox" style="display:none;">
            <label>Initial Deposit</label>
            <input type="number" name="amount_paid" id="amountPaid" class="form-control" value="0">
            <small id="remainingCredit" class="text-danger mt-1 d-block">
                Remaining Credit: 0.00
            </small>
        </div>

        <!-- Image -->
        <div class="col-md-6">
            <label>Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
    </div>

    <!-- Buttons -->
    <div class="mt-3 text-end">
        <button type="button" class="btn btn-secondary" id="cancelNewProduct">Back</button>
        <button type="button" class="btn btn-success" id="submitNewProduct">Add & Purchase</button>
    </div>
</form><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/purchases/new_product.blade.php ENDPATH**/ ?>