
<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">

    <h3><?php echo e($shop->name); ?> - Dashboard</h3>

    

    
    <div class="card mt-4 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Staff Reported Issues</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php $__empty_1 = true; $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($feedback->type); ?></td>
                            <td><?php echo e($feedback->message); ?></td>
                            <td>
                                <span class="badge <?php echo e($feedback->status === 'resolved' ? 'bg-success' : 'bg-warning'); ?>">
                                    <?php echo e(ucfirst($feedback->status)); ?>

                                </span>
                            </td>
                           <td><?php echo e($feedback->staff->full_name ?? 'N/A'); ?></td>
                            <td><?php echo e($feedback->created_at->format('Y-m-d H:i')); ?></td>
                            <td>
                                <?php if($feedback->status !== 'resolved'): ?>
                                    <form action="<?php echo e(route('admin.feedback.resolve', $feedback->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button class="btn btn-sm btn-success">Mark as Resolved</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-success">Resolved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-muted">No issues reported yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/report_issue/index.blade.php ENDPATH**/ ?>