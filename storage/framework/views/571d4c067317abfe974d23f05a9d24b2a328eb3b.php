<script>
document.addEventListener('DOMContentLoaded', function () {
    const lookupUrl = <?php echo json_encode(route('procurement.supplierProducts.lookup'), 15, 512) ?>;
    const priceManagerUrl = <?php echo json_encode(route('procurement.prices'), 15, 512) ?>;
    const panel = document.getElementById('assignPanel');
    const toggle = document.getElementById('toggleAssign');
    const supplier = document.getElementById('assignSupplier');
    const search = document.getElementById('assignSearch');
    const shopSelect = document.getElementById('assignShop');
    const productSelect = document.getElementById('assignProduct');
    const productId = document.getElementById('assignProductId');
    const details = document.getElementById('assignDetails');
    const newPrice = document.getElementById('assignNewPrice');
    const canPrice = <?php echo json_encode($canPrice, 15, 512) ?>;
    const pmLink = document.getElementById('assignPriceManager');
    const submit = document.getElementById('assignSubmit');
    const summary = document.getElementById('assignSummary');
    const money = n => (n === null || n === undefined || n === '') ? '—' : Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let selected = null;
    const shopLimit = <?php echo json_encode(\App\Http\Controllers\ProcurementController::SHOP_PRODUCT_LIMIT, 15, 512) ?>;
    let shopProducts = [];  // the selected shop's products as first loaded
    let products = [];      // what the dropdown currently lists (shop list, or server search results)
    let truncated = false;  // shop has more products than one load lists
    let loadId = 0;
    let searchTimer = null;

    function open(show) {
        panel.hidden = !show;
        toggle.setAttribute('aria-expanded', show ? 'true' : 'false');
        if (show) (supplier.value ? (shopSelect.value ? productSelect : shopSelect) : supplier).focus();
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
        if (p) {
            if (productSelect.value !== String(p.id)) productSelect.value = String(p.id);
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

    // Fill the product dropdown from the loaded list, narrowed by the filter box.
    // A shop too big to list in full is searched on the server instead.
    function render() {
        const term = search.value.trim().toLowerCase();
        const shown = term && !truncated ? products.filter(p => p.name.toLowerCase().includes(term) || (p.barcode && p.barcode === search.value.trim())) : products;
        const first = new Option(!products.length ? (term ? 'No products match' : 'No products in this shop')
            : truncated && !term ? 'Select product (first ' + products.length.toLocaleString() + ' shown, type below to search all)'
            : shown.length ? 'Select product (' + shown.length + ')' : 'No products match', '');
        productSelect.replaceChildren(first, ...shown.map(function (p) {
            const label = p.name + (p.unit ? ' (' + p.unit + ')' : '') + ' · ' + Number(p.quantity || 0).toLocaleString() + ' in stock'
                + (Number(p.assigned) ? ' ✓ already assigned' : '');
            return new Option(label, p.id);
        }));
        productSelect.disabled = !products.length && !term;
        if (selected && shown.some(p => p.id === selected.id)) productSelect.value = String(selected.id);
        // A scanned barcode (or a filter leaving one product) picks it straight away.
        if (term && shown.length === 1 && (!selected || selected.id !== shown[0].id)) pick(shown[0]);
    }

    // All products of the chosen shop; flags the ones this supplier already has.
    function fetchProducts(term) {
        const params = { shop: shopSelect.value, supplier: supplier.value };
        if (term) params.q = term;
        return fetch(lookupUrl + '?' + new URLSearchParams(params), { headers: { 'Accept': 'application/json' } }).then(r => r.json());
    }

    function loadProducts(selectId) {
        const id = ++loadId;
        products = shopProducts = [];
        truncated = false;
        pick(null);
        if (!shopSelect.value) {
            productSelect.replaceChildren(new Option('Select a shop first', ''));
            productSelect.disabled = true;
            return Promise.resolve();
        }
        productSelect.replaceChildren(new Option('Loading products...', ''));
        productSelect.disabled = true;
        return fetchProducts()
            .then(function (list) {
                if (id !== loadId) return; // a newer shop was picked meanwhile
                products = shopProducts = list;
                truncated = list.length >= shopLimit;
                search.value.trim().length >= 2 && truncated ? searchServer() : render();
                const keep = selectId && products.find(p => String(p.id) === String(selectId));
                if (keep) pick(keep);
            });
    }

    shopSelect.addEventListener('change', () => { search.value = ''; loadProducts(); });
    // Reload so the "already assigned" marks match the supplier.
    supplier.addEventListener('change', () => loadProducts(productSelect.value));
    function searchServer() {
        const term = search.value.trim();
        const id = ++loadId;
        fetchProducts(term).then(function (list) {
            if (id !== loadId || search.value.trim() !== term) return;
            products = list;
            render();
        });
    }

    search.addEventListener('input', function () {
        if (!truncated) return render();
        clearTimeout(searchTimer);
        if (search.value.trim().length < 2) {
            products = shopProducts;
            return render();
        }
        searchTimer = setTimeout(searchServer, 250);
    });
    productSelect.addEventListener('change', function () {
        pick(products.find(p => String(p.id) === productSelect.value) || null);
    });

    // Edit = reopen the panel for that supplier/product with its supplier price as the new price.
    document.querySelectorAll('.js-edit-assignment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const product = JSON.parse(btn.dataset.product);
            supplier.value = btn.dataset.supplier;
            shopSelect.value = product.shop_id || '';
            search.value = '';
            open(true);
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            loadProducts(product.id).then(function () {
                if (!selected) pick(product); // product's shop missing: still edit it
                newPrice.value = btn.dataset.price;
                sync();
                newPrice.focus();
                newPrice.select();
            });
        });
    });

    // Opening the page (or coming back after a validation error) loads the shop's list.
    loadProducts(productId.value);
    sync();
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/procurement/_supplier_products_scripts.blade.php ENDPATH**/ ?>