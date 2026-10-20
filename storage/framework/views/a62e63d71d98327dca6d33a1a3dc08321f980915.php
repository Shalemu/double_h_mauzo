<?php $__env->startSection('title', 'Supplier Products'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $canAssign = auth()->user()->hasPermission('suppliers.edit');
    $canPrice = auth()->user()->hasPermission('prices.edit');
    $money = fn ($v) => $v !== null ? number_format((float) $v, 2) : '—';
?>
<style>
.assign-panel { border:1px solid #f0dcdd; border-radius:.8rem; background:#fdf9f9; padding:1rem 1.15rem; }
.assign-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; max-height:300px; overflow:auto; display:none; }
.assign-results.is-open { display:block; }
.assign-stat { border:1px solid #e5e7eb; border-radius:.5rem; background:#fff; padding:.4rem .65rem; height:100%; }
.assign-stat span { display:block; font-size:.72rem; color:#6b7280; }
.assign-stat strong { font-size:.95rem; }
.price-diff { font-size:.72rem; }
</style>

<div class="container-fluid py-4">
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Supplier Products</h5>
                    <small class="text-muted">Which supplier supplies which product, and at what price.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('procurement.prices')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-tags"></i> Price Manager</a>
                    <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-truck"></i> Suppliers</a>
                    <?php if($canAssign): ?>
                        <button type="button" class="btn btn-sm btn-primary" id="toggleAssign" aria-expanded="<?php echo e($errors->any() ? 'true' : 'false'); ?>" aria-controls="assignPanel">
                            <i class="bi bi-link-45deg"></i> Assign product to supplier
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'assigned' ? 'active' : ''); ?>" href="<?php echo e(request()->fullUrlWithQuery(['tab' => 'assigned', 'page' => null])); ?>">
                        <i class="bi bi-link-45deg me-1"></i> Assigned products <span class="badge bg-light text-dark border ms-1"><?php echo e(number_format($assigned->total())); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'history' ? 'active' : ''); ?>" href="<?php echo e(request()->fullUrlWithQuery(['tab' => 'history', 'assigned_page' => null])); ?>">
                        <i class="bi bi-clock-history me-1"></i> From purchases
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <?php if($canAssign): ?>
                
                <form method="POST" action="<?php echo e(route('procurement.supplierProducts.assign')); ?>" class="assign-panel mb-4" id="assignPanel" <?php echo e($errors->any() ? '' : 'hidden'); ?>>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" id="assignProductId" value="<?php echo e(old('product_id')); ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold" id="assignTitle"><i class="bi bi-link-45deg me-1"></i> Assign product to supplier</h6>
                        <button type="button" class="btn-close" id="closeAssign" aria-label="Close"></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small" for="assignSupplier">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="assignSupplier" class="form-select" required>
                                <option value="">-- select supplier --</option>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($supplier->id); ?>" <?php echo e((string) old('supplier_id', $supplierId) === (string) $supplier->id ? 'selected' : ''); ?>><?php echo e($supplier->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-8 position-relative">
                            <label class="form-label small" for="assignSearch">Product <span class="text-danger">*</span></label>
                            <input type="search" id="assignSearch" class="form-control" autocomplete="off" placeholder="Type product name or scan barcode...">
                            <div class="list-group shadow assign-results" id="assignResults"></div>
                        </div>
                    </div>

                    <div id="assignDetails" hidden>
                        <div class="row g-2 mt-2">
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Shop / Store</span><strong id="aShop">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Unit</span><strong id="aUnit">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>In stock</span><strong id="aStock">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Current purchasing price</span><strong id="aCost">—</strong></div></div>
                            <div class="col-6 col-lg"><div class="assign-stat"><span>Retail / Wholesale</span><strong id="aSell">—</strong></div></div>
                        </div>
                        <div class="row g-3 align-items-end mt-1">
                            <div class="col-md-4">
                                <label class="form-label small" for="assignNewPrice">New price <span class="text-muted">(optional — if the price changed)</span></label>
                                <input type="number" name="new_price" id="assignNewPrice" class="form-control" min="0" step="any" value="<?php echo e(old('new_price')); ?>" placeholder="Leave empty to keep current">
                            </div>
                            <div class="col-md-5">
                                <?php if($canPrice): ?>
                                    <div class="small text-muted mb-1"><i class="bi bi-info-circle"></i> Saving also sets this as the product's purchasing price.</div>
                                    <a href="#" class="small" id="assignPriceManager" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Open this product in Price Manager</a>
                                <?php else: ?>
                                    <div class="small text-muted"><i class="bi bi-info-circle"></i> The purchasing price stays as it is — you don't have permission to change prices.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button class="btn btn-primary w-100" id="assignSubmit" disabled><i class="bi bi-check-lg"></i> Save</button>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" id="assignSummary"></div>
                    </div>
                </form>
            <?php endif; ?>

            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Supplier</label>
                    <select name="supplier" class="form-select form-select-sm">
                        <option value="">All suppliers</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>" <?php echo e(((string) $supplierId === (string) $supplier->id) ? 'selected' : ''); ?>><?php echo e($supplier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Shop / Store</label>
                    <select name="shop" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small mb-1">Product</label>
                    <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search product...">
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
            </form>

            <?php if($tab === 'assigned'): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Supplier</th>
                                <th class="text-start">Product</th>
                                <th>Unit</th>
                                <th>Supplier price (Tsh)</th>
                                <th>Our purchasing price</th>
                                <th>Retail</th>
                                <th>Wholesale</th>
                                <th>In stock</th>
                                <th style="width:160px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $assigned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $diff = (float) $row->supplier_price - (float) $row->current_cost; ?>
                                <tr>
                                    <td class="text-start"><?php echo e($row->supplier); ?><div class="small text-muted"><?php echo e($row->phone); ?></div></td>
                                    <td class="text-start"><?php echo e($row->product); ?><div class="small text-muted"><?php echo e($row->shop ?? '—'); ?></div></td>
                                    <td><span class="badge bg-secondary"><?php echo e($row->unit ?? '—'); ?></span></td>
                                    <td class="fw-semibold"><?php echo e($money($row->supplier_price)); ?></td>
                                    <td>
                                        <?php echo e($money($row->current_cost)); ?>

                                        <?php if(abs($diff) >= 0.01): ?>
                                            <div class="price-diff <?php echo e($diff > 0 ? 'text-danger' : 'text-success'); ?>">
                                                Supplier <?php echo e($diff > 0 ? 'higher' : 'lower'); ?> by <?php echo e(number_format(abs($diff), 2)); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($money($row->selling_price)); ?></td>
                                    <td><?php echo e($money($row->wholesale_price)); ?></td>
                                    <td><?php echo e(number_format((int) $row->in_stock)); ?></td>
                                    <td class="text-nowrap">
                                        <?php if($canAssign): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary js-edit-assignment" aria-label="Change price"
                                                    data-supplier="<?php echo e($row->supplier_id); ?>"
                                                    data-product="<?php echo e(json_encode(['id' => $row->product_id, 'name' => $row->product, 'shop' => $row->shop, 'unit' => $row->unit, 'quantity' => (int) $row->in_stock, 'purchase_price' => $row->current_cost, 'selling_price' => $row->selling_price, 'wholesale_price' => $row->wholesale_price])); ?>"
                                                    data-price="<?php echo e($row->supplier_price); ?>"><i class="bi bi-pencil"></i></button>
                                        <?php endif; ?>
                                        <?php if($canPrice && abs($diff) >= 0.01): ?>
                                            <form method="POST" action="<?php echo e(route('procurement.supplierProducts.applyPrice', $row->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-outline-success" title="Use supplier price as purchasing price" aria-label="Apply supplier price"><i class="bi bi-arrow-down-circle"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if($canAssign): ?>
                                            <form method="POST" action="<?php echo e(route('procurement.supplierProducts.remove', $row->id)); ?>" class="d-inline"
                                                  onsubmit="return confirm('Remove <?php echo e(addslashes($row->product)); ?> from <?php echo e(addslashes($row->supplier)); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="btn btn-sm btn-outline-danger" aria-label="Remove"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9" class="text-muted py-4">
                                    No products assigned yet.
                                    <?php if($canAssign): ?> Click <strong>Assign product to supplier</strong> to link one. <?php endif; ?>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3"><?php echo e($assigned->links('pagination::bootstrap-4')); ?></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Product</th>
                                <th>Deliveries</th>
                                <th>Total qty bought</th>
                                <th>Cost range (Tsh)</th>
                                <th>Current cost</th>
                                <th>Selling price</th>
                                <th>In stock</th>
                                <th>Last purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-start"><?php echo e($row->supplier); ?><div class="small text-muted"><?php echo e($row->phone); ?></div></td>
                                    <td class="text-start"><?php echo e($row->product ?? 'Deleted product'); ?></td>
                                    <td><?php echo e($row->deliveries); ?></td>
                                    <td><?php echo e(number_format($row->total_qty)); ?></td>
                                    <td class="text-nowrap">
                                        <?php echo e(number_format($row->min_cost, 2)); ?><?php if($row->max_cost != $row->min_cost): ?> – <?php echo e(number_format($row->max_cost, 2)); ?><?php endif; ?>
                                    </td>
                                    <td><?php echo e($row->current_cost !== null ? number_format($row->current_cost, 2) : '—'); ?></td>
                                    <td><?php echo e($row->selling_price !== null ? number_format($row->selling_price, 2) : '—'); ?></td>
                                    <td><?php echo e($row->in_stock ?? '—'); ?></td>
                                    <td class="text-nowrap"><?php echo e(\Carbon\Carbon::parse($row->last_purchased)->format('d M Y')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9" class="text-muted py-4">
                                    No supplier deliveries found. Products appear here once a purchase is recorded against a supplier.
                                    <a href="<?php echo e(route('purchases.create')); ?>">Record a purchase</a>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3"><?php echo e($rows->links('pagination::bootstrap-4')); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($canAssign): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lookupUrl = <?php echo json_encode(route('procurement.supplierProducts.lookup'), 15, 512) ?>;
    const priceManagerUrl = <?php echo json_encode(route('procurement.prices'), 15, 512) ?>;
    const panel = document.getElementById('assignPanel');
    const toggle = document.getElementById('toggleAssign');
    const supplier = document.getElementById('assignSupplier');
    const search = document.getElementById('assignSearch');
    const results = document.getElementById('assignResults');
    const productId = document.getElementById('assignProductId');
    const details = document.getElementById('assignDetails');
    const newPrice = document.getElementById('assignNewPrice');
    const canPrice = <?php echo json_encode($canPrice, 15, 512) ?>;
    const pmLink = document.getElementById('assignPriceManager');
    const submit = document.getElementById('assignSubmit');
    const summary = document.getElementById('assignSummary');
    const money = n => (n === null || n === undefined || n === '') ? '—' : Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let selected = null;
    let timer = null;

    function open(show) {
        panel.hidden = !show;
        toggle.setAttribute('aria-expanded', show ? 'true' : 'false');
        if (show) (supplier.value ? search : supplier).focus();
    }

    function sync() {
        details.hidden = !selected;
        submit.disabled = !(selected && supplier.value);
        const hasNew = newPrice.value !== '';
        if (!selected) { summary.textContent = ''; return; }
        const supplierName = supplier.value ? supplier.options[supplier.selectedIndex].text : 'the supplier';
        const price = hasNew ? Number(newPrice.value) : Number(selected.purchase_price || 0);
        summary.textContent = selected.name + ' will be linked to ' + supplierName + ' at Tsh ' + money(price)
            + (canPrice && price !== Number(selected.purchase_price || 0) ? ', and its purchasing price changes from ' + money(selected.purchase_price) + ' to ' + money(price) + '.' : '.');
    }

    function pick(p) {
        selected = p;
        productId.value = p ? p.id : '';
        results.classList.remove('is-open');
        if (p) {
            search.value = p.name;
            document.getElementById('aShop').textContent = p.shop || '—';
            document.getElementById('aUnit').textContent = p.unit || '—';
            document.getElementById('aStock').textContent = Number(p.quantity || 0).toLocaleString();
            document.getElementById('aCost').textContent = 'Tsh ' + money(p.purchase_price);
            document.getElementById('aSell').textContent = money(p.selling_price) + ' / ' + money(p.wholesale_price);
            if (pmLink) pmLink.href = priceManagerUrl + '?' + new URLSearchParams({ q: p.name });
            newPrice.focus();
        }
        sync();
    }

    toggle.addEventListener('click', () => open(panel.hidden));
    document.getElementById('closeAssign').addEventListener('click', () => open(false));
    [supplier, newPrice].forEach(el => el.addEventListener('input', sync));

    search.addEventListener('input', function () {
        if (selected) pick(null);
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) { results.classList.remove('is-open'); return; }
        timer = setTimeout(function () {
            fetch(lookupUrl + '?' + new URLSearchParams({ q: term }), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(function (list) {
                    results.replaceChildren();
                    if (!list.length) {
                        const empty = document.createElement('div');
                        empty.className = 'list-group-item small text-muted';
                        empty.textContent = 'No products match.';
                        results.appendChild(empty);
                    }
                    list.forEach(function (p) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2';
                        const left = document.createElement('div');
                        const name = document.createElement('div');
                        name.textContent = p.name;
                        const meta = document.createElement('small');
                        meta.className = 'text-muted';
                        meta.textContent = (p.shop || '—') + ' · ' + (p.unit || '—') + ' · ' + Number(p.quantity || 0).toLocaleString() + ' in stock';
                        left.append(name, meta);
                        const cost = document.createElement('span');
                        cost.className = 'badge bg-light text-dark border';
                        cost.textContent = 'Tsh ' + money(p.purchase_price);
                        btn.append(left, cost);
                        btn.addEventListener('click', () => pick(p));
                        results.appendChild(btn);
                    });
                    results.classList.add('is-open');
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!search.parentElement.contains(e.target)) results.classList.remove('is-open');
    });

    // Edit = reopen the panel for that supplier/product with its supplier price as the new price.
    document.querySelectorAll('.js-edit-assignment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            supplier.value = btn.dataset.supplier;
            pick(JSON.parse(btn.dataset.product));
            newPrice.value = btn.dataset.price;
            sync();
            open(true);
            newPrice.focus();
            newPrice.select();
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    sync();
});
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/procurement/supplier_products.blade.php ENDPATH**/ ?>