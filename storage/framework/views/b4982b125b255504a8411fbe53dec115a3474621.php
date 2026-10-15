
<?php
    $isSale = $type === 'sale';
    $lookupUrl = $isSale ? route('returns.saleItemLookup') : route('returns.purchaseItemLookup');
    $storeUrl = $isSale ? route('sales-returns.store') : route('purchase-returns.store');
?>

<div class="modal fade" id="recordReturnModal" tabindex="-1" aria-labelledby="recordReturnTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordReturnTitle">
                    <?php echo e($isSale ? 'Record a sales return (customer brings goods back)' : 'Record a purchase return (goods go back to supplier)'); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="returnError"></div>

                <label class="form-label" for="returnSearch">
                    <?php echo e($isSale ? 'Find the sale — product name or sale number' : 'Find the purchase — product name or invoice number'); ?>

                </label>
                <input type="search" id="returnSearch" class="form-control mb-2" autocomplete="off"
                       placeholder="<?php echo e($isSale ? 'e.g. Nivea or 154' : 'e.g. Nivea or INV-0012'); ?>">
                <div class="list-group mb-3" id="returnResults" style="max-height:240px; overflow:auto"></div>

                <form id="returnForm" class="d-none">
                    <div class="border rounded p-2 mb-3 small" id="returnSelected"></div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="returnQty">Quantity to return</label>
                            <input type="number" id="returnQty" class="form-control" min="1" required>
                            <div class="form-text" id="returnQtyHelp"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="returnAmount"><?php echo e($isSale ? 'Refund amount (Tsh)' : 'Credit from supplier (Tsh)'); ?></label>
                            <input type="number" id="returnAmount" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="returnReason">Reason</label>
                            <input type="text" id="returnReason" class="form-control" maxlength="255" placeholder="Damaged, expired...">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="returnSubmit" disabled>Record return</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isSale = <?php echo json_encode($isSale, 15, 512) ?>;
    const lookupUrl = <?php echo json_encode($lookupUrl, 15, 512) ?>;
    const storeUrl = <?php echo json_encode($storeUrl, 15, 512) ?>;
    const shopId = <?php echo json_encode($shopId, 15, 512) ?>;
    const search = document.getElementById('returnSearch');
    const results = document.getElementById('returnResults');
    const form = document.getElementById('returnForm');
    const selectedBox = document.getElementById('returnSelected');
    const qty = document.getElementById('returnQty');
    const qtyHelp = document.getElementById('returnQtyHelp');
    const amount = document.getElementById('returnAmount');
    const reason = document.getElementById('returnReason');
    const submit = document.getElementById('returnSubmit');
    const errorBox = document.getElementById('returnError');
    let selected = null;
    let timer = null;

    const money = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const unitPrice = () => isSale
        ? (selected.price - (selected.discount || 0) / Math.max(selected.quantity, 1))
        : selected.purchase_price;
    const maxQty = () => isSale ? selected.quantity : Math.min(selected.quantity, selected.in_stock);

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.toggle('d-none', !message);
    }

    function choose(item) {
        selected = item;
        results.replaceChildren();
        form.classList.remove('d-none');
        selectedBox.textContent = isSale
            ? `${item.product} — sale #${item.sale_id}, ${item.shop}, ${new Date(item.created_at).toLocaleDateString()} · ${item.quantity} sold at Tsh ${money(item.price)}`
            : `${item.product} — invoice ${item.invoice_number || '#'}, ${item.supplier || 'no supplier'}, ${item.shop} · ${item.quantity} bought at Tsh ${money(item.purchase_price)}`;
        qty.max = maxQty();
        qty.value = 1;
        qtyHelp.textContent = 'Up to ' + maxQty() + (isSale ? '' : ' (limited by current stock)');
        amount.value = unitPrice().toFixed(2);
        submit.disabled = maxQty() < 1;
        qty.focus();
    }

    qty.addEventListener('input', function () {
        if (selected) amount.value = (unitPrice() * (parseInt(qty.value, 10) || 0)).toFixed(2);
    });

    search.addEventListener('input', function () {
        clearTimeout(timer);
        const term = search.value.trim();
        if (!term) { results.replaceChildren(); return; }
        timer = setTimeout(function () {
            const params = new URLSearchParams({ q: term });
            if (shopId) params.set('shop', shopId);
            fetch(lookupUrl + '?' + params, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(function (items) {
                    results.replaceChildren();
                    if (!items.length) {
                        const empty = document.createElement('div');
                        empty.className = 'list-group-item text-muted small';
                        empty.textContent = 'Nothing found that can still be returned.';
                        results.appendChild(empty);
                    }
                    items.forEach(function (item) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action small';
                        btn.textContent = isSale
                            ? `#${item.sale_id} · ${item.product} · qty ${item.quantity} · ${item.shop} · ${new Date(item.created_at).toLocaleDateString()}`
                            : `${item.invoice_number || 'Invoice'} · ${item.product} · qty ${item.quantity} · ${item.supplier || '—'} · ${item.shop}`;
                        btn.addEventListener('click', () => choose(item));
                        results.appendChild(btn);
                    });
                });
        }, 250);
    });

    submit.addEventListener('click', function () {
        if (!selected) return;
        const q = parseInt(qty.value, 10);
        if (!q || q < 1 || q > maxQty()) { showError('Quantity must be between 1 and ' + maxQty() + '.'); return; }

        const body = {
            product_id: selected.product_id,
            shop_id: selected.shop_id,
            quantity: q,
            amount: amount.value,
            reason: reason.value,
        };
        if (isSale) { body.sale_id = selected.id; body.sale_type = selected.sale_type || 'retail'; }
        else { body.purchase_item_id = selected.id; }

        submit.disabled = true;
        showError('');
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(body),
        })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(function ({ ok, data }) {
                if (!ok) {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                    throw new Error(firstError || data.message || 'Could not record the return.');
                }
                window.location.reload();
            })
            .catch(function (err) {
                showError(err.message);
                submit.disabled = false;
            });
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\returns\_record_modal.blade.php ENDPATH**/ ?>