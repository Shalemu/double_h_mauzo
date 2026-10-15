<?php $__env->startSection('title', 'Reported Issues'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-circle me-2"></i>Reported Issues</h5>
            <small class="text-muted">Problems staff reported from their shops.</small>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3 gap-1">
                <?php $__currentLoopData = ['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($status === $key ? 'active' : ''); ?>" href="<?php echo e(route('admin.issues', ['status' => $key, 'shop' => $shopId])); ?>">
                            <?php echo e($label); ?>

                            <?php if($key !== 'all'): ?>
                                <span class="badge <?php echo e($status === $key ? 'bg-light text-dark' : 'bg-secondary'); ?> ms-1"><?php echo e((int) ($counts->$key ?? 0)); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <?php echo $__env->make('pages.overview._shop_filter', ['keep' => ['status' => $status]], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>Reported</th><th>Shop</th><th>By</th><th>Type</th><th>Message</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-nowrap"><?php echo e($issue->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($issue->shop->name ?? '—'); ?></td>
                                <td><?php echo e($issue->staff->full_name ?? '—'); ?></td>
                                <td><?php echo e(ucfirst($issue->type)); ?></td>
                                <td style="max-width:420px"><?php echo e($issue->message); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo e($issue->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark'); ?>"><?php echo e(ucfirst($issue->status ?? 'pending')); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if($issue->status !== 'resolved'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.feedback.resolve', $issue->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check2"></i> Resolve</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No <?php echo e($status === 'all' ? '' : $status); ?> issues.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($issues->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\overview\issues.blade.php ENDPATH**/ ?>