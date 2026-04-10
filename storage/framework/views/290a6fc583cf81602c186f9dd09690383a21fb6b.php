<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

        <div class="card-header bg-white">
            <h5>
                Daily Item Sales Detail for <?php echo e(\Carbon\Carbon::parse($date)->format('d M Y')); ?>

                (<?php echo e($shop->name); ?>)
            </h5>
        </div>

        <div class="card-body">

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>S/N</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Staff</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td class="text-start"><?php echo e($row['product']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td><?php echo e(number_format($row['revenue'],2)); ?></td>
                            <td><?php echo e($row['staff']); ?></td>
                            <td><?php echo e(ucfirst($row['sale_type'] ?? 'Retail')); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm return-sale-btn"
                                    data-sale-id="<?php echo e($row['sale_id'] ?? ''); ?>"
                                    data-product-id="<?php echo e($row['product_id'] ?? ''); ?>"
                                    data-product-name="<?php echo e($row['product']); ?>"
                                    data-quantity="<?php echo e($row['quantity']); ?>"
                                    data-price="<?php echo e($row['quantity'] > 0 ? $row['revenue'] / $row['quantity'] : 0); ?>"
                                    data-sale-type="<?php echo e(strtolower($row['sale_type'] ?? 'retail')); ?>">
                                    Return
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">No data found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/sales_returns/detail.blade.php ENDPATH**/ ?>