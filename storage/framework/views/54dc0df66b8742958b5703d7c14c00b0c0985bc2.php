<?php $__env->startSection('title', 'Purchase Returns'); ?>

<?php $__env->startSection('content'); ?>
<?php $canReturn = auth()->user()->hasPermission('purchase-returns.create'); ?>
<div class="container-fluid py-4">

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Returns</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->count)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units sent back</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->quantity)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Credited by suppliers (Tsh)</div><div class="fs-4 fw-bold text-success"><?php echo e(number_format($totals->amount, 2)); ?></div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-box-arrow-left me-2"></i>Purchase Returns</h5>
                    <small class="text-muted">When goods go back to a supplier: find the purchase, click <strong>Return</strong>, choose how many and why. Stock is taken out and the invoice is reduced automatically.</small>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'purchases' ? 'active' : ''); ?>" href="<?php echo e(request()->fullUrlWithQuery(['tab' => 'purchases', 'history_page' => null])); ?>">
                        <i class="bi bi-cart-check me-1"></i> Purchases
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'history' ? 'active' : ''); ?>" href="<?php echo e(request()->fullUrlWithQuery(['tab' => 'history', 'purchases_page' => null])); ?>">
                        <i class="bi bi-clock-history me-1"></i> Returns history <span class="badge bg-light text-dark border ms-1"><?php echo e(number_format($totals->count)); ?></span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop / Store</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>" <?php echo e((string) $shopId === (string) $shop->id ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="from" value="<?php echo e($from->toDateString()); ?>" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="to" value="<?php echo e($to->toDateString()); ?>" class="form-control form-control-sm">
                </div>
                <?php if($tab === 'purchases'): ?>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small mb-1">Invoice # or supplier</label>
                        <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="e.g. INV-0012 or Unilever">
                    </div>
                <?php endif; ?>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <?php if($tab === 'purchases'): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Shop / Store</th>
                                <th>Supplier</th>
                                <th>Products</th>
                                <th>Units left</th>
                                <th>Total (Tsh)</th>
                                <th>Payment</th>
                                <th style="width:170px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($invoice->invoice_number ?: '#' . $invoice->id); ?></td>
                                    <td class="text-nowrap"><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y H:i')); ?></td>
                                    <td><?php echo e($invoice->shop->name ?? '—'); ?></td>
                                    <td><?php echo e($invoice->supplier->name ?? '—'); ?></td>
                                    <td><?php echo e((int) $invoice->lines_count); ?></td>
                                    <td><?php echo e((int) $invoice->items_left); ?></td>
                                    <td><?php echo e(number_format($invoice->total_amount, 2)); ?></td>
                                    <td>
                                        <?php if(($invoice->payment_type ?? null) === 'credit' && $invoice->remaining_amount > 0): ?>
                                            <span class="badge bg-warning text-dark">Credit · owes <?php echo e(number_format($invoice->remaining_amount)); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="<?php echo e(route('purchases.history', $invoice->id)); ?>" class="btn btn-sm btn-outline-secondary" aria-label="View invoice <?php echo e($invoice->invoice_number ?: '#' . $invoice->id); ?>"><i class="bi bi-eye"></i></a>
                                        <?php if($canReturn): ?>
                                            <a href="<?php echo e(route('returns.purchases.create', $invoice->id)); ?>" class="btn btn-sm btn-danger">
                                                <i class="bi bi-box-arrow-left"></i> Return
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9" class="text-muted py-4">No purchases found for this period<?php echo e($search ? ' matching "' . $search . '"' : ''); ?>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3"><?php echo e($invoices->links('pagination::bootstrap-4')); ?></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Shop / Store</th>
                                <th>Product</th>
                                <th>Supplier</th>
                                <th>Invoice</th>
                                <th>Qty</th>
                                <th>Amount (Tsh)</th>
                                <th>Reason</th>
                                <th>Processed by</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $invoice = $return->purchaseItem->invoice ?? null; ?>
                                <tr>
                                    <td class="text-nowrap"><?php echo e(($return->returned_at ?? $return->created_at)->format('d M Y H:i')); ?></td>
                                    <td><?php echo e($return->shop->name ?? 'N/A'); ?></td>
                                    <td class="text-start"><?php echo e($return->product->name ?? 'Deleted product'); ?></td>
                                    <td><?php echo e($invoice->supplier->name ?? '—'); ?></td>
                                    <td>
                                        <?php if($invoice): ?>
                                            <a href="<?php echo e(route('purchases.history', $invoice->id)); ?>"><?php echo e($invoice->invoice_number ?: '#' . $invoice->id); ?></a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($return->quantity); ?></td>
                                    <td><?php echo e(number_format($return->amount, 2)); ?></td>
                                    <td><?php echo e($return->reason ?: '—'); ?></td>
                                    <td><?php echo e($return->processedBy->name ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9" class="text-muted py-4">No purchase returns in this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3"><?php echo e($returns->links('pagination::bootstrap-4')); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/returns/purchases.blade.php ENDPATH**/ ?>