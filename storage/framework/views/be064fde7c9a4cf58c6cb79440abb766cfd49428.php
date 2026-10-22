<?php $__env->startSection('title', 'Purchases'); ?>

<?php $__env->startSection('content'); ?>


<?php if(($pendingOrders ?? collect())->isNotEmpty()): ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending orders <span class="badge bg-warning text-dark"><?php echo e($pendingOrders->count()); ?></span></h5>
            <small class="text-muted">Not in stock or totals until received</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Ordered</th>
                            <th>Supplier</th>
                            <th>Lines</th>
                            <th>Units ordered</th>
                            <th>Order value (Tsh)</th>
                            <th>Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><a href="<?php echo e(route('purchases.history', $order->id)); ?>"><?php echo e(\App\Helpers\InvoiceNumber::for($order)); ?></a></td>
                                <td class="text-nowrap"><?php echo e(optional($order->ordered_at)->format('d M Y H:i')); ?></td>
                                <td><?php echo e($order->supplier->name ?? '—'); ?></td>
                                <td><?php echo e($order->items_count); ?></td>
                                <td><?php echo e(number_format($order->ordered_units)); ?></td>
                                <td><?php echo e(number_format($order->total_amount, 2)); ?></td>
                                <td><?php echo e(ucfirst($order->payment_type)); ?></td>
                                <td class="text-nowrap">
                                    <a href="<?php echo e(route('purchases.receive', $order->id)); ?>" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-down"></i> Receive</a>
                                    <form action="<?php echo e(route('purchases.cancel', $order->id)); ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('Cancel this order? Nothing has been stocked or paid.')">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php echo $__env->make('pages.purchases.index', [
    'shop' => $shop,
    'purchases' => $purchases ?? collect(),
    'purchasesByDate' => $purchasesByDate ?? collect(),
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php if(session('purchase_invoice_id') && ($savedInvoice = \App\Models\PurchaseInvoice::withOrders()->with('supplier', 'shop', 'items')->find(session('purchase_invoice_id')))): ?>
    <?php $savedNumber = \App\Helpers\InvoiceNumber::for($savedInvoice); ?>
    <div class="modal fade" id="purchaseDoneModal" tabindex="-1" aria-labelledby="purchaseDoneTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseDoneTitle"><i class="bi bi-check-circle-fill text-success me-2"></i>Purchase order placed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Order</span><strong><?php echo e($savedNumber); ?></strong></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Supplier</span><span><?php echo e($savedInvoice->supplier->name ?? '—'); ?></span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Units ordered</span><span><?php echo e(number_format($savedInvoice->items->sum('ordered_quantity'))); ?></span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Order value</span><strong>Tsh <?php echo e(number_format($savedInvoice->total_amount, 2)); ?></strong></div>

                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        This order is pending. Stock is added when you receive it and confirm what arrived.
                    </div>

                    <div class="alert <?php echo e(session('purchase_invoice_emailed') ? 'alert-success' : 'alert-warning'); ?> small py-2">
                        <i class="bi <?php echo e(session('purchase_invoice_emailed') ? 'bi-envelope-check' : 'bi-envelope-exclamation'); ?> me-1"></i>
                        <?php echo e(session('purchase_invoice_email_note')); ?>

                    </div>

                    <?php echo $__env->make('pages.purchases._invoice_share', ['invoice' => $savedInvoice], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo e(route('purchases.history', $savedInvoice->id)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View order</a>
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('purchaseDoneModal')).show();
    });
    </script>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/purchases/page.blade.php ENDPATH**/ ?>