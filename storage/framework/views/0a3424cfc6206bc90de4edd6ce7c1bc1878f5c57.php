<h6>Purchases on <?php echo e($date); ?></h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
            <th>Qty</th>
            <th>Sale Type</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            
            <td><?php echo e($index + 1); ?></td>

            
            <td><?php echo e($p->product?->name ?? '-'); ?></td>

            
            <td><?php echo e($p->invoice?->supplier?->name ?? '-'); ?></td>

            
            <td><?php echo e($p->quantity ?? 0); ?></td>

            
            <td><?php echo e(ucfirst($p->sale_type ?? '-')); ?></td>

            
            <td><?php echo e(number_format($p->purchase_price ?? 0, 2)); ?></td>

            
            <td><?php echo e(number_format(($p->quantity ?? 0) * ($p->purchase_price ?? 0), 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/purchases/detail.blade.php ENDPATH**/ ?>