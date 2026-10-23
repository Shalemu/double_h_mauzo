<?php $__env->startSection('title', 'Deleted Products'); ?>

<?php $__env->startSection('content'); ?>

<div class="dashboard-panel bg-white rounded-3 border p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-trash3 me-2"></i>Deleted Products (<?php echo e($trashedProducts->count()); ?>)</h5>
        <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Shop</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Selling Price</th>
                    <th>Barcode</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $trashedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr id="trash-row-<?php echo e($item->id); ?>">
                        <td><?php echo e($item->name); ?></td>
                        <td><?php echo e($item->brand ?? '-'); ?></td>
                        <td><?php echo e($item->shop->name ?? '-'); ?></td>
                        <td><?php echo e($item->category->name ?? '-'); ?></td>
                        <td><?php echo e(number_format($item->quantity)); ?></td>
                        <td><?php echo e(number_format($item->selling_price, 2)); ?></td>
                        <td><?php echo e($item->barcode ?? '-'); ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-success btn-restore" data-id="<?php echo e($item->id); ?>">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-force-delete" data-id="<?php echo e($item->id); ?>">
                                <i class="bi bi-x-circle"></i> Delete Permanently
                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Trash is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo $__env->make('pages.products._trash_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\trash.blade.php ENDPATH**/ ?>