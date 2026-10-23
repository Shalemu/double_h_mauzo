<?php $__env->startSection('title', 'Purchase ' . ($invoice->invoice_number ?: '#' . $invoice->id)); ?>

<?php $__env->startSection('content'); ?>
<?php
    $money = fn ($value) => number_format((float) $value, 2);
    $owed = (float) ($invoice->remaining_amount ?? 0);
    $isCredit = ($invoice->payment_type ?? null) === 'credit';
    $isPending = $invoice->isPending();
    $isCancelled = $invoice->status === \App\Models\PurchaseInvoice::CANCELLED;
    $subtotal = $invoice->items->sum(fn ($item) => $item->quantity * $item->purchase_price);
?>

<style>
@media  print {
    .app-sidebar, .app-header, .app-content-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:0 !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
        <a href="<?php echo e(url()->previous() !== url()->current() ? url()->previous() : route('purchases.index')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <div class="d-flex gap-2">
            <?php if($isPending && auth()->user()->hasPermission('purchases.receive')): ?>
                <a href="<?php echo e(route('purchases.receive', $invoice->id)); ?>" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-in-down"></i> Receive order</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('purchase-returns.create') && $invoice->items->sum('quantity') > 0): ?>
                <a href="<?php echo e(route('returns.purchases.create', $invoice->id)); ?>" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i> Return to supplier</a>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('invoiceFrame').contentWindow.print()"><i class="bi bi-printer"></i> Print</button>
        </div>
    </div>

    <?php if(session('error')): ?><div class="alert alert-danger no-print"><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if(session('success')): ?><div class="alert alert-success no-print"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($isPending): ?>
        <div class="alert alert-warning no-print"><i class="bi bi-hourglass-split me-1"></i> <strong>Pending order.</strong> Nothing is in stock or counted yet. Receive it when the goods arrive to confirm quantities and prices.</div>
    <?php elseif($isCancelled): ?>
        <div class="alert alert-secondary no-print"><i class="bi bi-x-circle me-1"></i> This order was cancelled. Nothing was stocked or paid.</div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small text-muted"><i class="bi bi-send me-1"></i> Send this invoice to the supplier</span>
            <?php echo $__env->make('pages.purchases._invoice_share', ['invoice' => $invoice], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 no-print">
                    <h5 class="mb-0"><?php echo e($isPending || $isCancelled ? 'Order' : 'Invoice'); ?> <?php echo e(\App\Helpers\InvoiceNumber::for($invoice)); ?></h5>
                    <?php if($isPending): ?>
                        <span class="badge bg-warning text-dark">Pending delivery</span>
                    <?php elseif($isCancelled): ?>
                        <span class="badge bg-secondary">Cancelled</span>
                    <?php elseif($isCredit && $owed > 0): ?>
                        <span class="badge bg-danger">Still owe Tsh <?php echo e($money($owed)); ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">Paid</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0 bg-light">
                    <iframe id="invoiceFrame" src="<?php echo e(route('purchases.invoice_preview', $invoice->id)); ?>" title="Invoice preview"
                            style="width:100%; height:900px; border:0; display:block; background:#fff;"
                            onload="try { this.style.height = (this.contentDocument.body.scrollHeight + 40) + 'px'; } catch (e) {}"></iframe>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Status</dt><dd class="col-7"><?php echo e(ucfirst($invoice->status)); ?></dd>
                        <?php if($invoice->ordered_at): ?>
                            <dt class="col-5 text-muted fw-normal">Ordered</dt><dd class="col-7"><?php echo e($invoice->ordered_at->format('d M Y, H:i')); ?></dd>
                        <?php endif; ?>
                        <?php if($invoice->status === \App\Models\PurchaseInvoice::RECEIVED): ?>
                            <dt class="col-5 text-muted fw-normal">Received</dt>
                            <dd class="col-7">
                                <?php echo e(\Carbon\Carbon::parse($invoice->received_at ?? $invoice->purchased_at)->format('d M Y, H:i')); ?>

                                <?php if($invoice->receivedBy): ?><div class="text-muted">by <?php echo e($invoice->receivedBy->name ?? ''); ?></div><?php endif; ?>
                            </dd>
                        <?php endif; ?>
                        <dt class="col-5 text-muted fw-normal">Shop / Store</dt><dd class="col-7"><?php echo e($invoice->shop->name ?? 'N/A'); ?></dd>
                        <dt class="col-5 text-muted fw-normal">Supplier</dt>
                        <dd class="col-7">
                            <?php echo e($invoice->supplier->name ?? '—'); ?>

                            <?php if($invoice->supplier->phone ?? null): ?><div class="text-muted"><?php echo e($invoice->supplier->phone); ?></div><?php endif; ?>
                        </dd>
                        <dt class="col-5 text-muted fw-normal">Payment</dt><dd class="col-7 mb-0"><?php echo e($isCredit ? 'Credit' : 'Cash'); ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Payments to supplier</div>
                <div class="card-body">
                    
                    <?php if($isPending || $isCancelled): ?>
                        <p class="text-muted small mb-0">Payment is recorded when the order is received.</p>
                    <?php elseif(!$isCredit && (float) $invoice->amount_paid > 0): ?>
                        <div class="d-flex justify-content-between small <?php echo e($invoice->payments->isNotEmpty() ? 'border-bottom pb-2 mb-2' : ''); ?>">
                            <span class="text-muted"><?php echo e(\Carbon\Carbon::parse($invoice->purchased_at)->format('d M Y')); ?> · Cash at purchase</span>
                            <span class="text-success text-nowrap"><?php echo e($money($invoice->amount_paid)); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php $__empty_1 = true; $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex justify-content-between small <?php echo e($loop->last ? '' : 'border-bottom pb-2 mb-2'); ?>">
                            <span class="text-muted"><?php echo e(\Carbon\Carbon::parse($payment->paid_at)->format('d M Y')); ?></span>
                            <span class="text-success text-nowrap"><?php echo e($money($payment->amount)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if(!$isPending && !$isCancelled && ($isCredit || (float) $invoice->amount_paid <= 0)): ?>
                            <p class="text-muted small mb-0">No payments recorded yet.</p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($isCredit && $owed > 0 && !$isPending && !$isCancelled): ?>
                        <form method="POST" action="<?php echo e(route('purchases.deposit')); ?>" class="d-flex gap-2 mt-3 pt-3 border-top no-print">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="purchase_invoice_id" value="<?php echo e($invoice->id); ?>">
                            <input type="number" name="amount" class="form-control form-control-sm" min="1" max="<?php echo e($owed); ?>" step="any" placeholder="Amount (Tsh)" aria-label="Payment amount" required>
                            <button class="btn btn-sm btn-success text-nowrap"><i class="bi bi-cash"></i> Pay</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Returns to supplier</div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex justify-content-between small <?php echo e($loop->last ? '' : 'border-bottom pb-2 mb-2'); ?>">
                            <div>
                                <div class="fw-semibold"><?php echo e($return->product->name ?? 'Product'); ?> × <?php echo e($return->quantity); ?></div>
                                <div class="text-muted"><?php echo e(($return->returned_at ?? $return->created_at)->format('d M Y')); ?><?php echo e($return->reason ? ' · ' . $return->reason : ''); ?></div>
                            </div>
                            <div class="text-success text-nowrap">− <?php echo e($money($return->amount)); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted small mb-0">No returns on this invoice.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/purchases/show.blade.php ENDPATH**/ ?>