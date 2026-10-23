<?php $__env->startSection('title', 'Sale #' . $sale->id); ?>

<?php $__env->startSection('content'); ?>
<?php
    $soldBy = $sale->staff->full_name ?? ($sale->admin->name ?? 'N/A');
    $balance = (float) ($sale->remaining_amount ?? 0);
    $money = fn ($value) => number_format((float) $value, 2);
?>

<style>
@media  print {
    .app-sidebar, .app-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:0 !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <a href="<?php echo e(url()->previous() !== url()->current() ? url()->previous() : route('admin.sales.index')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to sales
        </a>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.sales.detail', [$sale->shop_id, $sale->created_at->toDateString()])); ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-calendar-day"></i> All sales on <?php echo e($sale->created_at->format('d M Y')); ?>

            </a>
            <?php if(auth()->user()->hasPermission('sales-returns.create') && $sale->items->sum('quantity') > 0): ?>
                <a href="<?php echo e(route('returns.sales.create', $sale->id)); ?>" class="btn btn-sm btn-danger"><i class="bi bi-arrow-return-left"></i> Return items</a>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Sale #<?php echo e($sale->id); ?></h5>
                    <?php if($balance > 0): ?>
                        <span class="badge bg-danger">Balance due Tsh <?php echo e($money($balance)); ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">Paid</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Product</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Total (Tsh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php $returned = (int) ($returnedQty[$item->id] ?? 0); ?>
                                    <tr>
                                        <td><?php echo e($i + 1); ?></td>
                                        <td class="text-start">
                                            <?php echo e($item->product->name ?? 'Deleted product'); ?>

                                            <?php if($returned): ?>
                                                <div class="small text-danger"><?php echo e($returned); ?> returned</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(ucfirst($item->sale_type ?? 'retail')); ?></td>
                                        <td><?php echo e($item->quantity); ?></td>
                                        <td><?php echo e($money($item->price)); ?></td>
                                        <td><?php echo e((float) $item->discount > 0 ? $money($item->discount) : "—"); ?></td>
                                        <td class="fw-semibold"><?php echo e($money($item->quantity * $item->price - ($item->discount ?? 0))); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="7" class="text-muted py-4">No items on this sale (all returned).</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mt-3">
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-muted">Items subtotal</td><td class="text-end"><?php echo e($money($subtotal)); ?></td></tr>
                                <?php if($sale->bill_discount > 0): ?>
                                    <tr><td class="text-muted">Bill discount</td><td class="text-end">− <?php echo e($money($sale->bill_discount)); ?></td></tr>
                                <?php endif; ?>
                                <?php if($sale->shipping > 0): ?>
                                    <tr><td class="text-muted">Shipping</td><td class="text-end"><?php echo e($money($sale->shipping)); ?></td></tr>
                                <?php endif; ?>
                                <?php if(($sale->fine_amount ?? 0) > 0): ?>
                                    <tr><td class="text-muted">Late-payment fine</td><td class="text-end"><?php echo e($money($sale->fine_amount)); ?></td></tr>
                                <?php endif; ?>
                                <tr class="fw-bold"><td>Total</td><td class="text-end">Tsh <?php echo e($money($sale->total)); ?></td></tr>
                                <tr><td class="text-muted">Received</td><td class="text-end"><?php echo e($money($sale->received_amount)); ?></td></tr>
                                <?php if(($sale->change_amount ?? 0) > 0): ?>
                                    <tr><td class="text-muted">Change</td><td class="text-end"><?php echo e($money($sale->change_amount)); ?></td></tr>
                                <?php endif; ?>
                                <tr class="<?php echo e($balance > 0 ? 'text-danger fw-semibold' : ''); ?>"><td>Balance</td><td class="text-end"><?php echo e($money($balance)); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Date</dt><dd class="col-7"><?php echo e($sale->created_at->format('d M Y, H:i')); ?></dd>
                        <dt class="col-5 text-muted fw-normal">Shop</dt><dd class="col-7"><?php echo e($sale->shop->name ?? 'N/A'); ?></dd>
                        <dt class="col-5 text-muted fw-normal">Sold by</dt><dd class="col-7"><?php echo e($soldBy); ?></dd>
                        <dt class="col-5 text-muted fw-normal">Customer</dt>
                        <dd class="col-7">
                            <?php if($sale->customer): ?>
                                <a href="<?php echo e(route('customers.show', $sale->customer->id)); ?>"><?php echo e($sale->customer->name); ?></a>
                                <?php if($sale->customer->phone): ?><div class="text-muted"><?php echo e($sale->customer->phone); ?></div><?php endif; ?>
                            <?php else: ?>
                                Walk-in
                            <?php endif; ?>
                        </dd>
                        <dt class="col-5 text-muted fw-normal">Payment</dt>
                        <dd class="col-7 mb-0">
                            <?php echo e(collect([$sale->payment_type, $sale->payment_method])->filter()->map(fn ($v) => ucfirst(str_replace('_', ' ', $v)))->unique()->implode(' · ') ?: '—'); ?>

                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Returns</div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex justify-content-between small <?php echo e($loop->last ? '' : 'border-bottom pb-2 mb-2'); ?>">
                            <div>
                                <div class="fw-semibold"><?php echo e($return->product->name ?? 'Product'); ?> × <?php echo e($return->quantity); ?></div>
                                <div class="text-muted"><?php echo e(($return->returned_at ?? $return->created_at)->format('d M Y')); ?><?php echo e($return->reason ? ' · ' . $return->reason : ''); ?></div>
                            </div>
                            <div class="text-danger text-nowrap">− <?php echo e($money($return->amount)); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted small mb-0">No returns on this sale.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\sales_revenue\show.blade.php ENDPATH**/ ?>