<div class="container-fluid mt-4">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <div class="card-header bg-white">
            <h5>
                Daily Purchase Detail for <?php echo e(\Carbon\Carbon::parse($date)->format('d M Y')); ?>

                (<?php echo e($shop->name); ?>)
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td class="text-start"><?php echo e($row['product']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td><?php echo e(number_format($row['total'],2)); ?></td>
                            <td><?php echo e($row['supplier']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm return-purchase-btn"
                                    data-purchase-item-id="<?php echo e($row['purchase_item_id'] ?? ''); ?>"
                                    data-product-id="<?php echo e($row['product_id'] ?? ''); ?>"
                                    data-product-name="<?php echo e($row['product']); ?>"
                                    data-quantity="<?php echo e($row['quantity']); ?>"
                                    data-price="<?php echo e($row['quantity'] > 0 ? $row['total'] / $row['quantity'] : 0); ?>">
                                    Return
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6">No data found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/purchase_returns/detail.blade.php ENDPATH**/ ?>