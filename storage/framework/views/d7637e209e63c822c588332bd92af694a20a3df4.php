<h5>Invoice: <?php echo e($invoice->invoice_number ?? 'No Invoice'); ?></h5>
<p>Supplier: <?php echo e($invoice->supplier->name ?? '-'); ?></p>
<p>Total Amount: Tsh <?php echo e(number_format($invoice->total_amount, 2)); ?></p>
<p>Remaining Credit: Tsh <?php echo e(number_format($invoice->remaining_amount, 2)); ?></p>

<hr>

<h6>Payment History</h6>
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Amount Paid</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($loop->iteration); ?></td>
                <td><?php echo e(number_format($payment->amount, 2)); ?></td>
                <td><?php echo e(\Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="3">No payments found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/purchases/history.blade.php ENDPATH**/ ?>