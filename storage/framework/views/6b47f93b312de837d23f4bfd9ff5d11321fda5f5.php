<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Sales PDF</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        table, th, td { border: 1px solid black; padding: 5px; }
        th { background-color: #f0f0f0; }
        td { text-align: center; }
        td.text-left { text-align: left; }
    </style>
</head>
<body>
    <h4>Daily Item Sales Detail for <?php echo e(\Carbon\Carbon::parse($date)->format('d M Y')); ?></h4>
    <h5><?php echo e($shop->name ?? 'Double H Cosmetics Shop'); ?></h5>

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Item</th>
                <th>Quantity Sold</th>
                <th>Total (TZS)</th>
                <th>Sold by</th>
            
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $itemRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td class="text-left"><?php echo e($row['product']); ?></td>
                    <td><?php echo e($row['quantity']); ?></td>
                    <td><?php echo e(number_format($row['revenue'], 2)); ?></td>
                    <td class="text-left"><?php echo e($row['staff']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/sales/detail-pdf.blade.php ENDPATH**/ ?>