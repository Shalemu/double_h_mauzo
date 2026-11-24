<script>
/*
 * Add Product: fill one product → "Add to list" → it goes into the table and the
 * form clears for the next one. Each table row carries hidden inputs
 * products[N][...] (and the chosen photo's own file input), so "Save all
 * products" submits the whole list as a normal form.
 */
document.addEventListener('DOMContentLoaded', function () {
    const $ = id => document.getElementById(id);
    const form = $('addProductForm');
    const body = $('productRows');
    const entry = $('productEntry');
    const errorBox = $('entryError');
    const addBtn = $('addToList');
    const cancelEditBtn = $('cancelEdit');
    const preview = $('imagePreview');

    // products[N][field] ← entry element id
    const ENTRY = {
        name: 'p_name', barcode: 'p_barcode', category_id: 'p_category', brand: 'p_brand', unit_id: 'p_unit',
        size: 'p_size', color: 'p_color', purchase_price: 'p_purchase', selling_price: 'p_selling',
        wholesale_price: 'p_wholesale', quantity: 'p_quantity', min_quantity: 'p_min', expire_date: 'p_expiry',
    };
    const FIELDS = Object.keys(ENTRY);
    const KEEP = ['category_id', 'brand', 'unit_id']; // usually the same for the next product

    let nextIndex = 0;
    let editingRow = null;

    const money = v => v === '' ? '—' : (Number(v) || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
    const optionText = (id, value) => {
        const opt = Array.from($(id).options).find(o => o.value === String(value ?? ''));
        return value && opt ? opt.text : '—';
    };
    const identity = p => ['name', 'brand', 'size', 'color'].map(f => String(p[f] || '').trim().toLowerCase()).join('|');

    function readEntry() {
        const p = {};
        FIELDS.forEach(f => { p[f] = $(ENTRY[f]).value.trim(); });
        return p;
    }

    function fail(message, focusId) {
        errorBox.textContent = message;
        errorBox.hidden = false;
        $(focusId)?.focus();
        return false;
    }

    function validate(p) {
        if (!p.name) return fail('Enter the product name.', 'p_name');
        if (!p.unit_id) return fail('Choose a unit of measure.', 'p_unit');
        if (p.purchase_price === '' || Number(p.purchase_price) < 0) return fail('Enter the retail purchasing price.', 'p_purchase');
        for (const f of ['quantity', 'min_quantity']) {
            if (p[f] !== '' && !/^\d+$/.test(p[f])) return fail('Quantity and alert level must be whole numbers.', ENTRY[f]);
        }
        const others = Array.from(body.querySelectorAll('tr')).filter(r => r !== editingRow);
        const same = others.find(r => r.dataset.identity === identity(p));
        if (same) return fail('"' + p.name + '" is already on the list (same name, brand, size and colour). Edit that row instead.', 'p_name');
        const sameCode = p.barcode && others.find(r => r.dataset.barcode === p.barcode);
        if (sameCode) return fail('Barcode ' + p.barcode + ' is already on "' + sameCode.dataset.name + '". Each product needs its own barcode.', 'p_barcode');
        errorBox.hidden = true;
        return true;
    }

    function buildRow(p, photoInput) {
        const index = nextIndex++;
        const row = document.createElement('tr');
        row.dataset.name = p.name;
        row.dataset.barcode = p.barcode || '';
        row.dataset.identity = identity(p);
        row.dataset.index = index;
        const details = [p.brand, p.size, p.color].filter(Boolean).map(esc).join(' · ');
        const hasPhoto = photoInput && photoInput.files.length;

        row.innerHTML = `
            <td class="text-center" data-number></td>
            <td>
                <div class="fw-semibold">${esc(p.name)}</div>
                <div class="small text-muted" data-meta>
                    ${details}${p.barcode ? ' <span class="ms-1"><i class="bi bi-upc"></i> ' + esc(p.barcode) + '</span>' : ''}
                    ${hasPhoto ? '<span class="ms-1" data-photo-tag><i class="bi bi-image"></i> photo</span>' : ''}
                </div>
            </td>
            <td class="text-center">${esc(optionText('p_category', p.category_id))}</td>
            <td class="text-center">${esc(optionText('p_unit', p.unit_id).split(' (')[0])}</td>
            <td class="text-end">${money(p.purchase_price)}</td>
            <td class="text-end">${money(p.selling_price)}</td>
            <td class="text-end">${money(p.wholesale_price)}</td>
            <td class="text-center">${p.quantity === '' ? '0' : esc(p.quantity)}</td>
            <td class="text-center">${p.min_quantity === '' ? '—' : esc(p.min_quantity)}</td>
            <td class="text-center text-nowrap">${p.expire_date ? esc(p.expire_date) : '—'}</td>
            <td class="text-center text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-primary" data-edit aria-label="Edit ${esc(p.name)}"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="Remove ${esc(p.name)}"><i class="bi bi-trash"></i></button>
            </td>`;

        const hidden = document.createElement('td');
        hidden.hidden = true;
        FIELDS.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `products[${index}][${f}]`;
            input.value = p[f] ?? '';
            input.dataset.field = f;
            hidden.appendChild(input);
        });
        // A chosen file can't be copied, so the file input itself moves into the row.
        if (hasPhoto) attachPhoto(row, hidden, photoInput, index);
        row.appendChild(hidden);
        return row;
    }

    function attachPhoto(row, cell, input, index) {
        input.name = `products[${index}][image]`;
        input.removeAttribute('id');
        input.hidden = true;
        input.dataset.photo = '1';
        cell.appendChild(input);
    }

    function freshPhotoInput() {
        const input = document.createElement('input');
        input.type = 'file';
        input.id = 'p_image';
        input.className = 'form-control';
        input.accept = 'image/*';
        input.addEventListener('change', showPreview);
        $('p_imageBox').querySelector('input[type=file]')?.remove();
        $('p_imageBox').insertBefore(input, preview);
        preview.style.display = 'none';
    }

    function showPreview() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }
    $('p_image').addEventListener('change', showPreview);

    function resetEntry(keepClassification) {
        const kept = {};
        KEEP.forEach(f => { kept[f] = $(ENTRY[f]).value; });
        FIELDS.forEach(f => { $(ENTRY[f]).value = keepClassification && KEEP.includes(f) ? kept[f] : ''; });
        if ($('p_image').files.length) freshPhotoInput();
        preview.style.display = 'none';
        editingRow = null;
        entry.classList.remove('editing');
        $('entryTitle').innerHTML = '<i class="bi bi-box-seam me-1"></i> Product details';
        addBtn.querySelector('span').textContent = 'Add to list';
        cancelEditBtn.hidden = true;
        errorBox.hidden = true;
    }

    function addToList() {
        const p = readEntry();
        if (!validate(p)) return;

        const photo = $('p_image');
        const row = buildRow(p, photo.files.length ? photo : null);
        if (photo.files.length) freshPhotoInput();

        if (editingRow) {
            // No new photo chosen: keep the one the row already had.
            const oldPhoto = editingRow.querySelector('input[data-photo]');
            if (oldPhoto && !row.querySelector('input[data-photo]')) {
                attachPhoto(row, row.lastElementChild, oldPhoto, row.dataset.index);
                row.querySelector('[data-meta]').insertAdjacentHTML('beforeend', '<span class="ms-1" data-photo-tag><i class="bi bi-image"></i> photo</span>');
            }
            editingRow.replaceWith(row);
        } else {
            body.appendChild(row);
        }
        row.classList.add('just-added');
        resetEntry(true);
        refresh();
        $('p_name').focus();
    }

    function editRow(row) {
        if (editingRow) resetEntry(false);
        row.querySelectorAll('input[data-field]').forEach(input => { $(ENTRY[input.dataset.field]).value = input.value; });
        editingRow = row;
        entry.classList.add('editing');
        $('entryTitle').innerHTML = '<i class="bi bi-pencil me-1"></i> Editing: ' + esc(row.dataset.name)
            + (row.querySelector('input[data-photo]') ? ' <small class="text-muted">(keeps its photo unless you choose a new one)</small>' : '');
        addBtn.querySelector('span').textContent = 'Update';
        cancelEditBtn.hidden = false;
        entry.scrollIntoView({ behavior: 'smooth', block: 'start' });
        $('p_name').focus();
    }

    function refresh() {
        const rows = Array.from(body.querySelectorAll('tr'));
        rows.forEach((row, n) => { row.querySelector('[data-number]').textContent = n + 1; });
        $('emptyRow').hidden = rows.length > 0;
        $('productCount').textContent = rows.length;
    }

    addBtn.addEventListener('click', addToList);
    cancelEditBtn.addEventListener('click', () => resetEntry(false));

    // Enter in the product form = Add to list (not save everything).
    entry.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || e.target.tagName === 'BUTTON') return;
        e.preventDefault();
        if (e.target.id === 'p_barcode') { $('p_category').focus(); return; } // scanners send Enter after the code
        addToList();
    });

    body.addEventListener('click', function (e) {
        const row = e.target.closest('tr');
        if (e.target.closest('[data-edit]')) editRow(row);
        if (e.target.closest('[data-remove]')) {
            if (row === editingRow) resetEntry(false);
            row.remove();
            refresh();
        }
    });

    form.addEventListener('submit', function (e) {
        const shop = $('shop_id');
        const problem = shop && !shop.value ? ['shop_id', 'Choose the shop or store.']
            : !body.querySelector('tr') ? ['p_name', 'Add at least one product to the list.']
            : editingRow ? ['addToList', 'Finish editing "' + editingRow.dataset.name + '" first: click Update or Cancel edit.']
            : $('p_name').value.trim() ? ['addToList', '"' + $('p_name').value.trim() + '" is not on the list yet. Click Add to list, or clear it.']
            : null;
        if (problem) {
            e.preventDefault();
            notify('warning', 'Not ready yet', problem[1]);
            $(problem[0])?.focus();
            return;
        }
        const btn = $('saveAll');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    });

    // Back after an error: put the products back on the list (photos must be chosen again).
    (window.addProductOld || []).forEach(item => body.appendChild(buildRow(
        Object.fromEntries(FIELDS.map(f => [f, item[f] == null ? '' : String(item[f])])), null)));
    if ((window.addProductOld || []).length) {
        notify('info', 'Photos need to be chosen again', 'Your products are still on the list, but photos are not kept after an error. Use Edit to add them.');
    }

    refresh();
});
</script>
