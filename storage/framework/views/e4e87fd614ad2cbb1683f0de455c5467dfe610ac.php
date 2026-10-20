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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsUrl = <?php echo json_encode(route('stock-transfers.productSearch'), 15, 512) ?>;
    const places = <?php echo json_encode($shopOptions, 15, 512) ?>;
    const preselectedId = <?php echo json_encode($preselected ? $preselected->id : null, 15, 512) ?>;

    const fromType = document.getElementById('fromType');
    const fromShop = document.getElementById('fromShop');
    const toType = document.getElementById('toType');
    const toShop = document.getElementById('toShop');
    const search = document.getElementById('transferSearch');
    const results = document.getElementById('transferResults');
    const qty = document.getElementById('transferQty');
    const addBtn = document.getElementById('transferAdd');
    const info = document.getElementById('transferInfo');
    const lines = document.getElementById('transferLines');
    const itemsInput = document.getElementById('transferItems');
    const submit = document.getElementById('transferSubmit');

    let products = [];   // every in-stock product of the source
    let selected = null;
    let items = [];
    const money = n => Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });

    function fillPlaces(select, type, excludeId, keepId) {
        const list = places.filter(p => p.type === type && String(p.id) !== String(excludeId));
        select.replaceChildren(new Option(list.length ? 'Choose ' + type + '...' : 'No ' + type + 's available', ''));
        list.forEach(p => select.add(new Option(p.name, p.id)));
        if (keepId && list.some(p => String(p.id) === String(keepId))) select.value = keepId;
    }

    function refreshTo() {
        fillPlaces(toShop, toType.value, fromShop.value, toShop.value);
        syncState();
    }

    function loadProducts() {
        // Products belong to one shop/store, so a new source starts a new list.
        products = [];
        items = [];
        pick(null);
        search.value = '';
        render();
        if (!fromShop.value) return;
        search.disabled = true;
        search.placeholder = 'Loading products...';
        fetch(productsUrl + '?' + new URLSearchParams({ shop: fromShop.value }), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function (list) {
                products = list;
                search.disabled = false;
                search.placeholder = list.length
                    ? 'Search ' + list.length + ' products by name or barcode...'
                    : 'No products in stock here';
            });
    }

    function remaining(p) {
        return p.quantity - items.filter(i => i.id === p.id).reduce((s, i) => s + i.quantity, 0);
    }

    function showResults() {
        const term = search.value.trim().toLowerCase();
        const matches = products.filter(p => !term
            || p.name.toLowerCase().includes(term)
            || (p.barcode && String(p.barcode).toLowerCase().includes(term))).slice(0, 50);

        results.replaceChildren();
        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item small text-muted';
            empty.textContent = 'No products match.';
            results.appendChild(empty);
        }
        matches.forEach(function (p) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2';
            const left = document.createElement('div');
            const name = document.createElement('div');
            name.textContent = p.name;
            const prices = document.createElement('small');
            prices.textContent = (p.unit ? 'Unit: ' + p.unit + ' · ' : '') + 'Buy ' + money(p.purchase_price) + ' · Retail ' + money(p.selling_price) + ' · Wholesale ' + money(p.wholesale_price);
            left.append(name, prices);
            const stock = document.createElement('span');
            const left_qty = remaining(p);
            stock.className = 'badge ' + (left_qty > 0 ? 'bg-success' : 'bg-secondary');
            stock.textContent = left_qty + (p.unit ? ' ' + p.unit : '') + ' available';
            btn.append(left, stock);
            btn.addEventListener('click', () => pick(p));
            results.appendChild(btn);
        });
        results.classList.add('is-open');
    }

    function pick(p) {
        selected = p;
        results.classList.remove('is-open');
        info.hidden = !p;
        const qtyUnit = document.getElementById('qtyUnit');
        qtyUnit.textContent = p && p.unit ? p.unit : '';
        qtyUnit.hidden = !(p && p.unit);
        if (p) {
            const left = remaining(p);
            search.value = p.name;
            document.getElementById('infoAvailable').textContent = left + (p.unit ? ' ' + p.unit : '');
            document.getElementById('infoPurchase').textContent = money(p.purchase_price);
            document.getElementById('infoRetail').textContent = money(p.selling_price);
            document.getElementById('infoWholesale').textContent = money(p.wholesale_price);
            qty.max = left;
            qty.value = left > 0 ? 1 : '';
            qty.focus();
        }
        syncState();
    }

    function syncState() {
        qty.disabled = !selected;
        addBtn.disabled = !selected;
        submit.disabled = !(fromShop.value && toShop.value && fromShop.value !== toShop.value && items.length);
        itemsInput.value = JSON.stringify(items.map(i => ({ product_id: i.id, quantity: i.quantity })));
    }

    function render() {
        lines.replaceChildren();
        if (!items.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 9;
            td.className = 'text-muted text-center small';
            td.textContent = 'No products added yet.';
            tr.appendChild(td);
            lines.appendChild(tr);
        }
        let units = 0, value = 0;
        items.forEach(function (item, index) {
            const tr = document.createElement('tr');
            const cells = [
                [item.name, ''],
                [item.unit || '—', 'text-center'],
                [item.available, 'text-center'],
                [money(item.purchase_price), 'text-end'],
                [money(item.selling_price), 'text-end'],
                [money(item.wholesale_price), 'text-end'],
                [item.quantity, 'text-center fw-semibold'],
                [money(item.quantity * item.purchase_price), 'text-end'],
            ];
            cells.forEach(function ([text, cls]) {
                const td = document.createElement('td');
                td.textContent = text;
                td.className = cls;
                tr.appendChild(td);
            });
            const td = document.createElement('td');
            td.className = 'text-center';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.setAttribute('aria-label', 'Remove ' + item.name);
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.addEventListener('click', () => { items.splice(index, 1); render(); });
            td.appendChild(remove);
            tr.appendChild(td);
            lines.appendChild(tr);
            units += item.quantity;
            value += item.quantity * item.purchase_price;
        });
        document.getElementById('totalUnits').textContent = units;
        document.getElementById('totalValue').textContent = money(value);
        syncState();
    }

    fromType.addEventListener('change', function () {
        fillPlaces(fromShop, fromType.value);
        loadProducts();
        refreshTo();
    });
    fromShop.addEventListener('change', function () {
        loadProducts();
        refreshTo();
    });
    toType.addEventListener('change', refreshTo);
    toShop.addEventListener('change', syncState);

    search.addEventListener('focus', showResults);
    search.addEventListener('input', function () {
        if (selected) { selected = null; info.hidden = true; syncState(); }
        showResults();
    });

    addBtn.addEventListener('click', function () {
        const q = parseInt(qty.value, 10);
        const left = remaining(selected);
        if (!q || q < 1 || q > left) {
            alert(left > 0 ? 'Quantity must be between 1 and ' + left + '.' : 'All available stock of this product is already added.');
            return;
        }
        items.push({
            id: selected.id, name: selected.name, unit: selected.unit, available: selected.quantity, quantity: q,
            purchase_price: Number(selected.purchase_price || 0),
            selling_price: Number(selected.selling_price || 0),
            wholesale_price: Number(selected.wholesale_price || 0),
        });
        pick(null);
        search.value = '';
        qty.value = '';
        render();
        search.focus();
    });
    qty.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); }
    });

    document.addEventListener('click', function (e) {
        if (!search.parentElement.contains(e.target)) results.classList.remove('is-open');
    });

    // Initial state: honour the shop picked in the page filter, if any.
    fillPlaces(fromShop, fromType.value, null, preselectedId);
    refreshTo();
    render();
    if (fromShop.value) loadProducts();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/stock_transfers/create.blade.php ENDPATH**/ ?>