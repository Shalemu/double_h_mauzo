<script>
document.addEventListener("DOMContentLoaded", function(){

    function showLoader(){
        const loader = document.getElementById('global-loader');
        if(loader){
            loader.classList.remove('is-done');
            loader.classList.add('is-active');
        }
    }

    function hideLoader(){
        const loader = document.getElementById('global-loader');
        if(loader){
            loader.classList.remove('is-active');
            loader.classList.add('is-done');
            setTimeout(() => loader.classList.remove('is-done'), 250);
        }
    }

    /* SHOW SPINNER ON PAGE NAVIGATION */
    document.querySelectorAll('a').forEach(link=>{
        link.addEventListener('click',function(){
            let href = this.getAttribute('href');

            if(href && !href.startsWith('#') && !href.startsWith('javascript')){
                showLoader();
            }
        });
    });

    /* SHOW SPINNER ON FORM SUBMIT */
    document.querySelectorAll('form').forEach(form=>{
        form.addEventListener('submit',function(){
            showLoader();
        });
    });

    /* AJAX SUPPORT (jQuery) */
    if(window.jQuery){
        $(document).ajaxStart(function(){
            showLoader();
        });

        $(document).ajaxStop(function(){
            hideLoader();
        });
    }

});

</script>

<script>
function initPurchaseForm() {
    const formContainer = document.getElementById('add-purchase-container');
    if (!formContainer) return;

    let items = [];

    const product = formContainer.querySelector('#product');
    const qty = formContainer.querySelector('#quantity');
    const buy = formContainer.querySelector('#purchase_price');
    const retail = formContainer.querySelector('#selling_price');
    const wholesale = formContainer.querySelector('#wholesale_price');
    const table = formContainer.querySelector('#itemsTable tbody');
    const totalEl = formContainer.querySelector('#grandTotal');
    const itemsInput = formContainer.querySelector('#itemsInput');
    const paymentType = formContainer.querySelector('#paymentType');
    const amountBox = formContainer.querySelector('#amountPaidBox');
    const paidInput = formContainer.querySelector('#amountPaid');
    const remainingEl = formContainer.querySelector('#remainingCredit');
    const remainingInput = formContainer.querySelector('#remainingCreditInput');
    const addBtn = formContainer.querySelector('#addItemBtn');

    if (!product || !addBtn) return;

    // Choosing a supplier limits the product list to the products assigned
    // to that supplier (Supplier Products) in the chosen shop, priced at the
    // supplier's price. "Show all products" brings back the full list.
    const supplierSelect = formContainer.querySelector('#purchaseSupplier');
    const shopSelect = formContainer.querySelector('#purchaseShop');
    const hint = formContainer.querySelector('#supplierProductsHint');
    const allOptions = Array.from(product.options).map(o => o.cloneNode(true));
    const supplierProductsPage = @json(route('procurement.supplierProducts'));

    function showAllProducts() {
        product.replaceChildren(...allOptions.map(o => o.cloneNode(true)));
    }

    function loadSupplierProducts() {
        if (!supplierSelect || !hint) return;
        product.value = '';
        buy.value = retail.value = wholesale.value = '';
        if (!supplierSelect.value) {
            showAllProducts();
            hint.replaceChildren();
            return;
        }
        const supplierName = supplierSelect.options[supplierSelect.selectedIndex].text;
        const url = supplierSelect.dataset.productsUrl.replace('__ID__', supplierSelect.value)
            + (shopSelect && shopSelect.value ? '?shop=' + encodeURIComponent(shopSelect.value) : '');
        hint.textContent = 'Loading products for ' + supplierName + '...';
        hint.className = 'small mt-2 text-muted';

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(function (list) {
                product.replaceChildren(new Option(list.length ? 'Select Product' : 'No products assigned', ''));
                list.forEach(function (p) {
                    const opt = new Option(p.name + (p.unit ? ' (' + p.unit + ')' : ''), p.id);
                    // Supplier's price first; fall back to the product's purchasing price.
                    opt.dataset.buy = p.supplier_price ?? p.purchase_price ?? '';
                    opt.dataset.retail = p.selling_price ?? '';
                    opt.dataset.wholesale = p.wholesale_price ?? '';
                    product.add(opt);
                });

                hint.replaceChildren();
                const text = document.createElement('span');
                const link = document.createElement('a');
                link.href = supplierProductsPage + '?' + new URLSearchParams({ supplier: supplierSelect.value });
                link.target = '_blank';
                link.rel = 'noopener';
                if (list.length) {
                    hint.className = 'small mt-2 text-success';
                    text.textContent = 'Showing the ' + list.length + ' product(s) assigned to ' + supplierName + ', at the supplier\'s price. ';
                    link.textContent = 'Manage assignments';
                } else {
                    hint.className = 'small mt-2 text-warning';
                    text.textContent = 'No products are assigned to ' + supplierName
                        + (shopSelect && shopSelect.value ? ' in this shop' : '') + '. ';
                    link.textContent = 'Assign products';
                }
                const all = document.createElement('a');
                all.href = '#';
                all.className = 'ms-2';
                all.textContent = 'Show all products';
                all.addEventListener('click', function (e) {
                    e.preventDefault();
                    showAllProducts();
                    hint.className = 'small mt-2 text-muted';
                    hint.textContent = 'Showing all products.';
                });
                hint.append(text, link, all);
            })
            .catch(function () {
                showAllProducts();
                hint.className = 'small mt-2 text-danger';
                hint.textContent = 'Could not load the supplier\'s products — showing all products.';
            });
    }

    supplierSelect?.addEventListener('change', loadSupplierProducts);
    shopSelect?.addEventListener('change', loadSupplierProducts);

    product.addEventListener('change', () => {
        const opt = product.options[product.selectedIndex];
        buy.value = opt.getAttribute('data-buy') || '';
        retail.value = opt.getAttribute('data-retail') || '';
        wholesale.value = opt.getAttribute('data-wholesale') || '';
    });

    // --- Barcode: scan or type + Enter → find the product in the chosen shop and fill the row ---
    const barcodeInput = formContainer.querySelector('#purchaseBarcode');
    const barcodeInfo = formContainer.querySelector('#purchaseBarcodeInfo');
    const escHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    function setBarcodeInfo(html, tone) {
        if (!barcodeInfo) return;
        barcodeInfo.className = 'small ' + (tone || '');
        barcodeInfo.innerHTML = html;
    }

    let lastLookedUp = '';   // don't look the same code up twice in a row
    let lookupTimer = null;

    function lookupBarcode(force) {
        clearTimeout(lookupTimer);
        const code = (barcodeInput?.value || '').trim();
        if (!code) return;
        if (!force && code === lastLookedUp) return;
        lastLookedUp = code;
        if (!shopSelect || !shopSelect.value) {
            lastLookedUp = ''; // try again once a shop is chosen
            setBarcodeInfo('<i class="bi bi-exclamation-triangle"></i> Choose the shop first, then scan.', 'text-warning-emphasis');
            shopSelect?.focus();
            return;
        }
        setBarcodeInfo('<span class="spinner-border spinner-border-sm"></span> Looking up ' + escHtml(code) + '...', 'text-muted');

        const params = new URLSearchParams({ code: code, shop: shopSelect.value });
        if (supplierSelect && supplierSelect.value) params.set('supplier', supplierSelect.value);

        fetch(barcodeInput.dataset.lookupUrl + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    setBarcodeInfo('<i class="bi bi-x-circle"></i> ' + escHtml(data.message || 'Product not found.'), 'text-danger');
                    barcodeInput.select();
                    return;
                }
                const p = data;
                // The supplier's price when the product is assigned to them; otherwise the product's buying price.
                const buyPrice = p.supplier_price ?? p.purchase_price;

                // Make sure the product is in the list (it may be filtered to the supplier's products), then pick it.
                let opt = Array.from(product.options).find(o => o.value === String(p.id));
                if (!opt) {
                    opt = new Option(p.name + (p.unit ? ' (' + p.unit + ')' : ''), p.id);
                    product.add(opt);
                }
                opt.dataset.buy = buyPrice;
                opt.dataset.retail = p.selling_price;
                opt.dataset.wholesale = p.wholesale_price;
                product.value = String(p.id);
                buy.value = buyPrice;
                retail.value = p.selling_price || '';
                wholesale.value = p.wholesale_price || '';
                qty.value = qty.value || 1;
                updateEntryLineTotal();

                // Found: the row is filled in; no details line (only problems get a message).
                setBarcodeInfo('', '');
                qty.focus();
                qty.select();
            })
            .catch(() => setBarcodeInfo('<i class="bi bi-wifi-off"></i> Could not look up the barcode. Check your connection and try again.', 'text-danger'));
    }

    // Enter (most scanners send it) or 🔍 look up straight away; typing looks up once you
    // stop for a moment (scanners without Enter, typing by hand); leaving the box does too.
    barcodeInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); lookupBarcode(true); }
    });
    barcodeInput?.addEventListener('input', () => {
        clearTimeout(lookupTimer);
        const code = barcodeInput.value.trim();
        if (code === '') { lastLookedUp = ''; setBarcodeInfo('', ''); return; }
        if (code.length >= 3) lookupTimer = setTimeout(() => lookupBarcode(false), 600);
    });
    barcodeInput?.addEventListener('change', () => lookupBarcode(false));
    formContainer.querySelector('#purchaseBarcodeFind')?.addEventListener('click', () => lookupBarcode(true));

    // Enter in the item fields adds the item (instead of submitting the whole order).
    [qty, buy, retail, wholesale].forEach(el => el.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); }
    }));

    addBtn.addEventListener('click', () => {
        if (!product.value || !qty.value || !buy.value) {
            Swal.fire('Error', 'Please fill all item fields.', 'error');
            return;
        }

        items.push({
            product_id: product.value,
            name: product.options[product.selectedIndex].text,
            quantity: parseFloat(qty.value),
            price: parseFloat(buy.value),
            selling_price: retail.value !== '' ? parseFloat(retail.value) : null,
            wholesale_price: wholesale.value !== '' ? parseFloat(wholesale.value) : null
        });

        renderItems();

        product.value = '';
        qty.value = '';
        buy.value = '';
        retail.value = '';
        wholesale.value = '';

        // Ready for the next scan.
        if (barcodeInput) {
            barcodeInput.value = '';
            lastLookedUp = ''; // the same product can be scanned again
            setBarcodeInfo('', '');
            barcodeInput.focus();
        }
    });

    function renderItems() {
        table.innerHTML = '';
        let total = 0;
        const money = n => (n === null || n === undefined || n === '') ? '—'
            : Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        items.forEach((i, index) => {
            const rowTotal = i.quantity * i.price;
            total += rowTotal;

            table.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="fw-semibold">${escHtml(i.name)}</td>
                    <td class="text-center">${escHtml(i.quantity)}</td>
                    <td class="text-end">${money(i.price)}</td>
                    <td class="text-end">${money(i.selling_price)}</td>
                    <td class="text-end">${money(i.wholesale_price)}</td>
                    <td class="text-end fw-semibold">${money(rowTotal)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item" data-index="${index}" aria-label="Remove ${escHtml(i.name)}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        if (!items.length) {
            table.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No items yet. Scan a barcode or pick a product above, then click <strong>Add to order</strong>.</td></tr>';
        }
        const countBadge = formContainer.querySelector('#itemCount');
        if (countBadge) countBadge.textContent = items.length;

        orderTotal = total;
        totalEl.innerText = money(total);
        itemsInput.value = JSON.stringify(items);

        updateRemainingCredit();
    }
    let orderTotal = 0; // the number behind the formatted "Order total"

    table.addEventListener('click', e => {
        const removeBtn = e.target.closest('.remove-item');
        if (removeBtn) {
            items.splice(removeBtn.dataset.index, 1);
            renderItems();
        }
    });

    // Live line total while entering an item.
    const entryLineTotal = formContainer.querySelector('#entryLineTotal');
    function updateEntryLineTotal() {
        if (!entryLineTotal) return;
        const lineTotal = (parseFloat(qty.value) || 0) * (parseFloat(buy.value) || 0);
        entryLineTotal.textContent = lineTotal.toLocaleString(undefined, { maximumFractionDigits: 2 });
    }
    [qty, buy].forEach(el => el.addEventListener('input', updateEntryLineTotal));
    product.addEventListener('change', updateEntryLineTotal);
    addBtn.addEventListener('click', updateEntryLineTotal);

    renderItems(); // shows the "No items yet" row

    function updateRemainingCredit() {
        const total = orderTotal;
        const paid = parseFloat(paidInput?.value || 0);
        const remaining = Math.max(0, total - paid);

        remainingEl.innerText = 'Remaining Credit: ' + remaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (remainingInput) {
            remainingInput.value = remaining.toFixed(2);
        }
    }

    paymentType?.addEventListener('change', () => {
        const total = orderTotal;

        if (paymentType.value === 'credit') {
            amountBox.style.display = 'block';
            paidInput.value = 0;
        } else {
            amountBox.style.display = 'none';
            paidInput.value = total;
        }

        updateRemainingCredit();
    });

    paidInput?.addEventListener('input', updateRemainingCredit);

    formContainer.querySelector('form')?.addEventListener('submit', e => {
        if (items.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Add at least one item.', 'error');
            return;
        }

        itemsInput.value = JSON.stringify(items);
    });
}




</script>


<script>
document.addEventListener('click', function (e) {


    // =========================
    // OPEN RETURN MODAL
    // =========================
const returnBtn = e.target.closest('.return-sale-btn');
if (returnBtn) {

    const modalEl = document.getElementById('returnSaleModal');

    if (!modalEl) {
        console.error('Modal not found');
        return;
    }

    const saleIdEl = document.getElementById('returnSaleId');
    const productIdEl = document.getElementById('returnProductId');
    const productNameEl = document.getElementById('returnProductName');
    const qtyEl = document.getElementById('returnQuantity');
    const amountEl = document.getElementById('returnAmount');
    const typeEl = document.getElementById('returnSaleType');

    if (saleIdEl) saleIdEl.value = returnBtn.dataset.saleId || '';
    if (productIdEl) productIdEl.value = returnBtn.dataset.productId || '';
    if (productNameEl) productNameEl.value = returnBtn.dataset.productName || '';
    if (qtyEl) qtyEl.value = 1;
    if (qtyEl) qtyEl.max = returnBtn.dataset.quantity || 1;
    if (amountEl) amountEl.value = returnBtn.dataset.price || '';
    if (typeEl) typeEl.value = returnBtn.dataset.saleType || 'retail';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    return;
}

});
</script>
