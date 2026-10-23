<?php $__env->startSection('title', 'Order Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Order Requests</h5>

            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="<?php echo e($status); ?>">
                <select name="shop" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All shops</option>
                    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        </div>

        <div class="card-body">
            <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
            <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>

            <ul class="nav nav-pills mb-3 flex-wrap gap-1">
                <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'purchased' => 'Purchased', 'rejected' => 'Rejected', 'all' => 'All']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($status === $key ? 'active' : ''); ?>"
                           href="<?php echo e(route('admin.orders.index', ['status' => $key, 'shop' => $shopId])); ?>">
                            <?php echo e($label); ?>

                            <span class="badge <?php echo e($status === $key ? 'bg-light text-dark' : 'bg-secondary'); ?> ms-1">
                                <?php echo e($key === 'all' ? $counts->sum() : ($counts[$key] ?? 0)); ?>

                            </span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Shop</th>
                            <th>Requested by</th>
                            <th>Items</th>
                            <th>Total (Tsh)</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($order->id); ?></td>
                                <td><?php echo e($order->shop->name ?? 'N/A'); ?></td>
                                <td><?php echo e($order->staff->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($order->items->count()); ?></td>
                                <td><?php echo e(number_format($order->total_amount, 2)); ?></td>
                                <td><?php echo $__env->make('components.order_status_badge', ['status' => $order->status], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></td>
                                <td><?php echo e($order->created_at->format('d M Y H:i')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.orders.show', $order->id)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> <?php echo e($order->status === 'pending' ? 'Review' : 'View'); ?>

                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-muted py-4">No <?php echo e($status === 'all' ? '' : $status); ?> order requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3"><?php echo e($orders->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\orders\admin_index.blade.php ENDPATH**/ ?>