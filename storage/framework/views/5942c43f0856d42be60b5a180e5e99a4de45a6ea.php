

<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components.staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Orders</h5>

            <a href="<?php echo e(route('staff.orders.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Order
            </a>
        </div>

        <div class="card-body">

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $total = $order->items->sum('total');
                                $count = $order->items->count();
                            ?>

                            <tr>
                                <td><?php echo e($index + 1); ?></td>

                                <td><?php echo e($count); ?></td>

                                <td>Tsh <?php echo e(number_format($total, 2)); ?></td>

                                <td>
                                    <?php if($order->status == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif($order->status == 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo e($order->created_at->format('d M Y')); ?></td>

                                <td>
                                    <a href="<?php echo e(route('staff.orders.show', $order->id)); ?>" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/orders/index.blade.php ENDPATH**/ ?>