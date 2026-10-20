<?php $__env->startSection('title', 'Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Expenses</h5>
                <small class="text-muted">Day-to-day spending. Total for this period: <strong>Tsh <?php echo e(number_format($total, 2)); ?></strong></small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="bi bi-plus-circle"></i> Add expense</button>
        </div>
        <div class="card-body">
            <?php echo $__env->make('pages.overview._shop_filter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Shop</th><th class="text-start">Title</th><th>Amount (Tsh)</th><th>Note</th><th>Receipt</th><th>Recorded by</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-nowrap"><?php echo e($expense->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($expense->shop->name ?? '—'); ?></td>
                                <td class="text-start"><?php echo e($expense->title); ?></td>
                                <td><?php echo e(number_format($expense->amount, 2)); ?></td>
                                <td><?php echo e($expense->note ?: '—'); ?></td>
                                <td>
                                    <?php if($expense->receipt): ?>
                                        <a href="<?php echo e(asset('storage/' . $expense->receipt)); ?>" target="_blank" rel="noopener">View</a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($expense->creator_name); ?></td>
                                <td>
                                    <form method="POST" action="<?php echo e(route('expenses.destroy', $expense->id)); ?>" onsubmit="return confirm('Delete this expense?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($expense->title); ?>"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-muted py-4">No expenses in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($expenses->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>

<?php echo $__env->make('pages.overview._add_expense_modal', [
    'modalId' => 'addExpenseModal',
    'heading' => 'Add expense',
    'routeName' => 'expenses.store',
    'withReceipt' => true,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/overview/expenses.blade.php ENDPATH**/ ?>