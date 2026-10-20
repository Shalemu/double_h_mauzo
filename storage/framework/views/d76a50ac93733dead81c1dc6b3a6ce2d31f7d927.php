<?php $__env->startSection('title', 'Fixed Expenses'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-calendar2-check me-2"></i>Fixed Expenses</h5>
                <small class="text-muted">Recurring costs such as rent, salaries and utilities. Total: <strong>Tsh <?php echo e(number_format($fixedExpenses->sum('amount'), 2)); ?></strong></small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFixedExpenseModal"><i class="bi bi-plus-circle"></i> Add fixed expense</button>
        </div>
        <div class="card-body">
            <?php echo $__env->make('pages.overview._shop_filter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr><th>Shop</th><th class="text-start">Title</th><th>Amount (Tsh)</th><th>Note</th><th>Added</th><th style="width:110px"></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $fixedExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($expense->shop->name ?? '—'); ?></td>
                                <td class="text-start"><?php echo e($expense->title); ?></td>
                                <td><?php echo e(number_format($expense->amount, 2)); ?></td>
                                <td><?php echo e($expense->note ?: '—'); ?></td>
                                <td><?php echo e($expense->created_at->format('d M Y')); ?></td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFixed<?php echo e($expense->id); ?>" aria-label="Edit <?php echo e($expense->title); ?>"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?php echo e(route('fixed-expenses.destroy', $expense->id)); ?>" class="d-inline" onsubmit="return confirm('Delete this fixed expense?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($expense->title); ?>"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-muted py-4">No fixed expenses recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__currentLoopData = $fixedExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editFixed<?php echo e($expense->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="<?php echo e(route('fixed-expenses.update', $expense->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit fixed expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?php echo e($expense->title); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Amount (Tsh)</label><input type="number" name="amount" class="form-control" min="0" step="0.01" value="<?php echo e($expense->amount); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"><?php echo e($expense->note); ?></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php echo $__env->make('pages.overview._add_expense_modal', [
    'modalId' => 'addFixedExpenseModal',
    'heading' => 'Add fixed expense',
    'routeName' => 'fixed-expenses.store',
    'withReceipt' => false,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/overview/fixed_expenses.blade.php ENDPATH**/ ?>