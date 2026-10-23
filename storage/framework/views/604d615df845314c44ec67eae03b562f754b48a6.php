<div class="container-fluid mt-4">

    <div class="card shadow-sm p-3 w-100" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-trash me-2"></i>Deleted Products (Trash)</h4>
            <span class="text-muted">Total: <?php echo e($products->count()); ?> item(s)</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center" style="min-width: 900px;">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr id="row-<?php echo e($product->id); ?>">
                        <td class="text-start"><?php echo e($product->name); ?></td>
                        <td><?php echo e($product->quantity); ?></td>
                        <td><?php echo e($product->unit ? $product->unit->name : '-'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-success btn-restore me-1" data-id="<?php echo e($product->id); ?>">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-danger btn-force-delete" data-id="<?php echo e($product->id); ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No deleted products found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php echo $__env->make('pages.deleted_product._index_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('pages.deleted_product._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\deleted_product\index.blade.php ENDPATH**/ ?>