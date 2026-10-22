<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products List</title>
    <?php echo $__env->make('pages.products._pdf_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    <h3>Double H Cosmetics — Products List</h3>
    <div class="meta">
        <?php echo e($shopName ?? 'All shops'); ?>

        <?php if(($filter ?? 'all') !== 'all'): ?> · <?php echo e(ucwords(str_replace('_', ' ', $filter))); ?> <?php endif; ?>
        · <?php echo e(number_format($products->count())); ?> products · <?php echo e(now()->format('d M Y H:i')); ?>

    </div>
    
    <?php $__currentLoopData = $products->chunk(100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <?php if(($shopName ?? 'All shops') === 'All shops'): ?><th>Shop</th><?php endif; ?>
                <th>Brand</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Qty</th>
                <th>Min Qty</th>
                <th>Purchase</th>
                <th>Selling</th>
                <th>Wholesale</th>
                <th>Barcode</th>
                <th>Expire Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($index + 1); ?></td>
                <td><?php echo e($product->name); ?></td>
                <?php if(($shopName ?? 'All shops') === 'All shops'): ?><td><?php echo e($product->shop_name ?? '-'); ?></td><?php endif; ?>
                <td><?php echo e($product->brand ?: '-'); ?></td>
                <td><?php echo e($product->category_name ?? '-'); ?></td>
                <td><?php echo e($product->unit_name ?? '-'); ?></td>
                <td class="num"><?php echo e(number_format((int) $product->quantity)); ?></td>
                <td class="num"><?php echo e(number_format((int) $product->min_quantity)); ?></td>
                <td class="num"><?php echo e(number_format((float) $product->purchase_price, 2)); ?></td>
                <td class="num"><?php echo e(number_format((float) $product->selling_price, 2)); ?></td>
                <td class="num"><?php echo e(number_format((float) $product->wholesale_price, 2)); ?></td>
                <td><?php echo e($product->barcode ?: '-'); ?></td>
                <td><?php echo e($product->expire_date ?: '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\pdf.blade.php ENDPATH**/ ?>