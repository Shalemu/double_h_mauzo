<?php $__env->startSection('title', 'New Stock Transfer'); ?>

<?php $__env->startSection('content'); ?>
<style>
.transfer-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; max-height:320px; overflow:auto; display:none; }
.transfer-results.is-open { display:block; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <p class="text-muted mb-0">Move stock between stores and shops. The receiving side's matching product (same barcode or name) is topped up, or created if it doesn't stock it yet.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('stock-transfers.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="<?php echo e(route('stock-transfers.index')); ?>" class="btn btn-sm btn-primary"><i class="bi bi-list-ul"></i> Transfer History</a>
        </div>
    </div>

    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <form class="card shadow-sm" method="POST" action="<?php echo e(route('stock-transfers.store')); ?>" id="transferForm">
        <?php echo csrf_field(); ?>
        <div class="card-body">
                <?php
                    $shopOptions = $shops->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'type' => $s->isStore() ? 'store' : 'shop'])->values();
                    $preselected = $shops->firstWhere('id', (int) $shopId);
                ?>

                
                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-semibold mb-2"><i class="bi bi-box-arrow-up-right me-1 text-danger"></i> From</div>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <label class="form-label small" for="fromType">Type</label>
                                    <select id="fromType" class="form-select" aria-label="From type">
                                        <option value="store" <?php echo e($preselected && !$preselected->isStore() ? '' : 'selected'); ?>>Store</option>
                                        <option value="shop" <?php echo e($preselected && !$preselected->isStore() ? 'selected' : ''); ?>>Shop</option>
                                    </select>
                                </div>
                                <div class="col-sm-7">
                                    <label class="form-label small" for="fromShop">Store / Shop</label>
                                    <select name="from_shop_id" id="fromShop" class="form-select" required></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-semibold mb-2"><i class="bi bi-box-arrow-in-down-right me-1 text-success"></i> To</div>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <label class="form-label small" for="toType">Type</label>
                                    <select id="toType" class="form-select" aria-label="To type">
                                        <option value="shop" selected>Shop</option>
                                        <option value="store">Store</option>
                                    </select>
                                </div>
                                <div class="col-sm-7">
                                    <label class="form-label small" for="toShop">Store / Shop</label>
                                    <select name="to_shop_id" id="toShop" class="form-select" required></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="border rounded-3 p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-5 position-relative">
                            <label class="form-label small" for="transferSearch">Product</label>
                            <input type="search" id="transferSearch" class="form-control" autocomplete="off"
                                   placeholder="Choose where stock comes from first..." aria-label="Search product" disabled>
                            <div class="list-group shadow transfer-results" id="transferResults"></div>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label small" for="transferQty">Quantity to transfer <span class="badge bg-secondary" id="qtyUnit" hidden></span></label>
                            <input type="number" id="transferQty" class="form-control" min="1" placeholder="Qty" disabled>
                        </div>
                        <div class="col-6 col-lg-2">
                            <button type="button" class="btn btn-outline-primary w-100" id="transferAdd" disabled><i class="bi bi-plus-lg"></i> Add</button>
                        </div>
                    </div>

                    <div class="row g-2 mt-1" id="transferInfo" hidden>
                        <div class="col-6 col-lg-3">
                            <div class="transfer-stat"><span>Available</span><strong id="infoAvailable">—</strong></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="transfer-stat"><span>Purchasing price</span><strong id="infoPurchase">—</strong></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="transfer-stat"><span>Retail selling price</span><strong id="infoRetail">—</strong></div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="transfer-stat"><span>Wholesale price</span><strong id="infoWholesale">—</strong></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr><th class="text-start">Product</th><th>Unit</th><th>Available</th><th>Purchasing (TZS)</th><th>Retail (TZS)</th><th>Wholesale (TZS)</th><th>Transfer</th><th>Value (TZS)</th><th></th></tr>
                        </thead>
                        <tbody id="transferLines"></tbody>
                        <tfoot>
                            <tr class="table-light"><th colspan="6" class="text-end">Total</th><th class="text-center" id="totalUnits">0</th><th class="text-end" id="totalValue">0</th><th></th></tr>
                        </tfoot>
                    </table>
                </div>

                <label class="form-label" for="transferNote">Note (optional)</label>
                <textarea name="note" id="transferNote" class="form-control" rows="2" maxlength="1000"></textarea>
                <input type="hidden" name="items" id="transferItems">
            </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="<?php echo e(route('stock-transfers.index')); ?>" class="btn btn-outline-secondary px-4">Cancel</a>
            <button class="btn btn-primary px-4" id="transferSubmit" disabled><i class="bi bi-check-circle me-1"></i> Transfer stock</button>
        </div>
    </form>
</div>

<style>
.transfer-stat { border:1px solid #e5e7eb; border-radius:.5rem; padding:.45rem .7rem; background:#f9fafb; height:100%; }
.transfer-stat span { display:block; font-size:.72rem; color:#6b7280; }
.transfer-stat strong { font-size:1rem; }
.transfer-results .list-group-item small { color:#6b7280; }
</style>

<?php echo $__env->make('pages.stock_transfers._create_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/stock_transfers/create.blade.php ENDPATH**/ ?>