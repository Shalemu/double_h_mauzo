<?php $__env->startSection('title', 'Receive order ' . \App\Helpers\InvoiceNumber::for($invoice)); ?>

<?php $__env->startSection('content'); ?>
<?php
    $money = fn ($value) => number_format((float) $value, 2);
    $orderValue = $invoice->items->sum(fn ($item) => $item->ordered_quantity * $item->purchase_price);
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="<?php echo e(route('purchases.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="<?php echo e(route('purchases.history', $invoice->id)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text"></i> View order</a>
    </div>

    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <form method="POST" action="<?php echo e(route('purchases.confirm_receive', $invoice->id)); ?>" id="receiveForm">
        <?php echo csrf_field(); ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Receive order <?php echo e(\App\Helpers\InvoiceNumber::for($invoice)); ?></h5>
                <span class="text-muted small">
                    <?php echo e($invoice->supplier->name ?? '—'); ?> → <strong><?php echo e($invoice->shop->name ?? '—'); ?></strong>
                    · ordered <?php echo e(optional($invoice->ordered_at)->format('d M Y')); ?>

                </span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Enter what actually arrived and the actual prices. A line that didn't arrive at all can be set to 0.
                    Confirming adds the stock to <strong><?php echo e($invoice->shop->name ?? 'the shop'); ?></strong> and records the purchase.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0" id="receiveTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Product</th>
                                <th>Ordered</th>
                                <th style="min-width:110px">Received qty</th>
                                <th style="min-width:130px">Buying price (Tsh)</th>
                                <th style="min-width:130px">Retail price (Tsh)</th>
                                <th style="min-width:130px">Wholesale price (Tsh)</th>
                                <th>Line total (Tsh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $product = $item->product;
                                    $field = fn ($name, $default) => old("lines.{$item->id}.{$name}", $default);
                                ?>
                                <tr data-ordered="<?php echo e($item->ordered_quantity); ?>" data-order-price="<?php echo e($item->purchase_price); ?>" data-name="<?php echo e($product->name ?? 'Deleted product'); ?>">
                                    <td class="text-start">
                                        <div class="fw-semibold"><?php echo e($product->name ?? 'Deleted product'); ?></div>
                                        <div class="text-muted small">In stock now: <?php echo e(number_format($product->quantity ?? 0)); ?> <?php echo e($product->unit->short_name ?? ''); ?></div>
                                    </td>
                                    <td><?php echo e(number_format($item->ordered_quantity)); ?></td>
                                    <td>
                                        <input type="number" name="lines[<?php echo e($item->id); ?>][quantity]" class="form-control form-control-sm text-end js-qty"
                                               min="0" step="1" value="<?php echo e($field('quantity', $item->ordered_quantity)); ?>" aria-label="Received quantity">
                                        <div class="small js-qty-note"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[<?php echo e($item->id); ?>][purchase_price]" class="form-control form-control-sm text-end js-price"
                                               min="0" step="any" value="<?php echo e($field('purchase_price', $item->purchase_price)); ?>" aria-label="Buying price">
                                        <div class="small js-price-note"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[<?php echo e($item->id); ?>][selling_price]" class="form-control form-control-sm text-end"
                                               min="0" step="any" value="<?php echo e($field('selling_price', $item->selling_price ?? $product->selling_price ?? '')); ?>" aria-label="Retail price">
                                        <div class="text-muted small">now <?php echo e($money($product->selling_price ?? 0)); ?></div>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[<?php echo e($item->id); ?>][wholesale_price]" class="form-control form-control-sm text-end"
                                               min="0" step="any" value="<?php echo e($field('wholesale_price', $item->wholesale_price ?? $product->wholesale_price ?? '')); ?>" aria-label="Wholesale price">
                                        <div class="text-muted small">now <?php echo e($money($product->wholesale_price ?? 0)); ?></div>
                                    </td>
                                    <td class="text-end js-line-total"><?php echo e($money($item->ordered_quantity * $item->purchase_price)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th class="text-start" colspan="6">Order value (as ordered)</th>
                                <th class="text-end"><?php echo e($money($orderValue)); ?></th>
                            </tr>
                            <tr class="table-success">
                                <th class="text-start" colspan="6">Received value</th>
                                <th class="text-end" id="receivedTotal"><?php echo e($money($orderValue)); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Payment to supplier</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="paymentType">Payment type</label>
                        <select name="payment_type" id="paymentType" class="form-select" required>
                            <option value="cash" <?php echo e(old('payment_type', $invoice->payment_type) === 'cash' ? 'selected' : ''); ?>>Cash (paid in full)</option>
                            <option value="credit" <?php echo e(old('payment_type', $invoice->payment_type) === 'credit' ? 'selected' : ''); ?>>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="depositBox">
                        <label class="form-label" for="amountPaid">Deposit paid now (Tsh)</label>
                        <input type="number" name="amount_paid" id="amountPaid" class="form-control" min="0" step="any"
                               value="<?php echo e(old('amount_paid', (float) $invoice->amount_paid ?: '')); ?>">
                        <small class="text-danger" id="owedNote"></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo e(route('purchases.index')); ?>" class="btn btn-light">Not yet</a>
            
            <button type="button" class="btn btn-success" id="confirmReceive"><i class="bi bi-check2-circle"></i> Confirm receipt &amp; add to stock</button>
        </div>
    </form>
</div>

<?php echo $__env->make('pages.purchases._receive_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\receive.blade.php ENDPATH**/ ?>