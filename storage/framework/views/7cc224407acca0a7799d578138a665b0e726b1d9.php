<?php $__env->startSection('title', 'Price Manager'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Price Manager</h5>
            <small class="text-muted">Edit cost, retail and wholesale prices in bulk. Only the rows you change are saved.</small>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
            <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All shops</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Search</label>
                    <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Name, code or barcode">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Show</label>
                    <select name="flag" class="form-select form-select-sm">
                        <option value="">All products</option>
                        <option value="below_cost" <?php echo e(($flag === 'below_cost') ? 'selected' : ''); ?>>Selling below cost</option>
                        <option value="no_price" <?php echo e(($flag === 'no_price') ? 'selected' : ''); ?>>Missing selling price</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <form method="POST" action="<?php echo e(route('procurement.prices.update')); ?>" id="priceForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Product</th>
                                <th>Shop</th>
                                <th>Stock</th>
                                <th style="min-width:130px">Cost (Tsh)</th>
                                <th style="min-width:130px">Retail (Tsh)</th>
                                <th style="min-width:130px">Wholesale (Tsh)</th>
                                <th>Retail margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr data-row>
                                    <td class="text-start"><?php echo e($product->name); ?><div class="small text-muted"><?php echo e($product->item_code); ?></div></td>
                                    <td><?php echo e($product->shop->name ?? '—'); ?></td>
                                    <td><?php echo e($product->quantity); ?></td>
                                    <?php $__currentLoopData = ['purchase_price', 'selling_price', 'wholesale_price']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end price-input"
                                                   data-field="<?php echo e($field); ?>" data-name="prices[<?php echo e($product->id); ?>][<?php echo e($field); ?>]"
                                                   data-original="<?php echo e($product->$field); ?>" value="<?php echo e($product->$field); ?>"
                                                   aria-label="<?php echo e(str_replace('_', ' ', $field)); ?> for <?php echo e($product->name); ?>">
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <td class="margin-cell"></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="7" class="text-muted py-4">No products match.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <?php echo e($products->links('pagination::bootstrap-4')); ?>

                    <div>
                        <span class="text-muted small me-2" id="changeCount">No changes</span>
                        <button class="btn btn-success" id="saveBtn" disabled><i class="bi bi-save"></i> Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $__env->make('pages.procurement._price_manager_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/procurement/price_manager.blade.php ENDPATH**/ ?>