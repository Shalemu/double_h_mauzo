<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo e($invoice->invoice_number); ?></title>
    <?php echo $__env->make('pages.purchases._print_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h2><?php echo e($invoice->shop->name ?? 'Shop'); ?></h2>
        <h3>Invoice #<?php echo e($invoice->invoice_number ?? '-'); ?></h3>
    </div>

    <!-- Supplier & Date -->
    <div class="details">
        <p><strong>Supplier:</strong> <?php echo e($invoice->supplier->name ?? '-'); ?></p>
        <p><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('Y-m-d')); ?></p>
    </div>

    <!-- Products Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($item->product->name ?? '-'); ?></td>
                <td><?php echo e($invoice->isPending() ? $item->ordered_quantity : $item->quantity); ?></td>
                <td><?php echo e(number_format($item->purchase_price,2)); ?></td>
                <td><?php echo e(number_format($item->total,2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals">
        <table>
            <tr>
                <th>Total Amount:</th>
                <td><?php echo e(number_format($invoice->total_amount,2)); ?> Tsh</td>
            </tr>
            <tr>
                <th>Paid:</th>
                <td><?php echo e(number_format($invoice->amount_paid,2)); ?> Tsh</td>
            </tr>
            <tr class="highlight">
                <th>Remaining:</th>
                <td><?php echo e(number_format($invoice->remaining_amount,2)); ?> Tsh</td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>

    <script>
        window.print(); // Auto print when page loads
    </script>
</div>
</body>
</html><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\print.blade.php ENDPATH**/ ?>