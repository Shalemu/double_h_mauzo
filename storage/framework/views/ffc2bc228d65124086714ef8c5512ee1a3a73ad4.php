<h6>Purchases on <?php echo e($date); ?></h6>
<table class="table table-sm table-bordered text-center">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Supplier</th>
            <th>Qty</th>
            <th>Retail Price</th>
            <th>Wholesale Price</th>
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

            
            <td><?php echo e($p->product?->selling_price ? number_format($p->product->selling_price, 2) : '-'); ?></td>

            
            <td><?php echo e($p->product?->wholesale_price ? number_format($p->product->wholesale_price, 2) : '-'); ?></td>

            
            <td><?php echo e(number_format($p->purchase_price ?? 0, 2)); ?></td>

            
            <td><?php echo e(number_format(($p->quantity ?? 0) * ($p->purchase_price ?? 0), 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/purchases/detail.blade.php ENDPATH**/ ?>