@extends('main')

@section('title', 'New Item Purchase')

@section('content')
@php
    // Items already added (coming back after a validation error). Photos have to be picked again.
    $oldItems = array_values(old('items', []));
    $categoryNames = $categories->pluck('name', 'id');
    $unitNames = $units->pluck('name', 'id');
@endphp

<style>
#itemsList td, #itemsList th { vertical-align: middle; }
#itemsList tr.just-added { animation: niFlash 1.2s ease-out; }
@keyframes niFlash { from { background: rgba(25,135,84,.18); } to { background: transparent; } }
#itemEntry.editing { border-color: #0d6efd !important; box-shadow: 0 0 0 .15rem rgba(13,110,253,.15); }
</style>

<div class="container-fluid py-4">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Purchases</a>
        <a href="{{ route('purchases.create', array_filter(['shop' => $activeShopId])) }}" class="btn btn-outline-primary">
            <i class="bi bi-cart-plus"></i> Order existing products
        </a>
        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-person-plus"></i> Add Supplier
        </button>
    </div>

    <form method="POST" action="{{ route('purchases.store_new_product') }}" enctype="multipart/form-data" id="newItemsForm" novalidate>
        @csrf

        {{-- 1. Order details --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>New Item Purchase</h5>
                <small class="text-muted">
                    For products that are not in the system yet. Each item is created in the shop, assigned to the supplier
                    (Supplier Products) at its buying price, and ordered. Stock is added when the order is received.
                </small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="niShop" class="form-label">Shop / Store <span class="text-danger">*</span></label>
                        <select name="shop_id" id="niShop" class="form-select" required>
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ (string) old('shop_id', $activeShopId) === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="niSupplier" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="niSupplier" class="form-select" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="niInvoice" class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" id="niInvoice" class="form-control" maxlength="100"
                               value="{{ old('invoice_number') }}" placeholder="Supplier's invoice number (optional)">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Item entry: fill, click Add → goes to the list below, form clears for the next one.
             These fields have no name: only the list below is submitted. --}}
        <div class="card shadow-sm mb-4 border" id="itemEntry">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0" id="entryTitle"><i class="bi bi-box-seam me-2"></i>Item details</h6>
                <small class="text-muted" id="supplierNote"></small>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-lg-4 col-md-6">
                        <label for="eName" class="form-label small mb-1">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="eName" class="form-control" maxlength="255" placeholder="e.g. Nivea Body Lotion 400ml">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="eCategory" class="form-label small mb-1">Category</label>
                        <select id="eCategory" class="form-select">
                            <option value="">—</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="eUnit" class="form-label small mb-1">Unit <span class="text-danger">*</span></label>
                        <select id="eUnit" class="form-select">
                            <option value="">Select</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="eBarcode" class="form-label small mb-1">Barcode</label>
                        <input type="text" id="eBarcode" class="form-control" maxlength="100" placeholder="Scan">
                    </div>
                    <div class="col-lg-2 col-md-4" id="ePhotoBox">
                        <label for="ePhoto" class="form-label small mb-1">Photo</label>
                        <input type="file" id="ePhoto" class="form-control" accept="image/*">
                    </div>

                    <div class="col-lg-2 col-md-3 col-6">
                        <label for="eQty" class="form-label small mb-1">Quantity <span class="text-danger">*</span></label>
                        <input type="number" id="eQty" class="form-control" min="1" step="1" value="1">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label for="eBuy" class="form-label small mb-1">Buying Price <span class="text-danger">*</span></label>
                        <input type="number" id="eBuy" class="form-control" min="0" step="any">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label for="eRetail" class="form-label small mb-1">Retail Price</label>
                        <input type="number" id="eRetail" class="form-control" min="0" step="any">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label for="eWholesale" class="form-label small mb-1">Wholesale Price</label>
                        <input type="number" id="eWholesale" class="form-control" min="0" step="any">
                    </div>
                    <div class="col-lg-4 col-md-12 d-flex align-items-end gap-2">
                        <div class="me-auto">
                            <div class="small text-muted">Line total</div>
                            <div class="fw-bold">Tsh <span id="eTotal">0.00</span></div>
                        </div>
                        <button type="button" class="btn btn-light" id="eCancelEdit" hidden>Cancel edit</button>
                        <button type="button" class="btn btn-primary px-4" id="eAdd"><i class="bi bi-plus-lg me-1"></i><span>Add</span></button>
                    </div>
                </div>
                <div class="text-danger small mt-2" id="eError" role="alert" hidden></div>
            </div>
        </div>

        {{-- 3. Items added --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Items on this order <span class="badge bg-secondary" id="itemCount">0</span></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="itemsList">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width:40px">#</th>
                            <th class="text-start">Product</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th class="text-end">Buy</th>
                            <th class="text-end">Retail</th>
                            <th class="text-end">Wholesale</th>
                            <th class="text-end">Line total</th>
                            <th style="width:110px">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                    <tfoot>
                        <tr id="emptyRow">
                            <td colspan="10" class="text-center text-muted py-4">No items yet. Fill in the item details above and click <strong>Add</strong>.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 4. Payment + save --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="niPayment" class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="niPayment" class="form-select" required>
                            <option value="">Select Payment Type</option>
                            <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit" {{ old('payment_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="niDepositBox" hidden>
                        <label for="niDeposit" class="form-label">Initial Deposit</label>
                        <input type="number" name="amount_paid" id="niDeposit" class="form-control" min="0" step="any" value="{{ old('amount_paid', 0) }}">
                        <small class="text-danger" id="niRemaining">Remaining Credit: 0.00</small>
                    </div>
                    <div class="col-md text-md-end">
                        <div class="text-muted small">Order total</div>
                        <div class="fs-4 fw-bold">Tsh <span id="niTotal">0.00</span></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-success px-4" id="niSubmit"><i class="bi bi-check-lg me-1"></i> Create items &amp; place order</button>
            </div>
        </div>
    </form>
</div>

@include('pages.purchases._add_supplier_modal')
@include('pages.purchases._order_placed_modal')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('newItemsForm');
    const body = document.getElementById('itemsBody');
    const entry = document.getElementById('itemEntry');
    const errorBox = document.getElementById('eError');
    const addBtn = document.getElementById('eAdd');
    const cancelEditBtn = document.getElementById('eCancelEdit');
    const payment = document.getElementById('niPayment');
    const deposit = document.getElementById('niDeposit');
    const supplier = document.getElementById('niSupplier');
    const depositBox = document.getElementById('niDepositBox');
    const $ = id => document.getElementById(id);

    const FIELDS = ['name', 'category_id', 'unit_id', 'barcode', 'quantity', 'purchase_price', 'selling_price', 'wholesale_price'];
    const ENTRY = { name: 'eName', category_id: 'eCategory', unit_id: 'eUnit', barcode: 'eBarcode', quantity: 'eQty',
                    purchase_price: 'eBuy', selling_price: 'eRetail', wholesale_price: 'eWholesale' };

    let nextIndex = 0;
    let editingRow = null; // the list row being edited, while its values are in the form

    const money = n => (Number(n) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const optionText = (selectId, value) => {
        const opt = Array.from($(selectId).options).find(o => o.value === String(value ?? ''));
        return value && opt ? opt.text : '—';
    };
    const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    function readEntry() {
        const item = {};
        FIELDS.forEach(f => { item[f] = $(ENTRY[f]).value.trim(); });
        return item;
    }

    function showError(message, focusId) {
        errorBox.textContent = message;
        errorBox.hidden = false;
        if (focusId) $(focusId).focus();
    }

    function validate(item) {
        if (!item.name) return showError('Enter the product name.', 'eName'), false;
        if (!item.unit_id) return showError('Choose a unit.', 'eUnit'), false;
        if (!(parseInt(item.quantity, 10) >= 1)) return showError('The quantity must be at least 1.', 'eQty'), false;
        if (item.purchase_price === '' || Number(item.purchase_price) < 0) return showError('Enter the buying price.', 'eBuy'), false;
        const duplicate = Array.from(body.querySelectorAll('tr')).some(row =>
            row !== editingRow && row.dataset.name.toLowerCase() === item.name.toLowerCase());
        if (duplicate) return showError('"' + item.name + '" is already on the list. Edit that row instead.', 'eName'), false;
        // One barcode = one product: the same code can't be on two items.
        const sameBarcode = item.barcode && Array.from(body.querySelectorAll('tr')).find(row =>
            row !== editingRow && row.dataset.barcode === item.barcode);
        if (sameBarcode) return showError('Barcode ' + item.barcode + ' is already on "' + sameBarcode.dataset.name + '". Each product needs its own barcode.', 'eBarcode'), false;
        errorBox.hidden = true;
        return true;
    }

    // One list row = hidden inputs (what gets submitted) + what the user sees.
    function buildRow(item, photoInput) {
        const index = nextIndex++;
        const row = document.createElement('tr');
        row.dataset.name = item.name;
        row.dataset.barcode = item.barcode || '';
        row.dataset.index = index;
        const total = (parseInt(item.quantity, 10) || 0) * (Number(item.purchase_price) || 0);

        row.innerHTML = `
            <td class="text-center" data-number></td>
            <td>
                <div class="fw-semibold">${escapeHtml(item.name)}</div>
                <div class="small text-muted">
                    ${item.barcode ? '<i class="bi bi-upc"></i> ' + escapeHtml(item.barcode) : ''}
                    ${photoInput && photoInput.files.length ? '<span class="ms-1"><i class="bi bi-image"></i> photo</span>' : ''}
                </div>
            </td>
            <td class="text-center">${escapeHtml(optionText('eCategory', item.category_id))}</td>
            <td class="text-center">${escapeHtml(optionText('eUnit', item.unit_id))}</td>
            <td class="text-center">${escapeHtml(item.quantity)}</td>
            <td class="text-end">${money(item.purchase_price)}</td>
            <td class="text-end">${item.selling_price !== '' ? money(item.selling_price) : '—'}</td>
            <td class="text-end">${item.wholesale_price !== '' ? money(item.wholesale_price) : '—'}</td>
            <td class="text-end fw-semibold" data-total="${total}">${money(total)}</td>
            <td class="text-center text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-primary" data-edit aria-label="Edit ${escapeHtml(item.name)}"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="Remove ${escapeHtml(item.name)}"><i class="bi bi-trash"></i></button>
            </td>`;

        const hidden = document.createElement('td');
        hidden.hidden = true;
        FIELDS.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${index}][${f}]`;
            input.value = item[f] ?? '';
            input.dataset.field = f;
            hidden.appendChild(input);
        });
        // The chosen photo can't be copied, so the file input itself moves into the row.
        if (photoInput && photoInput.files.length) {
            photoInput.name = `items[${index}][image]`;
            photoInput.removeAttribute('id');
            photoInput.dataset.photo = '1';
            hidden.appendChild(photoInput);
        }
        row.appendChild(hidden);
        return row;
    }

    // A fresh, empty photo picker in the form (the old one went into the list).
    function newPhotoInput() {
        const input = document.createElement('input');
        input.type = 'file';
        input.id = 'ePhoto';
        input.className = 'form-control';
        input.accept = 'image/*';
        $('ePhotoBox').querySelector('input[type=file]')?.remove();
        $('ePhotoBox').appendChild(input);
    }

    function resetEntry(keepUnitAndCategory) {
        const unit = $('eUnit').value, category = $('eCategory').value;
        ['eName', 'eBarcode', 'eBuy', 'eRetail', 'eWholesale'].forEach(id => { $(id).value = ''; });
        $('eQty').value = 1;
        // Next item is usually the same kind: keep unit and category.
        $('eUnit').value = keepUnitAndCategory ? unit : '';
        $('eCategory').value = keepUnitAndCategory ? category : '';
        editingRow = null;
        entry.classList.remove('editing');
        $('entryTitle').innerHTML = '<i class="bi bi-box-seam me-2"></i>Item details';
        addBtn.querySelector('span').textContent = 'Add';
        cancelEditBtn.hidden = true;
        errorBox.hidden = true;
        refresh();
    }

    function addItem() {
        const item = readEntry();
        if (!validate(item)) return;

        const photo = $('ePhoto');
        const row = buildRow(item, photo.files.length ? photo : null);
        if (photo.files.length) newPhotoInput();

        if (editingRow) {
            // Kept its old photo if no new one was chosen.
            const oldPhoto = editingRow.querySelector('input[data-photo]');
            if (oldPhoto && !row.querySelector('input[data-photo]')) {
                oldPhoto.name = `items[${row.dataset.index}][image]`;
                row.lastElementChild.appendChild(oldPhoto);
                row.children[1].querySelector('.small').insertAdjacentHTML('beforeend', '<span class="ms-1"><i class="bi bi-image"></i> photo</span>');
            }
            editingRow.replaceWith(row);
        } else {
            body.appendChild(row);
        }
        row.classList.add('just-added');
        resetEntry(true);
        $('eName').focus();
    }

    function editRow(row) {
        if (editingRow) resetEntry(false);
        row.querySelectorAll('input[data-field]').forEach(input => { $(ENTRY[input.dataset.field]).value = input.value; });
        editingRow = row;
        entry.classList.add('editing');
        $('entryTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Editing: ' + escapeHtml(row.dataset.name)
            + (row.querySelector('input[data-photo]') ? ' <small class="text-muted">(keeps its photo unless you pick a new one)</small>' : '');
        addBtn.querySelector('span').textContent = 'Update';
        cancelEditBtn.hidden = false;
        refresh();
        entry.scrollIntoView({ behavior: 'smooth', block: 'start' });
        $('eName').focus();
    }

    function refresh() {
        const rows = Array.from(body.querySelectorAll('tr'));
        let total = 0;
        rows.forEach((row, n) => {
            row.querySelector('[data-number]').textContent = n + 1;
            total += Number(row.querySelector('[data-total]').dataset.total) || 0;
        });
        $('emptyRow').hidden = rows.length > 0;
        $('itemCount').textContent = rows.length;
        $('niTotal').textContent = money(total);
        $('eTotal').textContent = money((parseInt($('eQty').value, 10) || 0) * (Number($('eBuy').value) || 0));

        depositBox.hidden = payment.value !== 'credit';
        const paid = Math.min(Number(deposit.value) || 0, total);
        $('niRemaining').textContent = 'Remaining Credit: ' + money(Math.max(0, total - paid));
        $('supplierNote').textContent = supplier.value
            ? 'Will be assigned to ' + supplier.options[supplier.selectedIndex].text
            : '';
    }

    addBtn.addEventListener('click', addItem);
    cancelEditBtn.addEventListener('click', () => resetEntry(false));

    // Enter in the item form = Add (not submit the whole order).
    entry.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || e.target.tagName === 'BUTTON') return;
        e.preventDefault();
        if (e.target.id === 'eBarcode') { $('eQty').focus(); return; } // scanner sends Enter after the code
        addItem();
    });
    entry.addEventListener('input', refresh);

    body.addEventListener('click', function (e) {
        const row = e.target.closest('tr');
        if (e.target.closest('[data-edit]')) editRow(row);
        if (e.target.closest('[data-remove]')) {
            if (row === editingRow) resetEntry(false);
            row.remove();
            refresh();
        }
    });

    [payment, deposit, supplier].forEach(el => el.addEventListener('input', refresh));
    payment.addEventListener('change', function () {
        if (payment.value === 'credit' && !deposit.value) deposit.value = 0;
        refresh();
    });

    form.addEventListener('submit', function (e) {
        const missing = !$('niShop').value ? ['niShop', 'Choose the shop.']
            : !supplier.value ? ['niSupplier', 'Choose the supplier.']
            : !body.querySelector('tr') ? ['eName', 'Add at least one item to the list.']
            : !payment.value ? ['niPayment', 'Choose the payment type.']
            : null;
        // Typed an item but didn't click Add? Don't silently drop it.
        const pending = !missing && $('eName').value.trim() !== '' && !editingRow
            ? ['eAdd', 'The item "' + $('eName').value.trim() + '" is not on the list yet. Click Add, or clear it.'] : null;
        const editing = !missing && editingRow ? ['eAdd', 'Finish editing "' + editingRow.dataset.name + '" first: click Update or Cancel edit.'] : null;
        const problem = missing || pending || editing;
        if (problem) {
            e.preventDefault();
            notify('warning', 'Not ready yet', problem[1]);
            $(problem[0]).focus();
            return;
        }
        const btn = $('niSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    });

    // Back after a validation error: put the items back on the list.
    @json($oldItems).forEach(item => body.appendChild(buildRow(
        Object.fromEntries(FIELDS.map(f => [f, item[f] == null ? '' : String(item[f])])), null)));
    @if(count($oldItems))
        notify('info', 'Photos need to be chosen again', 'Your items are still on the list, but photos are not kept after an error. Use Edit to add them.');
    @endif

    refresh();
});
</script>
@endsection
