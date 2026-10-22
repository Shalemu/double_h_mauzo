<?php $__env->startSection('title', 'Stock Transfer #' . $transfer->id); ?>

<?php $__env->startSection('content'); ?>
<style>
.transfer-meta span { display:block; font-size:.75rem; color:#6b7280; }
.transfer-meta strong { font-size:.95rem; }
@media  print {
    .app-header, .app-sidebar, .app-footer, .app-content-header, .no-print { display:none !important; }
    .app-main, .app-wrapper { margin:0 !important; padding:0 !important; }
    .card { box-shadow:none !important; border:1px solid #ccc !important; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-end flex-wrap gap-2 mb-3 no-print">
        <a href="<?php echo e(route('stock-transfers.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to history</a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Transfer #<?php echo e($transfer->id); ?></h5>
            <span class="badge bg-success">Completed</span>
        </div>
        <div class="card-body">
            <div class="row g-3 transfer-meta mb-4">
                <div class="col-sm-6 col-lg-3"><span>Date</span><strong><?php echo e($transfer->created_at->format('d M Y H:i')); ?></strong></div>
                <div class="col-sm-6 col-lg-3"><span>From</span><strong><?php echo e($transfer->fromShop->label ?? '—'); ?></strong></div>
                <div class="col-sm-6 col-lg-3"><span>To</span><strong><?php echo e($transfer->toShop->label ?? '—'); ?></strong></div>
                <div class="col-sm-6 col-lg-3"><span>Transferred by</span><strong><?php echo e($transfer->transferredBy->name ?? '—'); ?></strong></div>
                <?php if($transfer->note): ?>
                    <div class="col-12"><span>Note</span><strong><?php echo e($transfer->note); ?></strong></div>
                <?php endif; ?>
            </div>

            <?php $totalUnits = 0; $totalValue = 0; ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th><th class="text-start">Product</th><th>Barcode</th><th>Unit</th><th>Quantity</th>
                            <th>Purchasing (TZS)</th><th>Retail (TZS)</th><th>Wholesale (TZS)</th><th>Value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $product = $products->get($item->from_product_id);
                                $value = $product ? $item->quantity * $product->purchase_price : null;
                                $totalUnits += $item->quantity;
                                $totalValue += $value ?? 0;
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($item->product_name); ?></td>
                                <td class="text-center"><?php echo e($product->barcode ?? '—'); ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?php echo e($product->unit->short_name ?? '—'); ?></span></td>
                                <td class="text-center fw-semibold"><?php echo e($item->quantity); ?><?php echo e($product && $product->unit ? ' ' . $product->unit->short_name : ''); ?></td>
                                <td class="text-end"><?php echo e($product ? number_format($product->purchase_price, 2) : '—'); ?></td>
                                <td class="text-end"><?php echo e($product ? number_format($product->selling_price, 2) : '—'); ?></td>
                                <td class="text-end"><?php echo e($product ? number_format($product->wholesale_price, 2) : '—'); ?></td>
                                <td class="text-end"><?php echo e($value !== null ? number_format($value, 2) : '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td class="text-center"><?php echo e($totalUnits); ?></td>
                            <td colspan="3"></td>
                            <td class="text-end"><?php echo e(number_format($totalValue, 2)); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="small text-muted mt-2 mb-0">Prices are the sending product's current prices. "—" means that product has since been deleted.</p>
        </div>
    </div>
</div>

<?php if(request()->boolean('print')): ?>
    <script>window.addEventListener('load', () => window.print());</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\stock_transfers\show.blade.php ENDPATH**/ ?>