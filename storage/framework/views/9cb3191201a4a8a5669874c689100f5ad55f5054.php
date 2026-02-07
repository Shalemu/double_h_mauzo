<?php $__env->startSection('title', 'Dashboard'); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<?php
    $products = $products ?? collect();
?>

<div class="cat__content">
<br><br>

<!-- TOP ACTION BUTTONS -->
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap" style="gap:12px;padding-left:20px;">
        <button class="btn btn-outline-danger"><i class="bi bi-cart-plus"></i> Summary</button>
        <button class="btn btn-outline-success"><i class="bi bi-bag-plus"></i> Purchases</button>
        <button class="btn btn-outline-warning text-dark"><i class="bi bi-cash-stack"></i> Expenses</button>
        <button class="btn btn-outline-primary"><i class="bi bi-shop"></i> Sales</button>
        <button class="btn btn-outline-secondary"><i class="bi bi-geo-alt"></i> Items</button>
    </div>
</div>

<div class="container-fluid mt-4">
<div class="card border shadow-sm">

<!-- HEADER -->
<div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">My Cart</h5>
            <small class="text-muted">
                <?php echo e(Auth::guard('staff')->user()->shop->name ?? 'My Shop'); ?>

            </small>
        </div>
        <h4 class="mb-0 text-primary">
            Tsh <span id="grand-total">0</span>
        </h4>
    </div>

        <!-- BODY -->
        <div class="card-body">
        <div class="row">

        <!-- LEFT: PRODUCTS -->
        <div class="col-lg-7">
        <div class="border rounded p-2 bg-light h-100">

        <input type="text" id="product-search"
            class="form-control form-control-sm mb-3"
            placeholder="Search by name, ID or barcode...">

        <h6 class="mb-3">Items</h6>

        <?php if($products->count()): ?>

        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card mb-2 product-card"
            data-id="<?php echo e($product->id); ?>"
            data-name="<?php echo e(strtolower($product->name)); ?>"
            data-barcode="<?php echo e($product->barcode); ?>"
            data-price="<?php echo e($product->selling_price ?? 0); ?>">

        <div class="card-body py-2">
        <div class="row align-items-center">

        <!-- IMAGE -->
        <div class="col-2">
            <img src="<?php echo e($product->image
                ? asset('storage/'.$product->image)
                : asset('assets/img/product-placeholder.png')); ?>"
                class="img-fluid rounded">
        </div>

        <!-- NAME -->
        <div class="col-4">
            <strong><?php echo e($product->name); ?></strong><br>
            <small class="text-muted">
                Tsh <?php echo e(number_format($product->selling_price ?? 0)); ?>

            </small><br>
            <small class="text-muted">
                Barcode: <?php echo e($product->barcode ?? 'N/A'); ?>

            </small>
        </div>

        <!-- QTY -->
        <div class="col-3 d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary qty-minus">−</button>
            <input type="number" class="form-control form-control-sm mx-1 qty-input"
                value="0" min="0" style="width:60px;">
            <button class="btn btn-sm btn-outline-secondary qty-plus">+</button>
        </div>

        <!-- DISCOUNT -->
        <div class="col-3">
            <input type="number" class="form-control form-control-sm discount-input"
                placeholder="Discount" value="0">
            <button class="btn btn-sm btn-primary mt-1 w-100 add-to-cart d-none">
                Add to Cart
            </button>
        </div>

        </div>
        </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php else: ?>
        <div class="alert alert-warning text-center">
            No products available for this shop
        </div>
        <?php endif; ?>

        </div>
        </div>

        <!-- RIGHT: CART -->
        <div class="col-lg-5">
        <div class="border rounded p-3 h-100">

        <h6 class="mb-3">Cart</h6>

        <table class="table table-bordered table-sm">
        <thead class="table-warning">
        <tr>
            <th>Name</th>
            <th width="70">Qty</th>
            <th>Price</th>
            <th width="80">Discount</th>
            <th>Total</th>
            <th width="40"></th>
        </tr>
        </thead>

        <tbody id="cart-items">
        <tr class="text-muted empty-cart">
            <td colspan="6" class="text-center">Your cart is empty</td>
        </tr>
        </tbody>

        </table>

        <div class="text-end mt-3">
            <strong>Total: Tsh <span id="cart-total">0</span></strong>
        </div>

    </div>
    </div>

</div>

<hr>

<div class="row mt-3">

