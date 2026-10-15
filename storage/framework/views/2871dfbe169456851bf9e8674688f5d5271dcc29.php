<?php $__env->startSection('title', 'Request Stock'); ?>

<?php $__env->startSection('content'); ?>

<style>
.order-product-search { position:relative; }
.order-product-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1050; max-height:320px; overflow:auto; display:none; }
.order-product-results.is-open { display:block; }
</style>

<div class="container-fluid mt-4">
    <section class="card mb-5 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Request Stock</h5>
            <small class="text-muted">Admin is notified as soon as you submit, and you'll get a notification when it's approved or rejected.</small>
        </div>

        <div class="card-body">
            <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
            <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

            <form method="POST" action="<?php echo e(route('staff.orders.store')); ?>" id="orderForm">
                <?php echo csrf_field(); ?>

                <!-- Shop -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Shop</label>
                        <input type="text" class="form-control"
                            value="<?php echo e(auth()->guard('staff')->user()->shop->name ?? 'N/A'); ?>" disabled>
                    </div>
                </div>

                <hr>
                <h6>Add Items</h6>

                <!-- Add Item Row -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4 order-product-search">
                        <input type="search" id="productSearch" class="form-control" autocomplete="off"
                               placeholder="Search product by name or code..." aria-label="Search product">
                        <input type="hidden" id="productId">
                        <div class="list-group shadow order-product-results" id="productResults" role="listbox"></div>
                    </div>

                    <div class="col-md-2">
                        <input type="number" id="quantity" class="form-control" placeholder="Qty" min="1" aria-label="Quantity">
                    </div>

                    <div class="col-md-3">
                        <input type="number" id="selling_price" class="form-control" placeholder="Selling Price" min="0" aria-label="Selling price">
                    </div>

                    <div class="col-md-2">
                        <select id="sale_type" class="form-select" aria-label="Sale type">
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="button" id="addItemBtn" class="btn btn-primary w-100">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Selling Price</th>
                                <th>Total</th>
                                <th>Sale Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Total -->
                <h5 class="text-end mt-3">
                    Total: Tsh <span id="grandTotal">0.00</span>
                </h5>

                <div class="mb-3">
                    <label class="form-label">Note to admin (optional)</label>
                    <textarea name="note" class="form-control" rows="2" maxlength="1000"
                              placeholder="e.g. customers keep asking for this, stock finishes Friday"><?php echo e(old('note')); ?></textarea>
                </div>

                <!-- Hidden input -->
                <input type="hidden" name="items" id="itemsInput">

                <div class="text-end mt-4">
                    <button class="btn btn-success">Submit Order</button>
                </div>
            </form>
        </div>
    </section>
</div>

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
    const searchUrl = <?php echo json_encode(route('staff.orders.productSearch'), 15, 512) ?>;

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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/staff/orders/create.blade.php ENDPATH**/ ?>