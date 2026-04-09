<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo e($invoice->invoice_number); ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 28px; }
        .header h3 { margin: 5px 0; font-weight: normal; color: #555; }
        .details { margin-bottom: 20px; }
        .details p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        table th { background-color: #f5f5f5; }
        .totals { width: 300px; float: right; margin-top: 20px; }
        .totals table { border: none; }
        .totals th, .totals td { border: none; text-align: right; padding: 5px 10px; }
        .totals th { font-weight: bold; }
        .totals td { font-size: 16px; }
        .highlight { font-size: 18px; font-weight: bold; color: #d9534f; }
    </style>
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
                <td><?php echo e($item->quantity); ?></td>
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
</html><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/purchases/print.blade.php ENDPATH**/ ?>