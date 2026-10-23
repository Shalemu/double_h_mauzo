<?php $__env->startSection('title', 'Distribution #' . $distribution->id); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo e(route('distributions.index')); ?>" class="text-decoration-none"><i class="bi bi-arrow-left"></i> All distributions</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-truck-flatbed me-2"></i>Distribution #<?php echo e($distribution->id); ?></h5>
            <?php if($distribution->isOut()): ?>
                <span class="badge bg-warning text-dark">Out with distributor</span>
            <?php else: ?>
                <span class="badge bg-success">Settled <?php echo e($distribution->settled_at?->format('d M Y H:i')); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Store</div><div class="fw-semibold"><?php echo e($distribution->store->name ?? '—'); ?></div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Distributor</div><div class="fw-semibold"><?php echo e($distribution->staff->full_name ?? '—'); ?></div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Issued</div><div class="fw-semibold"><?php echo e($distribution->created_at->format('d M Y H:i')); ?> by <?php echo e($distribution->issuedBy->name ?? '—'); ?></div></div>
                <div class="col-sm-6 col-lg-3"><div class="text-muted">Note</div><div class="fw-semibold"><?php echo e($distribution->note ?: '—'); ?></div></div>
            </div>
        </div>
    </div>

    <?php if($distribution->isOut()): ?>
        
        <form method="POST" action="<?php echo e(route('distributions.settle', $distribution)); ?>" class="card shadow-sm" id="settleForm">
            <?php echo csrf_field(); ?>
            <div class="card-header bg-white">
                <h6 class="mb-0">Settle — distributor is back</h6>
                <small class="text-muted">Enter how many of each product came back. Returned units go back into the store; the rest is recorded as a sale.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th style="width:130px">Returned</th><th>Sold</th><th style="width:160px">Price (TZS)</th><th>Amount (TZS)</th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $distribution->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-taken="<?php echo e($item->quantity_taken); ?>">
                                    <td><?php echo e($item->product_name); ?></td>
                                    <td class="text-center"><?php echo e($item->quantity_taken); ?></td>
                                    <td>
                                        <input type="number" name="items[<?php echo e($item->id); ?>][returned]" class="form-control form-control-sm text-center js-returned"
                                               min="0" max="<?php echo e($item->quantity_taken); ?>" value="<?php echo e(old("items.{$item->id}.returned", 0)); ?>" required
                                               aria-label="Returned <?php echo e($item->product_name); ?>">
                                    </td>
                                    <td class="text-center js-sold"><?php echo e($item->quantity_taken); ?></td>
                                    <td>
                                        <input type="number" name="items[<?php echo e($item->id); ?>][price]" class="form-control form-control-sm text-end js-price"
                                               min="0" step="0.01" value="<?php echo e(old("items.{$item->id}.price", $item->price)); ?>" required
                                               aria-label="Price <?php echo e($item->product_name); ?>">
                                    </td>
                                    <td class="text-end js-amount">0.00</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light"><th colspan="5" class="text-end">Total sold</th><th class="text-end" id="settleTotal">0.00</th></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="amountReceived">Cash handed in (TZS)</label>
                        <input type="number" name="amount_received" id="amountReceived" class="form-control" min="0" step="0.01"
                               value="<?php echo e(old('amount_received', 0)); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Still owed by distributor</div>
                        <div class="fs-5 fw-semibold" id="settleBalance">0.00</div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="button" class="btn btn-primary" id="settleReview">
                            <i class="bi bi-check2-circle"></i> Settle distribution
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="modal fade" id="settleConfirmModal" tabindex="-1" aria-labelledby="settleConfirmTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title" id="settleConfirmTitle"><i class="bi bi-clipboard-check me-2"></i>Confirm settlement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                <strong><?php echo e($distribution->staff->full_name ?? 'The distributor'); ?></strong> is back.
                                Check the figures below — once settled, this can't be changed.
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-box-arrow-in-down text-success"></i> Back to <?php echo e($distribution->store->name ?? 'store'); ?></div>
                                        <div class="fs-4 fw-semibold" id="cfReturned">0</div>
                                        <div class="small text-muted">units</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-receipt text-primary"></i> Recorded as sold</div>
                                        <div class="fs-4 fw-semibold" id="cfSold">0</div>
                                        <div class="small text-muted">units · TZS <span id="cfTotal">0</span></div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted"><i class="bi bi-cash-stack text-success"></i> Cash handed in</div>
                                        <div class="fs-4 fw-semibold">TZS <span id="cfReceived">0</span></div>
                                        <div class="small" id="cfBalanceLine"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr><th class="text-start">Product</th><th>Taken</th><th>Returned</th><th>Sold</th><th>Amount (TZS)</th></tr>
                                    </thead>
                                    <tbody id="cfLines"></tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning small mt-3 mb-0 d-none" id="cfOverpaid">
                                <i class="bi bi-exclamation-triangle"></i> Cash handed in is more than the sold amount — only the sold amount will be recorded.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Go back and edit</button>
                            <button type="submit" class="btn btn-primary" id="settleSubmit"><i class="bi bi-check2-circle"></i> Confirm &amp; settle</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php echo $__env->make('pages.distributions._show_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">What happened to the stock</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr><th>Product</th><th>Taken</th><th>Returned to store</th><th>Sold</th><th>Price (TZS)</th><th>Amount (TZS)</th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $distribution->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->product_name); ?></td>
                                    <td class="text-center"><?php echo e($item->quantity_taken); ?></td>
                                    <td class="text-center"><?php echo e($item->quantity_returned); ?></td>
                                    <td class="text-center"><?php echo e($item->quantity_sold); ?></td>
                                    <td class="text-end"><?php echo e(number_format($item->price, 2)); ?></td>
                                    <td class="text-end"><?php echo e(number_format($item->quantity_sold * $item->price, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Total sold (TZS)</div><div class="fs-5 fw-semibold"><?php echo e(number_format($distribution->total_value, 2)); ?></div></div></div>
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Cash received (TZS)</div><div class="fs-5 fw-semibold"><?php echo e(number_format($distribution->amount_received, 2)); ?></div></div></div>
                    <div class="col-sm-4"><div class="border rounded-3 p-3"><div class="small text-muted">Still owed (TZS)</div><div class="fs-5 fw-semibold <?php echo e($distribution->balance > 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($distribution->balance, 2)); ?></div></div></div>
                </div>

                <?php if($distribution->sale_id): ?>
                    <div class="small text-muted mt-3">
                        Recorded as <a href="<?php echo e(route('admin.sales.show', $distribution->sale_id)); ?>">sale #<?php echo e($distribution->sale_id); ?></a> from <?php echo e($distribution->store->name ?? 'the store'); ?>, settled by <?php echo e($distribution->settledBy->name ?? '—'); ?>.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Payments from distributor</h6></div>
            <div class="card-body">
                <?php if($distribution->balance > 0): ?>
                    <form method="POST" action="<?php echo e(route('distributions.pay', $distribution)); ?>" class="row g-2 mb-3">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-3">
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01" max="<?php echo e($distribution->balance); ?>"
                                   placeholder="Amount (TZS)" aria-label="Amount" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="note" class="form-control" maxlength="255" placeholder="Note (optional)" aria-label="Note">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100"><i class="bi bi-cash"></i> Record payment</button>
                        </div>
                    </form>
                <?php endif; ?>

                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light"><tr><th>Date</th><th class="text-end">Amount (TZS)</th><th>Received by</th><th>Note</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $distribution->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($payment->created_at->format('d M Y H:i')); ?></td>
                                <td class="text-end"><?php echo e(number_format($payment->amount, 2)); ?></td>
                                <td><?php echo e($payment->receivedBy->name ?? '—'); ?></td>
                                <td><?php echo e($payment->note ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-muted text-center small">No later payments. Cash handed in at settlement is already included above.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\distributions\show.blade.php ENDPATH**/ ?>