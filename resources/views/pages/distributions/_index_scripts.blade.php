<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = @json(route('distributions.productSearch'));
    const store = document.getElementById('distStore');
    const staff = document.getElementById('distStaff');
    const search = document.getElementById('distSearch');
    const results = document.getElementById('distResults');
    const qty = document.getElementById('distQty');
    const addBtn = document.getElementById('distAdd');
    const lines = document.getElementById('distLines');
    const itemsInput = document.getElementById('distItems');
    const submit = document.getElementById('distSubmit');
    let selected = null;
    let items = [];
    let timer = null;

    function syncState() {
        search.disabled = qty.disabled = !store.value;
        addBtn.disabled = !selected;
        submit.disabled = !(store.value && staff.value && items.length);
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

    store.addEventListener('change', function () {
        // Products belong to one store, so changing it clears the list.
        items = [];
        selected = null;
        search.value = '';
        render();
    });
    staff.addEventListener('change', syncState);

    search.addEventListener('input', function () {
        selected = null;
        syncState();
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) { results.classList.remove('is-open'); return; }
        timer = setTimeout(function () {
            fetch(searchUrl + '?' + new URLSearchParams({ q: term, store: store.value }), { headers: { 'Accept': 'application/json' } })
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
