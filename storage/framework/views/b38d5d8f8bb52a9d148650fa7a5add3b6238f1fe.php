

<?php $__env->startSection('title', 'Create Order'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components.staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="container mt-4">

    <div class="card shadow-sm" style="max-width: 700px; margin:auto;">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Report Shop Issue</h5>
        </div>

        <div class="card-body">

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('staff.report.issue.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                
                <div class="mb-3">
                    <label class="form-label">Issue Type</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select issue</option>
                        <option value="stock">Stock Problem</option>
                        <option value="system">System Error</option>
                        <option value="customer">Customer Issue</option>
                        <option value="finance">Finance Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="message" class="form-control" rows="5"
                        placeholder="Explain the issue clearly..." required></textarea>
                </div>

                
                <div class="d-flex justify-content-between">
                    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">
                        Back
                    </a>

                    <button type="submit" class="btn btn-danger">
                        Submit Report
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/report_issue/index.blade.php ENDPATH**/ ?>