<!-- LEFT: CUSTOMER & PAYMENT -->
<div class="col-md-6">
    <h6>Sale Details</h6>

    <div class="mb-2">
        <label class="form-label">Customer</label>
        <select class="form-select form-select-sm">
            <option>Walk-in Customer</option>
            <option>Registered Customer</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Payment Method</label>
        <select class="form-select form-select-sm">
            <option>Cash</option>
            <option>Mobile Money</option>
            <option>Card</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Sale Date</label>
        <input type="date" class="form-control form-control-sm"
               value="<?php echo e(now()->toDateString()); ?>">
    </div>

    <div class="mb-2">
        <label class="form-label">Bill Discount</label>
        <input type="number" class="form-control form-control-sm"
               id="bill-discount" value="0">
    </div>
</div>

<!-- RIGHT: SUMMARY -->
<div class="col-md-6">
    <h6>Cart Summary</h6>

    <table class="table table-sm">
        <tr>
            <td>Payment Sum</td>
            <td class="text-end">Tsh <span id="payment-sum">0.00</span></td>
        </tr>
        <tr>
            <td>Bill Discount</td>
            <td class="text-end">Tsh <span id="bill-discount-value">0.00</span></td>
        </tr>
        <tr>
            <td>Shipping</td>
            <td class="text-end">Tsh <span id="shipping">0.00</span></td>
        </tr>
        <tr>
            <td>Sub Total</td>
            <td class="text-end">Tsh <span id="sub-total">0.00</span></td>
        </tr>
        <tr class="fw-bold">
            <td>Grand Total</td>
            <td class="text-end text-primary">
                Tsh <span id="summary-grand-total">0.00</span>
            </td>
        </tr>
    </table>
</div>

</div>

<!-- ACTION BUTTONS -->
<div class="d-flex justify-content-between mt-3">
    <button class="btn btn-outline-danger" id="clear-cart">
        <i class="fa fa-trash"></i> Clear Cart
    </button>

    <button class="btn btn-success" id="complete-sale">
        <i class="fa fa-check"></i> Complete Sale
    </button>
</div>

</div>
</div>
</div>

<!-- SCRIPTS -->
<script>
let cart = [];

document.querySelectorAll('.product-card').forEach(card => {

const minus = card.querySelector('.qty-minus');
const plus = card.querySelector('.qty-plus');
const qtyInput = card.querySelector('.qty-input');
const discountInput = card.querySelector('.discount-input');
const addBtn = card.querySelector('.add-to-cart');

function toggle() {
    addBtn.classList.toggle('d-none', Number(qtyInput.value) <= 0);
}

minus.onclick = () => { qtyInput.value = Math.max(0, qtyInput.value - 1); toggle(); };
plus.onclick = () => { qtyInput.value = Number(qtyInput.value) + 1; toggle(); };
qtyInput.oninput = toggle;

addBtn.onclick = () => {
    const name = card.dataset.name;
    const price = Number(card.dataset.price);
    const qty = Number(qtyInput.value);
    const discount = Number(discountInput.value) || 0;

    if (!qty) return;

    const item = cart.find(i => i.name === name);
    if (item) {
        item.qty += qty;
        item.discount += discount;
    } else {
        cart.push({ name, price, qty, discount });
    }

    qtyInput.value = 0;
    discountInput.value = 0;
    toggle();
    renderCart();
};
});

function renderCart() {
const tbody = document.getElementById('cart-items');
const totalEl = document.getElementById('cart-total');
const grandEl = document.getElementById('grand-total');

tbody.innerHTML = '';
let total = 0;

cart.forEach(item => {
    const subtotal = (item.qty * item.price) - item.discount;
    total += subtotal;

    tbody.innerHTML += `
        <tr>
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>${item.price}</td>
            <td>${item.discount}</td>
            <td>${subtotal}</td>
        </tr>
    `;
});

if (!cart.length) {
    tbody.innerHTML = `<tr class="text-muted">
        <td colspan="5" class="text-center">Your cart is empty</td>
    </tr>`;
}

totalEl.textContent = total;
grandEl.textContent = total;
}

document.getElementById('product-search').addEventListener('keyup', function () {
const q = this.value.toLowerCase();
document.querySelectorAll('.product-card').forEach(card => {
    const match =
        card.dataset.name.includes(q) ||
        card.dataset.id.includes(q) ||
        (card.dataset.barcode || '').includes(q);
    card.style.display = match ? '' : 'none';
});
});
</script>

<style>
.product-card:hover { background:#f8f9fa; }
.table th, .table td { vertical-align:middle; }
</style>

<?php echo $__env->make('components/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/index.blade.php ENDPATH**/ ?>