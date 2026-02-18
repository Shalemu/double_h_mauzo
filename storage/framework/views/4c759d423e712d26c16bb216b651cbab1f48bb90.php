<h5>Sales for <?php echo e($date); ?></h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total (TZS)</th>
            <th>Staff</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($loop->iteration); ?></td>
            <td><?php echo e($item['product'] ?? 'Unknown'); ?></td>
            <td><?php echo e($item['quantity'] ?? 0); ?></td>
            <td><?php echo e(number_format($item['revenue'] ?? 0, 2)); ?></td>
            <td><?php echo e($item['staff'] ?? 'Unknown'); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="5" class="text-center text-muted">No sales for this date.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<button class="btn btn-secondary btn-sm mt-2" onclick="document.getElementById('sales-details').style.display='none'; document.getElementById('sales-table-container').style.display='block';">
    ← Back to Sales
</button>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/sales/detail.blade.php ENDPATH**/ ?>