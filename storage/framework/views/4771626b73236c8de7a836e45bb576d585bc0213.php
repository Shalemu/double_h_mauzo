<?php $__env->startSection('title', $store->name); ?>

<?php $__env->startSection('content'); ?>
<style>
.store-stat { border:1px solid #e5e7eb; border-radius:.75rem; padding:.85rem 1rem; background:#fff; height:100%; }
.store-stat span { display:block; font-size:.78rem; color:#6b7280; }
.store-stat strong { font-size:1.35rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="text-muted mb-0">
            <i class="bi bi-geo-alt"></i> <?php echo e($store->location); ?>

            <?php if($store->isSuspended()): ?> · <span class="badge bg-secondary">Suspended</span><?php endif; ?>
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('stores.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> All Stores</a>
            <a href="<?php echo e(route('stock-transfers.create', ['shop' => $store->id])); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left-right"></i> Transfer stock</a>
            <a href="<?php echo e(route('distributions.index', ['store' => $store->id])); ?>" class="btn btn-sm btn-primary"><i class="bi bi-truck-flatbed"></i> Distribution</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg"><div class="store-stat"><span>Products</span><strong><?php echo e(number_format($summary['products'])); ?></strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Units in store</span><strong><?php echo e(number_format($summary['units'])); ?></strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Stock value (TZS)</span><strong><?php echo e(number_format($summary['value'], 2)); ?></strong></div></div>
        <div class="col-6 col-lg"><div class="store-stat"><span>Units with distributors</span><strong class="<?php echo e($summary['out_units'] ? 'text-warning' : ''); ?>"><?php echo e(number_format($summary['out_units'])); ?></strong></div></div>
        <div class="col-12 col-lg"><div class="store-stat"><span>Owed by distributors (TZS)</span><strong class="<?php echo e($summary['owed'] > 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format($summary['owed'], 2)); ?></strong></div></div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="bi bi-box-seam me-1"></i> Products in this store</h6>
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search name or barcode..." aria-label="Search products" style="min-width:220px">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <?php if($search !== ''): ?>
                    <a href="<?php echo e(route('stores.show', $store)); ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Product</th>
                            <th>Purchasing (TZS)</th>
                            <th>Retail (TZS)</th>
                            <th>Wholesale (TZS)</th>
                            <th title="On the shelf now">Available in store</th>
                            <th title="Taken by distributors on trips not yet settled">With distributors</th>
                            <th title="In store + with distributors">Total stock</th>
                            <th title="Given out on settled trips (recorded as sales)">Sold</th>
                            <th title="Brought back on settled trips — already added back to the store">Returned</th>
                            <th>Stock value (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $m = $movement->get($product->id);
                                $out = (int) ($m->out_qty ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo e($product->name); ?></div>
                                    <?php if($product->barcode): ?><small class="text-muted"><?php echo e($product->barcode); ?></small><?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo e(number_format($product->purchase_price, 2)); ?></td>
                                <td class="text-end"><?php echo e(number_format($product->selling_price, 2)); ?></td>
                                <td class="text-end"><?php echo e(number_format($product->wholesale_price, 2)); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo e($product->quantity > 0 ? 'bg-success' : 'bg-danger'); ?> fs-6"><?php echo e($product->quantity); ?></span>
                                </td>
                                <td class="text-center <?php echo e($out ? 'text-warning fw-semibold' : 'text-muted'); ?>"><?php echo e($out); ?></td>
                                <td class="text-center fw-semibold"><?php echo e($product->quantity + $out); ?></td>
                                <td class="text-center"><?php echo e((int) ($m->sold_qty ?? 0)); ?></td>
                                <td class="text-center"><?php echo e((int) ($m->returned_qty ?? 0)); ?></td>
                                <td class="text-end"><?php echo e(number_format($product->quantity * $product->purchase_price, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <?php echo e($search !== '' ? 'No products match your search.' : 'This store has no products yet. Buy stock into it from Purchases, or transfer stock in.'); ?>

                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($products->links('pagination::bootstrap-4')); ?></div>
            <p class="small text-muted mt-2 mb-0">
                When a distributor takes stock it leaves <em>Available in store</em> and shows under <em>With distributors</em>.
                When the trip is settled, returned units go back into <em>Available in store</em> and the rest count as <em>Sold</em>.
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-truck-flatbed me-1"></i> Recent distribution trips</h6>
            <a href="<?php echo e(route('distributions.index', ['store' => $store->id])); ?>" class="small">See all</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>Distributor</th><th>Taken</th><th>Returned</th><th>Sold</th><th>Status</th><th>Owed (TZS)</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $distributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($d->id); ?></td>
                                <td class="text-nowrap"><?php echo e($d->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($d->staff->full_name ?? '—'); ?></td>
                                <td class="text-center"><?php echo e($d->items->sum('quantity_taken')); ?></td>
                                <td class="text-center"><?php echo e($d->isOut() ? '—' : $d->items->sum('quantity_returned')); ?></td>
                                <td class="text-center"><?php echo e($d->isOut() ? '—' : $d->items->sum(fn ($i) => $i->quantity_sold)); ?></td>
                                <td class="text-center">
                                    <?php if($d->isOut()): ?>
                                        <span class="badge bg-warning text-dark">Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Settled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end <?php echo e($d->balance > 0 ? 'text-danger fw-semibold' : ''); ?>"><?php echo e($d->isOut() ? '—' : number_format($d->balance, 2)); ?></td>
                                <td class="text-center">
                                    <a href="<?php echo e(route('distributions.show', $d)); ?>" class="btn btn-sm <?php echo e($d->isOut() ? 'btn-primary' : 'btn-outline-secondary'); ?>"><?php echo e($d->isOut() ? 'Settle' : 'View'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">No distribution trips from this store yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\stores\show.blade.php ENDPATH**/ ?>