

<?php $__env->startSection('title', 'Order Details'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components.staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order #<?php echo e($order->id); ?> Details</h5>

            <a href="<?php echo e(route('staff.orders.index')); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>

        <div class="card-body">

            <div class="mb-3">
                <strong>Staff:</strong> <?php echo e($order->staff->first_name ?? 'N/A'); ?> <?php echo e($order->staff->last_name ?? ''); ?> <br>
                <strong>Status:</strong>
                <?php if($order->status == 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif($order->status == 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
                <br>
                <strong>Total Amount:</strong> Tsh <?php echo e(number_format($order->items->sum('total'), 2)); ?> <br>
                <strong>Created At:</strong> <?php echo e($order->created_at->format('d M Y H:i')); ?>

            </div>

            <hr>

            <h6>Items</h6>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Sale Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($item->product->name ?? 'N/A'); ?></td>
                                <td><?php echo e($item->quantity); ?></td>
                                <td>Tsh <?php echo e(number_format($item->price, 2)); ?></td>
                                <td>Tsh <?php echo e(number_format($item->total, 2)); ?></td>
                                <td><?php echo e(ucfirst($item->sale_type)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/orders/show.blade.php ENDPATH**/ ?>