@extends('main')

@section('title', 'Issue Stock to Distributor')

@section('content')
@include('pages.products._create_styles')
@php
    // Back after an error: the products that were on the list.
    $oldItems = old('items');
    $oldItems = is_string($oldItems) ? (json_decode($oldItems, true) ?: []) : (is_array($oldItems) ? $oldItems : []);
@endphp
<style>
.dist-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; max-height:320px; overflow:auto; display:none; background:#fff; border-radius:.5rem; }
.dist-results.is-open { display:block; }
#distLines tr.just-added { animation: distFlash 1.2s ease-out; }
@keyframes distFlash { from { background: rgba(25,135,84,.18); } to { background: transparent; } }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <p class="text-muted mb-0">
            The products leave the store now and go with the distributor. They sell them on their POS; when they're back, a supervisor settles the trip.
        </p>
        <a href="{{ route('distributions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> All distributions</a>
    </div>

    @if($stores->isEmpty())
        <div class="alert alert-info">You don't have a store yet. <a href="{{ route('stores.index') }}">Add a store</a> under Manage Business → All Stores.</div>
    @endif

    <form method="POST" action="{{ route('distributions.store') }}" id="distForm" novalidate>
        @csrf

        <!-- Trip details -->
        <div class="card product-form-section accent-primary mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-primary"><i class="bi bi-truck-flatbed"></i></div>
                <div>
                    <p class="product-form-section-title">Trip Details</p>
                    <p class="product-form-section-subtitle">Which store the stock leaves from, and who takes it</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="distStore">From Store <span class="text-danger">*</span></label>
                        <select name="store_id" id="distStore" class="form-select" required>
                            <option value="">-- select store --</option>
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}" {{ (string) old('store_id', $storeId) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="distStaff">Distributor <span class="text-danger">*</span></label>
                        <select name="staff_id" id="distStaff" class="form-select" required>
                            <option value="">-- select distributor --</option>
                            @foreach($staff as $person)
                                @php $out = $outTrips[$person->id] ?? null; @endphp
                                <option value="{{ $person->id }}" {{ $out ? 'disabled' : '' }} {{ (string) old('staff_id') === (string) $person->id ? 'selected' : '' }}>
                                    {{ $person->full_name }}{{ $person->shop ? ' — ' . $person->shop->name : '' }}{{ $out ? ' (out on trip #' . $out . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Someone already out must be settled before a new trip.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="distNote">Note <span class="text-muted">(optional)</span></label>
                        <input type="text" name="note" id="distNote" class="form-control" maxlength="1000" value="{{ old('note') }}" placeholder="e.g. route, vehicle, area">
                    </div>
                </div>
            </div>
        </div>

        <!-- Add products -->
        <div class="card product-form-section accent-success mb-4">
            <div class="product-form-section-header">
                <div class="product-form-section-icon icon-success"><i class="bi bi-upc-scan"></i></div>
                <div>
                    <p class="product-form-section-title">Add Products</p>
                    <p class="product-form-section-subtitle">Scan a barcode or search by name, enter the quantity, then Add</p>
                </div>
            </div>
            <div class="product-form-section-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="distBarcode">Barcode</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc"></i></span>
                            <input type="text" id="distBarcode" class="form-control" placeholder="Scan or type, then Enter" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6 position-relative">
                        <label class="form-label" for="distSearch">Product</label>
                        <input type="search" id="distSearch" class="form-control" autocomplete="off" placeholder="Search by name in the store...">
                        <div class="list-group shadow dist-results" id="distResults" role="listbox" aria-label="Matching products"></div>
                    </div>
                    <div class="col-lg-2 col-md-6 col-6">
                        <label class="form-label" for="distQty">Quantity</label>
                        <input type="number" id="distQty" class="form-control" min="1" step="1" placeholder="0">
                    </div>
                    <div class="col-lg-2 col-md-6 col-6">
                        <button type="button" class="btn btn-primary w-100" id="distAdd"><i class="bi bi-plus-lg me-1"></i>Add</button>
                    </div>
                    <div class="col-12 mt-1">
                        <div class="small" id="distInfo" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The list -->
        <div class="card shadow-sm mb-4" style="border-radius:1rem; overflow:hidden;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Products the distributor takes <span class="badge bg-secondary" id="distCount">0</span></h6>
                <span class="small text-muted">Total units: <strong class="text-body" id="distUnits">0</strong></span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th style="width:40px">#</th><th class="text-start">Product</th><th>Barcode</th><th>In store</th><th style="width:140px">Taking</th><th style="width:70px">Remove</th></tr>
                    </thead>
                    <tbody id="distLines"></tbody>
                </table>
            </div>
        </div>

        <input type="hidden" name="items" id="distItems">

        <div class="d-flex justify-content-end flex-wrap gap-2 mb-5">
            <a href="{{ route('distributions.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-success px-4" id="distSubmit"><i class="bi bi-box-arrow-right me-1"></i> Issue stock</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = @json(route('distributions.productSearch'));
    const $ = id => document.getElementById(id);
    const form = $('distForm'), store = $('distStore'), staff = $('distStaff');
    const barcode = $('distBarcode'), search = $('distSearch'), results = $('distResults');
    const qty = $('distQty'), info = $('distInfo'), lines = $('distLines');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    let items = (@json($oldItems) || []).map(i => ({
        id: Number(i.product_id), name: i.name || ('Product #' + i.product_id), barcode: i.barcode || '',
        available: Number(i.available || i.quantity), quantity: Number(i.quantity),
    }));
    let selected = null;      // product picked in the search / scan, waiting for Add
    let searchTimer = null, barcodeTimer = null, lastBarcode = '';

    const storeName = () => store.value ? store.options[store.selectedIndex].text : 'the store';
    const onList = id => items.filter(i => i.id === id).reduce((s, i) => s + i.quantity, 0);

    function say(html, tone) { info.className = 'small ' + (tone || ''); info.innerHTML = html; }

    function render(flashId) {
        lines.replaceChildren();
        if (!items.length) {
            lines.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No products yet. Scan a barcode or search above, then click <strong>Add</strong>.</td></tr>';
        }
        items.forEach(function (item, index) {
            const tr = document.createElement('tr');
            if (item.id === flashId) tr.className = 'just-added';
            tr.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td class="fw-semibold">${esc(item.name)}</td>
                <td class="text-center small text-muted">${esc(item.barcode || '—')}</td>
                <td class="text-center">${item.available}</td>
                <td><input type="number" class="form-control form-control-sm text-center" min="1" max="${item.available}" step="1"
                           value="${item.quantity}" data-index="${index}" aria-label="Quantity for ${esc(item.name)}"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-remove="${index}" aria-label="Remove ${esc(item.name)}"><i class="bi bi-trash"></i></button></td>`;
            lines.appendChild(tr);
        });
        $('distCount').textContent = items.length;
        $('distUnits').textContent = items.reduce((s, i) => s + i.quantity, 0);
        $('distItems').value = JSON.stringify(items.map(i => ({ product_id: i.id, quantity: i.quantity, name: i.name, barcode: i.barcode, available: i.available })));
    }

    // A product was found (scan or search): ready to Add.
    function pick(p) {
        selected = { id: Number(p.id), name: p.name, barcode: p.barcode || '', available: Number(p.quantity) };
        search.value = p.name;
        results.classList.remove('is-open');
        const left = selected.available - onList(selected.id);
        if (selected.available <= 0) {
            say('<i class="bi bi-x-circle"></i> "' + esc(p.name) + '" is out of stock in ' + esc(storeName()) + '.', 'text-danger');
            selected = null; search.value = ''; // nothing to add: don't leave its name in the search box
            return;
        }
        if (left <= 0) {
            say('<i class="bi bi-info-circle"></i> All ' + selected.available + ' of "' + esc(p.name) + '" in ' + esc(storeName()) + ' are already on the list.', 'text-primary');
            selected = null; search.value = '';
            return;
        }
        qty.max = left;
        qty.value = 1;
        say('<i class="bi bi-check-circle text-success"></i> In ' + esc(storeName()) + ': <strong>' + selected.available + '</strong>'
            + (left < selected.available ? ' (' + left + ' not yet on the list)' : ''), '');
        qty.focus();
        qty.select();
    }

    function add() {
        if (!store.value) { say('<i class="bi bi-exclamation-triangle"></i> Choose the store first.', 'text-warning-emphasis'); store.focus(); return; }
        if (!selected) { say('<i class="bi bi-exclamation-triangle"></i> Scan a barcode or search for a product first.', 'text-warning-emphasis'); search.focus(); return; }
        const q = parseInt(qty.value, 10);
        const left = selected.available - onList(selected.id);
        if (!q || q < 1) { say('<i class="bi bi-exclamation-triangle"></i> Enter how many to take.', 'text-warning-emphasis'); qty.focus(); return; }
        if (q > left) {
            say('<i class="bi bi-x-circle"></i> Only <strong>' + left + '</strong> of "' + esc(selected.name) + '" ' + (left === selected.available ? 'in ' + esc(storeName()) : 'left to add (some are already on the list)') + '.', 'text-danger');
            qty.value = left; qty.focus(); qty.select();
            return;
        }
        const existing = items.find(i => i.id === selected.id);
        if (existing) existing.quantity += q; else items.push({ ...selected, quantity: q });
        const flash = selected.id;
        selected = null; search.value = ''; qty.value = ''; barcode.value = ''; lastBarcode = '';
        say('', '');
        render(flash);
        barcode.focus(); // ready for the next scan
    }

    // ---- Barcode: Enter (scanners send it), a short pause, or leaving the box ----
    function lookupBarcode(force) {
        clearTimeout(barcodeTimer);
        const code = barcode.value.trim();
        if (!code || (!force && code === lastBarcode)) return;
        if (!store.value) { say('<i class="bi bi-exclamation-triangle"></i> Choose the store first, then scan.', 'text-warning-emphasis'); return; }
        lastBarcode = code;
        say('<span class="spinner-border spinner-border-sm"></span> Looking up ' + esc(code) + '…', 'text-muted');
        fetch(searchUrl + '?' + new URLSearchParams({ barcode: code, store: store.value }), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function (found) {
                if (barcode.value.trim() !== code) return;
                if (!found.length) {
                    say('<i class="bi bi-x-circle"></i> No product with barcode ' + esc(code) + ' in ' + esc(storeName()) + '.', 'text-danger');
                    barcode.select();
                    return;
                }
                pick(found[0]);
            })
            .catch(() => say('<i class="bi bi-wifi-off"></i> Could not look up the barcode. Check your connection.', 'text-danger'));
    }
    barcode.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); lookupBarcode(true); } });
    barcode.addEventListener('input', () => {
        clearTimeout(barcodeTimer);
        if (barcode.value.trim().length >= 3) barcodeTimer = setTimeout(() => lookupBarcode(false), 600);
    });
    barcode.addEventListener('change', () => lookupBarcode(false));

    // ---- Product list: all the store's products on click, narrowed as you type ----
    let storeProducts = null; // the store's in-stock products (loaded once per store)

    function showList(products, note) {
        results.replaceChildren();
        if (note) results.insertAdjacentHTML('beforeend', '<div class="list-group-item small text-muted bg-light">' + note + '</div>');
        if (!products.length) {
            results.insertAdjacentHTML('beforeend', '<div class="list-group-item small text-muted">No products in stock match.</div>');
        }
        products.forEach(function (p) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center small';
            const taken = onList(Number(p.id));
            btn.innerHTML = '<span><strong>' + esc(p.name) + '</strong>' + (p.barcode ? ' <span class="text-muted">· ' + esc(p.barcode) + '</span>' : '')
                + (taken ? ' <span class="badge text-bg-primary">' + taken + ' on list</span>' : '')
                + '</span><span class="badge text-bg-light border">In store: ' + p.quantity + '</span>';
            btn.addEventListener('click', () => pick(p));
            results.appendChild(btn);
        });
        results.classList.add('is-open');
    }

    function openList() {
        if (!store.value) { say('<i class="bi bi-exclamation-triangle"></i> Choose the store first.', 'text-warning-emphasis'); return; }
        const term = search.value.trim().toLowerCase();
        if (term.length >= 2) return; // a real search is running
        const show = () => {
            const list = term ? storeProducts.filter(p => p.name.toLowerCase().includes(term) || String(p.barcode || '').includes(term)) : storeProducts;
            showList(list, storeProducts.length + ' product(s) in stock' + (storeProducts.length >= 100 ? ' (first 100, type to search all)' : '') + '. Type to narrow.');
        };
        if (storeProducts) return show();
        // Focus and click both ask on the first open: load the list once.
        loadingProducts = loadingProducts || fetch(searchUrl + '?' + new URLSearchParams({ browse: 1, store: store.value }), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json()).then(list => { storeProducts = list; loadingProducts = null; });
        loadingProducts.then(() => { if (storeProducts && document.activeElement === search) show(); });
    }
    let loadingProducts = null;

    search.addEventListener('input', function () {
        selected = null;
        clearTimeout(searchTimer);
        const term = search.value.trim();
        if (!store.value) { say('<i class="bi bi-exclamation-triangle"></i> Choose the store first.', 'text-warning-emphasis'); return; }
        if (term.length < 2) { openList(); return; } // empty or one letter: the store's list
        searchTimer = setTimeout(function () {
            fetch(searchUrl + '?' + new URLSearchParams({ q: term, store: store.value }), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(products => { if (search.value.trim() === term) showList(products); });
        }, 250);
    });
    // Clicking into the box shows the list and selects what's typed, so typing starts a new search.
    search.addEventListener('focus', () => { search.select(); openList(); });
    search.addEventListener('click', openList);
    // ↓ / Enter in the search box picks the first match.
    search.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = results.querySelector('button');
            if (results.classList.contains('is-open') && first) first.click();
        }
    });
    document.addEventListener('click', e => { if (!search.parentElement.contains(e.target)) results.classList.remove('is-open'); });

    // ---- Quantity + list ----
    qty.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); add(); } });
    $('distAdd').addEventListener('click', add);

    lines.addEventListener('input', function (e) {
        const input = e.target.closest('input[data-index]');
        if (!input) return;
        const item = items[input.dataset.index];
        let q = parseInt(input.value, 10) || 0;
        if (q > item.available) {
            say('<i class="bi bi-x-circle"></i> Only <strong>' + item.available + '</strong> of "' + esc(item.name) + '" in ' + esc(storeName()) + '.', 'text-danger');
            q = item.available; input.value = q;
        }
        item.quantity = Math.max(q, 0);
        $('distUnits').textContent = items.reduce((s, i) => s + i.quantity, 0);
        $('distItems').value = JSON.stringify(items.map(i => ({ product_id: i.id, quantity: i.quantity, name: i.name, barcode: i.barcode, available: i.available })));
    });
    lines.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove]');
        if (!btn) return;
        items.splice(Number(btn.dataset.remove), 1);
        render();
    });

    // Products belong to one store: changing it clears the list.
    store.addEventListener('change', async function () {
        if (items.length && !(await confirmDialog('Change the store? The products on the list are from ' + 'the previous store and will be removed.', { danger: true, confirmText: 'Yes, change store' }))) {
            store.value = store.dataset.previous || '';
            return;
        }
        store.dataset.previous = store.value;
        storeProducts = null; loadingProducts = null; // another store, another product list
        items = []; selected = null; search.value = ''; barcode.value = ''; lastBarcode = ''; say('', '');
        render();
    });
    store.dataset.previous = store.value;

    // ---- Issue ----
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const problem = !store.value ? ['distStore', 'Choose the store.']
            : !staff.value ? ['distStaff', 'Choose the distributor.']
            : !items.length ? ['distBarcode', 'Add at least one product.']
            : items.some(i => i.quantity < 1) ? ['distLines', 'Every product on the list needs a quantity of at least 1 (or remove it).']
            : null;
        if (problem) { notify('warning', 'Not ready yet', problem[1]); $(problem[0])?.focus(); return; }
        const units = items.reduce((s, i) => s + i.quantity, 0);
        const ok = await confirmDialog('Issue ' + units + ' unit(s) of ' + items.length + ' product(s) from ' + storeName() + ' to '
            + staff.options[staff.selectedIndex].text.split(' — ')[0].trim() + '? The stock leaves the store now.', { danger: false, confirmText: 'Yes, issue stock' });
        if (!ok) return;
        const btn = $('distSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Issuing...';
        form.submit();
    });

    render();
});
</script>
@endsection
