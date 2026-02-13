<h5>Expenses for <?php echo e($date); ?></h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>SN</th>
            <th>Title</th>
            <th>Amount (TZS)</th>
            <th>Note</th>
            <th>Added By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($loop->iteration); ?></td>
            <td><?php echo e($expense->title); ?></td>
            <td><?php echo e(number_format($expense->amount, 2)); ?></td>
            <td><?php echo e($expense->note); ?></td>
            <td><?php echo e($expense->user->name ?? 'Unknown'); ?></td>
            <td><?php echo e($expense->created_at->format('H:i')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="6" class="text-center text-muted">No expenses for this date.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/expenses/detail.blade.php ENDPATH**/ ?>