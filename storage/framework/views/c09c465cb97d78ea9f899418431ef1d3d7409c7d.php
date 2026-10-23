

<?php $__env->startSection('title', 'Report Issue'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid mt-4">

    <div class="card shadow-sm w-100">
        <div class="card-header text-white" style="background-color: #8b171b;">
            <h5 class="mb-0">Report Shop Issue</h5>
        </div>

        <div class="card-body">

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn text-white" id="toggleIssueForm" aria-expanded="<?php echo e($errors->any() ? 'true' : 'false'); ?>" style="background-color: #8b171b; border-color: #8b171b;">
                    <i class="bi bi-plus-circle me-1"></i> New Issue
                </button>
            </div>

            <div id="newIssueForm" <?php if(!$errors->any()): ?> hidden <?php endif; ?>>
            <form action="<?php echo e(route('staff.report.issue.store')); ?>" method="POST" class="border rounded p-3 p-md-4 bg-light">
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

                    <button type="submit" class="btn text-white" style="background-color: #8b171b; border-color: #8b171b;">
                        Submit Report
                    </button>
                </div>

            </form>
            </div>

        </div>
    </div>

    <div class="card shadow-sm w-100 mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">My Reported Issues</h5>
        </div>
        <div class="card-body">
                <div class="table-responsive border rounded">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Issue Type</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Reported</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $status = $issue->status ?: 'pending';
                                    $statusClass = [
                                        'resolved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'pending' => 'bg-warning text-dark',
                                    ][$status] ?? 'bg-secondary';
                                ?>
                                <tr>
                                    <td class="text-nowrap"><?php echo e(ucfirst($issue->type)); ?></td>
                                    <td><?php echo e($issue->message); ?></td>
                                    <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                    <td class="text-nowrap"><?php echo e($issue->created_at->format('Y-m-d H:i')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">You haven’t reported any issues yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($issues->hasPages()): ?>
                    <div class="mt-3">
                        <?php echo e($issues->links()); ?>

                    </div>
                <?php endif; ?>
        </div>
    </div>

</div>
<script>
    document.getElementById('toggleIssueForm').addEventListener('click', function () {
        const formPanel = document.getElementById('newIssueForm');
        const isExpanded = !formPanel.hidden;
        formPanel.hidden = isExpanded;
        this.setAttribute('aria-expanded', String(!isExpanded));
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/staff/report_issue/index.blade.php ENDPATH**/ ?>