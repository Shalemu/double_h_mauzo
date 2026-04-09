

<div class="container-fluid mt-4 main-content">

    <div class="card shadow-sm" style="max-width: 1300px; margin: 0 auto;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Orders for Shop: <?php echo e($shop->name ?? 'N/A'); ?></h5>
        </div>

        <div class="card-body">

            
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Items</th>
                            <th>Total (Tsh)</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $order->load('staff', 'items.product'); // ensure relations loaded
                                $total = $order->items->sum('total') ?? 0;
                                $count = $order->items->count() ?? 0;
                            ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($order->staff->full_name ?? 'N/A'); ?></td>
                                <td><?php echo e($count); ?></td>
                                <td><?php echo e(number_format($total, 2)); ?></td>
                                <td>
                                    <?php switch($order->status):
                                        case ('pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            <?php break; ?>
                                        <?php case ('approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                            <?php break; ?>
                                        <?php case ('rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-secondary">Unknown</span>
                                    <?php endswitch; ?>
                                </td>
                                <td><?php echo e(optional($order->created_at)->format('d M Y H:i') ?? 'N/A'); ?></td>
                                <td>
                                    
                                    <button 
                                        class="btn btn-sm btn-info view-order-btn"
                                        data-order='<?php echo json_encode($order->toArray(), 15, 512) ?>'
                                    >
                                        <i class="bi bi-eye"></i> View
                                    </button>

                                    
                                    <?php if($order->status == 'pending'): ?>
                                        <form action="<?php echo e(route('admin.orders.updateStatus', $order->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>

                                        <form action="<?php echo e(route('admin.orders.updateStatus', $order->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                <div class="modal fade" id="orderModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Order Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body" id="orderDetailsContent">
                                
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.view-order-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const order = JSON.parse(this.getAttribute('data-order'));

            // Format staff name
            const staffName = order.staff?.full_name ?? 'N/A';
            const totalAmount = Number(order.total_amount ?? order.items.reduce((sum, i) => sum + i.total, 0));

            // Build HTML
            let html = `
                <p><strong>Staff:</strong> ${staffName}</p>
                <p><strong>Status:</strong> ${order.status}</p>
                <p><strong>Total:</strong> Tsh ${totalAmount.toLocaleString()}</p>
                <hr>
                <h6>Items</h6>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            order.items.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.product?.name ?? 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>Tsh ${Number(item.price).toLocaleString()}</td>
                        <td>Tsh ${Number(item.total).toLocaleString()}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;

            document.getElementById('orderDetailsContent').innerHTML = html;

            new bootstrap.Modal(document.getElementById('orderModal')).show();
        });
    });
});
</script><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/orders/index.blade.php ENDPATH**/ ?>