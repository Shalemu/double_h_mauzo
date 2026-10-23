<?php $__env->startSection('title', 'Return items — Sale #' . $sale->id); ?>

<?php $__env->startSection('content'); ?>
<?php $money = fn ($v) => number_format((float) $v, 2); ?>
<div class="container-fluid py-4">
    <a href="<?php echo e(route('returns.sales')); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to sales returns</a>

    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <form method="POST" action="<?php echo e(route('returns.sales.store', $sale->id)); ?>" id="returnForm">
        <?php echo csrf_field(); ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Return items from Sale #<?php echo e($sale->id); ?></h5>
                        <small class="text-muted">Enter how many of each item the customer is bringing back. Leave 0 for items not returned.</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Product</th>
                                        <th>Sold</th>
                                        <th>Already returned</th>
                                        <th>Can return</th>
                                        <th style="width:130px">Return qty</th>
                                        <th style="width:150px">Refund (Tsh)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $already = (int) ($returned[$item->id] ?? 0);
                                            $left = (int) $item->quantity;
                                            $unit = $item->price - ((float) $item->discount / max($left, 1));
                                        ?>
                                        <tr class="return-line" data-unit="<?php echo e(round($unit, 2)); ?>">
                                            <td class="text-start">
                                                <?php echo e($item->product->name ?? 'Deleted product'); ?>

                                                <div class="small text-muted"><?php echo e(ucfirst($item->sale_type ?? 'retail')); ?> · Tsh <?php echo e($money($item->price)); ?> each</div>
                                            </td>
                                            <td><?php echo e($left + $already); ?></td>
                                            <td><?php echo e($already ?: '—'); ?></td>
                                            <td class="fw-semibold"><?php echo e($left); ?></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary qty-step" data-step="-1" aria-label="Less" <?php echo e($left ? '' : 'disabled'); ?>>−</button>
                                                    <input type="number" name="lines[<?php echo e($item->id); ?>][quantity]" class="form-control text-center return-qty"
                                                           value="<?php echo e(old('lines.' . $item->id . '.quantity', 0)); ?>" min="0" max="<?php echo e($left); ?>" <?php echo e($left ? '' : 'disabled'); ?>

                                                           aria-label="Quantity of <?php echo e($item->product->name ?? 'item'); ?> to return">
                                                    <button type="button" class="btn btn-outline-secondary qty-step" data-step="1" aria-label="More" <?php echo e($left ? '' : 'disabled'); ?>>+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="lines[<?php echo e($item->id); ?>][amount]" class="form-control form-control-sm text-end return-amount"
                                                       value="<?php echo e(old('lines.' . $item->id . '.amount')); ?>" placeholder="0.00" <?php echo e($left ? '' : 'disabled'); ?>

                                                       aria-label="Refund for <?php echo e($item->product->name ?? 'item'); ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">Sold on</dt><dd class="col-7"><?php echo e($sale->created_at->format('d M Y, H:i')); ?></dd>
                            <dt class="col-5 text-muted fw-normal">Shop</dt><dd class="col-7"><?php echo e($sale->shop->name ?? '—'); ?></dd>
                            <dt class="col-5 text-muted fw-normal">Customer</dt><dd class="col-7"><?php echo e($sale->customer->name ?? 'Walk-in'); ?></dd>
                            <dt class="col-5 text-muted fw-normal">Sold by</dt><dd class="col-7"><?php echo e($sale->staff->full_name ?? ($sale->admin->name ?? '—')); ?></dd>
                            <dt class="col-5 text-muted fw-normal">Sale total</dt><dd class="col-7 mb-0">Tsh <?php echo e($money($sale->total)); ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold">Why is it being returned?</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3" role="radiogroup" aria-label="Return reason">
                            <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <input type="radio" class="btn-check" name="reason" id="reason-<?php echo e($loop->index); ?>" value="<?php echo e($reason); ?>"
                                       <?php echo e(old('reason', 'Damaged') === $reason ? 'checked' : ''); ?> required>
                                <label class="btn btn-sm btn-outline-secondary" for="reason-<?php echo e($loop->index); ?>"><?php echo e($reason); ?></label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <label class="form-label small" for="returnNote">Note (optional)</label>
                        <input type="text" name="note" id="returnNote" class="form-control mb-3" maxlength="255" value="<?php echo e(old('note')); ?>" placeholder="e.g. seal was broken">

                        <div class="d-flex justify-content-between border-top pt-3 mb-1">
                            <span class="text-muted">Items returning</span><strong id="sumQty">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Refund to customer</span><strong class="text-danger">Tsh <span id="sumAmount">0.00</span></strong>
                        </div>
                        <button class="btn btn-danger w-100" id="submitReturn" disabled>
                            <i class="bi bi-arrow-return-left"></i> Record return
                        </button>
                        <p class="small text-muted mt-2 mb-0">The items go back into stock and the sale total is reduced. Any unpaid balance on this sale is reduced first.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('pages.returns._sale_return_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\returns\sale_return.blade.php ENDPATH**/ ?>