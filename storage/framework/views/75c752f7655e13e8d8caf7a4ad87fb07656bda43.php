<!DOCTYPE html>
<html>
<head>
    <title>New Order Notification</title>
</head>
<body>
    <h3>New Order Submitted</h3>

    <p>Staff: <?php echo e($order->staff->first_name); ?> <?php echo e($order->staff->last_name); ?></p>
    <p>Shop: <?php echo e($order->shop->name ?? 'N/A'); ?></p>
    <p>Total Amount: Tsh <?php echo e(number_format($order->total_amount, 2)); ?></p>
    <p>Status: <?php echo e(ucfirst($order->status)); ?></p>

    <h4>Items:</h4>
    <ul>
        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($item->product->name ?? 'N/A'); ?> - Qty: <?php echo e($item->quantity); ?>, Price: Tsh <?php echo e(number_format($item->price, 2)); ?>, Total: Tsh <?php echo e(number_format($item->total, 2)); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <p>Please review and approve the order in the admin dashboard.</p>
</body>
</html><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/emails/new_order_notification.blade.php ENDPATH**/ ?>