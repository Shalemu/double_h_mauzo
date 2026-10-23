<?php $__env->startSection('title', 'Add Product'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('pages.products._create_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">
                Fill in the details below to register a new product
                <?php if($targetShop): ?>
                    in <strong><?php echo e($targetShop->name); ?></strong><?php echo e($targetShop->isStore() ? ' (Store)' : ''); ?>

                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="<?php echo e(route('products.index')); ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-list-ul"></i> Product List
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('products.store')); ?>" enctype="multipart/form-data" id="addProductForm">
        <?php echo csrf_field(); ?>

        <!-- Classification -->
        <div class="card product-form-section accent-primary mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-primary"><i class="bi bi-tags"></i></div>
                <div>
                    <p class="product-form-section-title">Classification</p>
                    <p class="product-form-section-subtitle">Where it's kept, its category and brand</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <?php if($shops->isNotEmpty()): ?>
                        <div class="col-md-4">
                            <label for="shop_id" class="form-label">Shop / Store <span class="text-danger">*</span></label>
                            <select name="shop_id" id="shop_id" class="form-select" required>
                                <option value="" disabled <?php echo e(old('shop_id') ? '' : 'selected'); ?>>-- select shop or store --</option>
                                <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($shop->id); ?>" <?php echo e(old('shop_id') == $shop->id ? 'selected' : ''); ?>><?php echo e($shop->label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="<?php echo e($shops->isNotEmpty() ? 'col-md-4' : 'col-md-6'); ?>">
                        <label for="category_id" class="form-label">Item Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">-- select category --</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="<?php echo e($shops->isNotEmpty() ? 'col-md-4' : 'col-md-6'); ?>">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="">-- select brand --</option>
                            <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brandName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($brandName); ?>" <?php echo e(old('brand') === $brandName ? 'selected' : ''); ?>><?php echo e($brandName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Not listed? <a href="<?php echo e(route('brands.index')); ?>" target="_blank" rel="noopener">Add a brand</a>, then reload this page.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="card product-form-section accent-success mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-success"><i class="bi bi-box-seam"></i></div>
                <div>
                    <p class="product-form-section-title">Product Information</p>
                    <p class="product-form-section-subtitle">Name, barcode, unit, size, colour and photo</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo e(old('name')); ?>" autofocus required>
                    </div>
                    <div class="col-md-6">
                        <label for="barcode" class="form-label">Barcode <span class="text-muted">(optional)</span></label>
                        <input type="text" name="barcode" id="barcode" class="form-control" placeholder="Scan barcode" value="<?php echo e(old('barcode')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="unit_id" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-select" required>
                            <option value="" disabled <?php echo e(old('unit_id') ? '' : 'selected'); ?>>-- select unit --</option>
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unit->id); ?>" <?php echo e(old('unit_id') == $unit->id ? 'selected' : ''); ?>><?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)<?php echo e($unit->baseUnit ? ' — ' . $unit->multiplier . ' ' . $unit->baseUnit->short_name : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="size" class="form-label">Size</label>
                        <input type="text" name="size" id="size" class="form-control" value="<?php echo e(old('size')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="color" class="form-label">Colour</label>
                        <input type="text" name="color" id="color" class="form-control" value="<?php echo e(old('color')); ?>">
                    </div>
                    <div class="col-12">
                        <label for="image" class="form-label">Product Image <span class="text-muted">(optional)</span></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <img id="imagePreview" class="product-image-preview" src="#" alt="Selected product image preview">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card product-form-section accent-warning mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-warning"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <p class="product-form-section-title">Pricing</p>
                    <p class="product-form-section-subtitle">What you pay for it and what you sell it for (TZS)</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="purchase_price" class="form-label">Retail Purchasing Price <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_price" id="purchase_price" class="form-control" min="0" step="any" value="<?php echo e(old('purchase_price')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="selling_price" class="form-label">Retail Selling Price</label>
                        <input type="number" name="selling_price" id="selling_price" class="form-control" min="0" step="any" value="<?php echo e(old('selling_price')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="wholesale_price" class="form-label">Wholesale Selling Price</label>
                        <input type="number" name="wholesale_price" id="wholesale_price" class="form-control" min="0" step="any" value="<?php echo e(old('wholesale_price')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div class="card product-form-section accent-info mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-info"><i class="bi bi-boxes"></i></div>
                <div>
                    <p class="product-form-section-title">Stock</p>
                    <p class="product-form-section-subtitle">Opening quantity, stock alert level, expiry and invoice</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" value="<?php echo e(old('quantity')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="min_quantity" class="form-label">Stock Alert Level</label>
                        <input type="number" name="min_quantity" id="min_quantity" class="form-control" min="0" value="<?php echo e(old('min_quantity')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="expire_date" class="form-label">Expire Date</label>
                        <input type="date" name="expire_date" id="expire_date" class="form-control" value="<?php echo e(old('expire_date')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="<?php echo e(old('invoice_number')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end flex-wrap gap-2 mb-5">
            <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" name="add_another" value="1" class="btn btn-outline-primary px-4">
                <i class="bi bi-plus-circle me-1"></i> Save &amp; Add Another
            </button>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-1"></i> Save Product
            </button>
        </div>
    </form>
</div>

<?php echo $__env->make('pages.products._create_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/products/create.blade.php ENDPATH**/ ?>