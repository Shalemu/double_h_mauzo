

<?php $__env->startSection('content'); ?>

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header">
            <h5>Edit Fixed Expense</h5>
        </div>

        <div class="card-body">

            <form action="<?php echo e(route('fixed-expenses.update', $expense->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo e($expense->title); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control"
                           value="<?php echo e($expense->amount); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control"><?php echo e($expense->note); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Expense
                </button>

                <a href="<?php echo e(route('fixed-expenses.index', $shop->id)); ?>"
                   class="btn btn-secondary">
                   Cancel
                </a>

            </form>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\fixed_expenses\edit.blade.php ENDPATH**/ ?>