<?php $__env->startSection('title', 'Order #' . $order->id); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<?php echo e($order->id); ?> <?php echo $__env->make('components.order_status_badge', ['status' => $order->status], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></h5>
                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>In stock now</th>
                                    <th>Requested qty</th>
                                    <th>Price (Tsh)</th>
                                    <th>Total (Tsh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($i + 1); ?></td>
                                        <td class="text-start"><?php echo e($item->product->name ?? 'Deleted product'); ?></td>
                                        <td><?php echo e($item->product->quantity ?? '—'); ?></td>
                                        <td><?php echo e($item->quantity); ?></td>
                                        <td><?php echo e(number_format($item->price, 2)); ?></td>
                                        <td><?php echo e(number_format($item->total, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Total</td>
                                    <td><?php echo e(number_format($order->total_amount, 2)); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Shop</dt><dd><?php echo e($order->shop->name ?? 'N/A'); ?></dd>
                        <dt>Requested by</dt><dd><?php echo e($order->staff->full_name ?? 'N/A'); ?><?php echo e($order->staff?->phone ? ' · ' . $order->staff->phone : ''); ?></dd>
                        <dt>Requested at</dt><dd><?php echo e($order->created_at->format('d M Y H:i')); ?></dd>
                        <dt>Staff note</dt><dd><?php echo e($order->note ?: '—'); ?></dd>
                        <?php if($order->reviewed_at): ?>
                            <dt>Reviewed</dt><dd><?php echo e($order->reviewer->name ?? 'Admin'); ?>, <?php echo e($order->reviewed_at->format('d M Y H:i')); ?></dd>
                            <dt>Admin note</dt><dd><?php echo e($order->admin_note ?: '—'); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php
                $canApprove = auth()->user()->hasPermission('order-requests.approve');
                $canMarkPurchased = auth()->user()->hasPermission('order-requests.purchased');
                $canDecide = ($order->status === 'pending' && $canApprove)
                    || ($order->status === 'approved' && ($canApprove || $canMarkPurchased));
            ?>
            <?php if(in_array($order->status, ['pending', 'approved']) && $canDecide): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Decision</div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo e(route('admin.orders.updateStatus', $order->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <label class="form-label">Note to staff (optional)</label>
                            <textarea name="admin_note" class="form-control mb-3" rows="3" maxlength="1000"></textarea>

                            <div class="d-grid gap-2">
                                <?php if($order->status === 'pending'): ?>
                                    <button name="status" value="approved" class="btn btn-success"><i class="bi bi-check2-circle"></i> Approve</button>
                                <?php elseif($canMarkPurchased): ?>
                                    <?php if(auth()->user()->hasPermission('purchases.create')): ?>
                                        <a href="<?php echo e(route('purchases.create')); ?>" class="btn btn-outline-primary"><i class="bi bi-cart-plus"></i> Record the purchase</a>
                                    <?php endif; ?>
                                    <button name="status" value="purchased" class="btn btn-success"><i class="bi bi-bag-check"></i> Mark as purchased</button>
                                <?php endif; ?>
                                <?php if($canApprove): ?>
                                    <button name="status" value="rejected" class="btn btn-outline-danger"
                                            onclick="return confirm('Reject this order request?')"><i class="bi bi-x-circle"></i> Reject</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/orders/show.blade.php ENDPATH**/ ?>