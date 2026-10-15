<?php $__env->startSection('title', 'Stock Transfers'); ?>

<?php $__env->startSection('content'); ?>
<style>
.transfer-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; max-height:260px; overflow:auto; display:none; }
.transfer-results.is-open { display:block; }
</style>

<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Transfers</h5>
                <small class="text-muted">Move stock between shops. The receiving shop's matching product (same barcode or name) is topped up, or created if it doesn't stock it yet.</small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTransferModal" <?php echo e(($shops->count() < 2) ? 'disabled' : ''); ?>>
                <i class="bi bi-plus-circle"></i> New transfer
            </button>
        </div>
        <div class="card-body">
            <?php if($shops->count() < 2): ?>
                <div class="alert alert-info">You need at least two shops to transfer stock. <a href="<?php echo e(route('dashboard.shop')); ?>">Add a shop</a>.</div>
            <?php endif; ?>

            <?php echo $__env->make('pages.overview._shop_filter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>From</th><th>To</th><th>Items</th><th>Units</th><th>By</th><th>Note</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($transfer->id); ?></td>
                                <td class="text-nowrap"><?php echo e($transfer->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($transfer->fromShop->name ?? '—'); ?></td>
                                <td><?php echo e($transfer->toShop->name ?? '—'); ?></td>
                                <td>
                                    <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="small"><?php echo e($item->product_name); ?> × <?php echo e($item->quantity); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td class="text-center"><?php echo e($transfer->items->sum('quantity')); ?></td>
                                <td><?php echo e($transfer->transferredBy->name ?? '—'); ?></td>
                                <td><?php echo e($transfer->note ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No stock transfers yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($transfers->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>

<div class="modal fade" id="newTransferModal" tabindex="-1" aria-labelledby="newTransferTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('stock-transfers.store')); ?>" id="transferForm">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="newTransferTitle">New stock transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fromShop">From shop</label>
                        <select name="from_shop_id" id="fromShop" class="form-select" required>
                            <option value="">Choose...</option>
                            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="toShop">To shop</label>
                        <select name="to_shop_id" id="toShop" class="form-select" required>
                            <option value="">Choose...</option>
                            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($shop->id); ?>"><?php echo e($shop->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-8 position-relative">
                        <input type="search" id="transferSearch" class="form-control" autocomplete="off"
                               placeholder="Search product in the sending shop..." aria-label="Search product" disabled>
                        <div class="list-group shadow transfer-results" id="transferResults"></div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" id="transferQty" class="form-control" min="1" placeholder="Qty" aria-label="Quantity" disabled>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="transferAdd" disabled>Add</button>
                    </div>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light"><tr><th>Product</th><th class="text-center">Available</th><th class="text-center">Transfer</th><th></th></tr></thead>
                    <tbody id="transferLines"><tr><td colspan="4" class="text-muted text-center small">No products added yet.</td></tr></tbody>
                </table>

                <label class="form-label" for="transferNote">Note (optional)</label>
                <textarea name="note" id="transferNote" class="form-control" rows="2" maxlength="1000"></textarea>
                <input type="hidden" name="items" id="transferItems">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="transferSubmit" disabled>Transfer stock</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = <?php echo json_encode(route('stock-transfers.productSearch'), 15, 512) ?>;
    const fromShop = document.getElementById('fromShop');
    const toShop = document.getElementById('toShop');
    const search = document.getElementById('transferSearch');
    const results = document.getElementById('transferResults');
    const qty = document.getElementById('transferQty');
    const addBtn = document.getElementById('transferAdd');
    const lines = document.getElementById('transferLines');
    const itemsInput = document.getElementById('transferItems');
    const submit = document.getElementById('transferSubmit');
    let selected = null;
    let items = [];
    let timer = null;

    function syncState() {
        const ready = fromShop.value && toShop.value && fromShop.value !== toShop.value;
        search.disabled = qty.disabled = !fromShop.value;
        addBtn.disabled = !selected;
        submit.disabled = !(ready && items.length);
        itemsInput.value = JSON.stringify(items.map(i => ({ product_id: i.id, quantity: i.quantity })));
    }

    function render() {
        lines.replaceChildren();
        if (!items.length) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="4" class="text-muted text-center small">No products added yet.</td>';
            lines.appendChild(tr);
        }
        items.forEach(function (item, index) {
            const tr = document.createElement('tr');
            [item.name, item.available, item.quantity].forEach(function (value, col) {
                const td = document.createElement('td');
                td.textContent = value;
                if (col) td.className = 'text-center';
                tr.appendChild(td);
            });
            const td = document.createElement('td');
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => { items.splice(index, 1); render(); });
            td.appendChild(remove);
            tr.appendChild(td);
            lines.appendChild(tr);
        });
        syncState();
    }

    fromShop.addEventListener('change', function () {
        // Products belong to one shop, so changing the source clears the list.
        items = [];
        selected = null;
        search.value = '';
        render();
    });
    toShop.addEventListener('change', syncState);

    search.addEventListener('input', function () {
        selected = null;
        syncState();
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) { results.classList.remove('is-open'); return; }
        timer = setTimeout(function () {
            fetch(searchUrl + '?' + new URLSearchParams({ q: term, shop: fromShop.value }), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(function (products) {
                    results.replaceChildren();
                    if (!products.length) {
                        const empty = document.createElement('div');
                        empty.className = 'list-group-item small text-muted';
                        empty.textContent = 'No products in stock match.';
                        results.appendChild(empty);
                    }
                    products.forEach(function (p) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between small';
                        const name = document.createElement('span');
                        name.textContent = p.name;
                        const stock = document.createElement('span');
                        stock.className = 'text-muted';
                        stock.textContent = 'In stock: ' + p.quantity;
                        btn.append(name, stock);
                        btn.addEventListener('click', function () {
                            selected = p;
                            search.value = p.name;
                            qty.max = p.quantity;
                            qty.value = 1;
                            results.classList.remove('is-open');
                            syncState();
                            qty.focus();
                        });
                        results.appendChild(btn);
                    });
                    results.classList.add('is-open');
                });
        }, 250);
    });

    addBtn.addEventListener('click', function () {
        const q = parseInt(qty.value, 10);
        const already = items.filter(i => i.id === selected.id).reduce((s, i) => s + i.quantity, 0);
        if (!q || q < 1 || q + already > selected.quantity) {
            alert('Quantity must be between 1 and ' + (selected.quantity - already) + '.');
            return;
        }
        items.push({ id: selected.id, name: selected.name, available: selected.quantity, quantity: q });
        selected = null;
        search.value = '';
        qty.value = '';
        render();
        search.focus();
    });

    document.addEventListener('click', function (e) {
        if (!search.parentElement.contains(e.target)) results.classList.remove('is-open');
    });

    syncState();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/stock_transfers/index.blade.php ENDPATH**/ ?>