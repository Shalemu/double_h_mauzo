<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo e($sale->id); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .receipt { max-width: 400px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #ccc; padding: 5px; text-align: left; }
        th { text-align: left; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="receipt">
        <h3>My Shop</h3>
        <p>Receipt #<?php echo e($sale->id); ?><br>Date: <?php echo e($sale->created_at->format('d/m/Y H:i')); ?></p>
        <p>Customer: <?php echo e($sale->customer->name ?? 'Walk-in'); ?></p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product->name); ?></td>
                    <td><?php echo e($item->quantity); ?></td>
                    <td><?php echo e(number_format($item->price, 2)); ?></td>
                    <td><?php echo e(number_format($item->discount, 2)); ?></td>
                    <td><?php echo e(number_format(($item->price * $item->quantity) - $item->discount, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <p class="total">Bill Discount: <?php echo e(number_format($sale->bill_discount, 2)); ?><br>
        Grand Total: <?php echo e(number_format($sale->total, 2)); ?></p>

        <p>Thank you for shopping!</p>
    </div>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\sales\receipt.blade.php ENDPATH**/ ?>