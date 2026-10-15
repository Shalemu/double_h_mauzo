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
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-restore').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            Swal.fire({
                title: 'Restore this product?',
                text: 'It will be added back to the active product list.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(`/deleted-products/restore/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Restored!', data.success, 'success');
                        document.getElementById(`trash-row-${id}`)?.remove();
                    } else {
                        Swal.fire('Error', data.error ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
            });
        });
    });

    document.querySelectorAll('.btn-force-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            Swal.fire({
                title: 'Delete permanently?',
                text: 'This cannot be undone — the product will be gone for good.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete permanently!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(`/deleted-products/force-delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.success, 'success');
                        document.getElementById(`trash-row-${id}`)?.remove();
                    } else {
                        Swal.fire('Error', data.error ?? 'Something went wrong.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
            });
        });
    });

});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/products/trash.blade.php ENDPATH**/ ?>