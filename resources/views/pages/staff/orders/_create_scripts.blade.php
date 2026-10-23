<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('orderForm');
    const search = document.getElementById('productSearch');
    const productId = document.getElementById('productId');
    const results = document.getElementById('productResults');
    const qty = document.getElementById('quantity');
    const sellingPrice = document.getElementById('selling_price');
    const saleType = document.getElementById('sale_type');
    const table = document.querySelector('#itemsTable tbody');
    const totalEl = document.getElementById('grandTotal');
    const itemsInput = document.getElementById('itemsInput');
    const addBtn = document.getElementById('addItemBtn');
    const searchUrl = @json(route('staff.orders.productSearch'));

    let items = [];
    let selected = null;
    let timer = null;

    function warn(text) {
        if (window.Swal) Swal.fire({ title: 'Warning!', text: text, icon: 'warning', confirmButtonText: 'OK' });
        else alert(text);
    }

    function applyPrice() {
        if (!selected) return;
        const price = saleType.value === 'wholesale' && Number(selected.wholesale_price) > 0
            ? selected.wholesale_price : selected.selling_price;
        sellingPrice.value = price || '';
    }

    function choose(product) {
        selected = product;
        productId.value = product.id;
        search.value = product.name;
        results.classList.remove('is-open');
        applyPrice();
        qty.focus();
    }

    search.addEventListener('input', function () {
        selected = null;
        productId.value = '';
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) { results.classList.remove('is-open'); return; }

        timer = setTimeout(function () {
            fetch(searchUrl + '?q=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(function (products) {
                    results.replaceChildren();
                    if (!products.length) {
                        const empty = document.createElement('div');
                        empty.className = 'list-group-item text-muted small';
                        empty.textContent = 'No products found';
                        results.appendChild(empty);
                    }
                    products.forEach(function (p) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between';
                        const name = document.createElement('span');
                        name.textContent = p.name;
                        const stock = document.createElement('small');
                        stock.className = Number(p.quantity) > 0 ? 'text-muted' : 'text-danger';
                        stock.textContent = 'In stock: ' + p.quantity;
                        btn.append(name, stock);
                        btn.addEventListener('click', () => choose(p));
                        results.appendChild(btn);
                    });
                    results.classList.add('is-open');
                });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!search.parentElement.contains(e.target)) results.classList.remove('is-open');
    });

    saleType.addEventListener('change', applyPrice);

    addBtn.addEventListener('click', () => {
        if (!selected || !qty.value || !sellingPrice.value) {
            warn('Pick a product from the search results and fill quantity and price.');
            return;
        }

        items.push({
            product_id: selected.id,
            name: selected.name,
            quantity: parseInt(qty.value, 10),
            price: parseFloat(sellingPrice.value),
            sale_type: saleType.value
        });

        renderItems();

        selected = null;
        productId.value = '';
        search.value = '';
        qty.value = '';
        sellingPrice.value = '';
        saleType.value = 'retail';
        search.focus();
    });

    function renderItems() {
        table.replaceChildren();
        let total = 0;
        items.forEach((item, index) => {
            const rowTotal = item.quantity * item.price;
            total += rowTotal;
            const tr = document.createElement('tr');
            [item.name, item.quantity, item.price, rowTotal.toFixed(2), item.sale_type].forEach(value => {
                const td = document.createElement('td');
                td.textContent = value;
                tr.appendChild(td);
            });
            const td = document.createElement('td');
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-danger btn-sm';
            remove.textContent = 'X';
            remove.setAttribute('aria-label', 'Remove ' + item.name);
            remove.addEventListener('click', () => { items.splice(index, 1); renderItems(); });
            td.appendChild(remove);
            tr.appendChild(td);
            table.appendChild(tr);
        });

        totalEl.innerText = total.toFixed(2);
        itemsInput.value = JSON.stringify(items);
    }

    form.addEventListener('submit', (e) => {
        if (items.length === 0) {
            e.preventDefault();
            warn('Add at least one item before submitting!');
        }
    });
});
</script>
