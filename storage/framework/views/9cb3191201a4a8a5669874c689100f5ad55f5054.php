<?php $__env->startSection('title', 'Dashboard'); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/staff_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $shopId = auth('staff')->user()->shop_id;
?>


<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">


<div class="cat__content">


<!-- TOP ACTION BUTTONS -->
<div class="row mb-4">
<div class="col-12 d-flex flex-wrap" style="gap:12px;padding-left:20px;">

    <button class="btn btn-outline-danger">
        <i class="bi bi-cart-plus text-danger"></i> Summary
    </button>

    <button class="btn btn-outline-success">
        <i class="bi bi-bag-plus text-success"></i> Purchases
    </button>



     <a href="<?php echo e(route('staff.expenses.index', auth('staff')->user()->shop_id)); ?>" class="btn btn-outline-warning">
        <i class="bi bi-cash-stack text-warning"></i> Expenses
    </a>


      <a href="<?php echo e(route('staff.sales.index', auth('staff')->user()->shop_id)); ?>" class="btn btn-outline-primary">
        <i class="bi bi-shop text-primary"></i> Sales
    </a>

    <a href="<?php echo e(route('staff.products.index')); ?>" class="btn btn-outline-info">
        <i class="bi bi-box-seam text-info"></i> Items
    </a>
    <a href="<?php echo e(route('staff.customers.manage')); ?>" class="btn btn-outline-secondary">
    <i class="bi bi-people"></i> Customers
    </a>
</div>

</div>

<div class="container-fluid mt-4">
    <div class="card border shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">My Cart</h5>
                <small class="text-muted"><?php echo e(Auth::guard('staff')->user()->shop->name ?? 'My Shop'); ?></small>
            </div>
            <h4 class="mb-0 text-primary">Tsh <span id="grand-total">0.00</span></h4>
        </div>

        <div class="card-body">
            <div class="row">

                <!-- PRODUCTS -->
                <div class="col-lg-7">
                    <div class="border rounded p-2 bg-light h-100">
                        <input type="text" id="product-search" class="form-control form-control-sm mb-3" placeholder="Search by name, ID or barcode...">
                        <h6 class="mb-3">Sales</h6>

                        <?php if($products->count()): ?>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="card mb-2 product-card"
                                     data-id="<?php echo e($product->id); ?>"
                                     data-name="<?php echo e(strtolower($product->name)); ?>"
                                     data-barcode="<?php echo e($product->barcode); ?>"
                                     data-price="<?php echo e($product->selling_price ?? 0); ?>"
                                     data-stock="<?php echo e($product->quantity ?? 0); ?>">
                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-2">
                                                <img src="<?php echo e($product->image ? asset('storage/'.$product->image) : asset('assets/img/product-placeholder.png')); ?>" class="img-fluid rounded">
                                            </div>
                                            <div class="col-4">
                                                <strong><?php echo e($product->name); ?></strong><br>
                                                <small class="text-muted">Tsh <?php echo e(number_format($product->selling_price ?? 0)); ?></small><br>
                                                <small class="text-muted">Stock: <?php echo e($product->quantity ?? 0); ?> | Barcode: <?php echo e($product->barcode ?? 'N/A'); ?></small>
                                            </div>
                                            <div class="col-3 d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary qty-minus">−</button>
                                                <input type="number" class="form-control form-control-sm mx-1 qty-input" value="0" min="0" style="width:60px;">
                                                <button class="btn btn-sm btn-outline-secondary qty-plus">+</button>
                                            </div>
                                            <div class="col-3">
                                                <input type="number" class="form-control form-control-sm discount-input" placeholder="Discount" value="0">
                                                <button class="btn btn-sm btn-primary mt-1 w-100 add-to-cart d-none">Add to Cart</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">No products available for this shop</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CART -->
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
                        <div class="text-end mt-3"><strong>Total: Tsh <span id="cart-total">0.00</span></strong></div>
                    </div>
                </div>

            </div>

            <hr>

            <!-- SALE DETAILS -->
            <div class="row g-3">

                <!-- CUSTOMER & DETAILS -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 fw-bold"><i class="fa fa-info-circle me-1 text-primary"></i> Sale Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Customer</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control form-control-sm me-2" id="customer-id" style="max-width: 85%;">
                                <option value="">-- Select Customer --</option>
                                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <option value="">No customers yet</option>
                                <?php endif; ?>
                            </select>

                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>


                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Payment Method</label>
                             <select class="form-control form-control-sm" id="payment-method">
                            <option value="cash">Cash</option>
                            <option value="mobile">Mobile</option>
                            <option value="bank">Bank</option>
                            <option value="credit">Credit</option>
                        </select>

                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Sale Date</label>
                                <input type="date" class="form-control form-control-sm" value="<?php echo e(now()->toDateString()); ?>" id="sale-date">
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-semibold">Bill Discount (Tsh)</label>
                                <input type="number" class="form-control form-control-sm" id="bill-discount" value="0">
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-semibold">Shipping (Tsh)</label>
                                <input type="number" class="form-control form-control-sm" id="shipping" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CART SUMMARY -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-light py-2 d-flex align-items-center">
                            <i class="fa fa-shopping-cart me-2 text-success"></i>
                            <h6 class="mb-0 fw-bold">Cart Summary</h6>
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-sm mb-0 align-middle">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Payment Sum</td>
                                        <td class="text-end fw-semibold">Tsh <span id="payment-sum">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Bill Discount</td>
                                        <td class="text-end text-danger fw-semibold">− Tsh <span id="bill-discount-value">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Shipping</td>
                                        <td class="text-end fw-semibold">Tsh <span id="summary-shipping">0.00</span></td>
                                    </tr>
                                    <tr><td colspan="2"><hr class="my-2"></td></tr>
                                    <tr>
                                        <td class="fw-semibold">Sub Total</td>
                                        <td class="text-end fw-semibold">Tsh <span id="sub-total">0.00</span></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold fs-6">Grand Total</td>
                                        <td class="text-end fw-bold fs-5 text-primary">Tsh <span id="summary-grand-total">0.00</span></td>
                                    </tr>
                                    <tr id="received-row" style="display:none;">
    <td class="fw-semibold">Received</td>
    <td>
        <input type="number" class="form-control form-control-sm text-end" id="received-amount" value="0">
    </td>
</tr>

<tr id="change-row" style="display:none;">
    <td class="fw-semibold text-success">Change</td>
    <td class="text-end text-success fw-bold">
        Tsh <span id="change-amount">0.00</span>
    </td>
</tr>

<tr id="remaining-row" style="display:none;">
    <td class="fw-semibold text-danger">Remaining Credit</td>
    <td class="text-end text-danger fw-bold">
        Tsh <span id="remaining-amount">0.00</span>
    </td>
</tr>

<tr id="bank-row" style="display:none;">
    <td class="fw-semibold">Select Bank</td>
    <td>
        <select class="form-control form-control-sm" id="bank-name">
            <option value="">-- Select Bank --</option>
            <option value="CRDB">CRDB</option>
            <option value="NMB">NMB</option>
            <option value="NBC">NBC</option>
            <option value="Equity">Equity</option>
        </select>
    </td>
</tr>

<tr id="mobile-row" style="display:none;">
    <td class="fw-semibold">Select Mobile</td>
    <td>
        <select class="form-control form-control-sm" id="mobile-name">
            <option value="">-- Select Mobile --</option>
            <option value="mpesa">M-Pesa</option>
            <option value="mixx">Mixx by Yas</option>
            <option value="airtel">Airtel Money</option>
            <option value="halopesa">HaloPesa</option>
        </select>
    </td>
</tr>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button class="btn btn-outline-danger" id="clear-cart"><i class="fa fa-trash me-1"></i> Clear Cart</button>
                <button class="btn btn-success px-4" id="checkout-btn"><i class="fa fa-check me-1"></i> Complete Sale</button>
            </div>

        </div>
    </div>
</div>

<!-- CUSTOMER MODAL -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" style="margin-top: 200px;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger m-3">
                    <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                </div>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <div class="alert alert-success m-3"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <form action="<?php echo e(route('staff.customers.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="js-data" data-show-customer-modal="<?php echo e($errors->any() || session('success') ? '1' : '0'); ?>"></div>

<!-- SUCCESS MODAL -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">

      <div class="modal-body">
        <!-- Green Check Icon -->
        <div class="mb-3">
          <div class="success-check">
            <i class="fa fa-check"></i>
          </div>
        </div>

        <h4 class="mb-2 text-success">Payment Successful</h4>
        <p class="text-muted mb-4" id="success-message">
          Sale completed successfully!
        </p>

        <button class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
      </div>

    </div>
  </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const cartItemsEl = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-total');
    const subTotalEl = document.getElementById('sub-total');
    const grandTotalEl = document.getElementById('summary-grand-total');
    const billDiscountEl = document.getElementById('bill-discount');
    const shippingEl = document.getElementById('shipping');
    const summaryShippingEl = document.getElementById('summary-shipping');
    const checkoutBtn = document.getElementById('checkout-btn');
    const customerSelect = document.getElementById('customer-id');

    // Cart and stock tracking
    let cart = {};        // Use let instead of const
    const stockMap = {};

    document.querySelectorAll('.product-card').forEach(card => {
        stockMap[card.dataset.id] = parseInt(card.dataset.stock);
        card.dataset.stockOriginal = card.dataset.stock;
    });

    function updateCartDisplay() {
        cartItemsEl.innerHTML = '';
        let total = 0;
        const cartIds = Object.keys(cart);

        if(cartIds.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'text-muted empty-cart';
            tr.innerHTML = '<td colspan="6" class="text-center">Your cart is empty</td>';
            cartItemsEl.appendChild(tr);
        } else {
            cartIds.forEach(id => {
                const item = cart[id];
                const rowTotal = item.qty * item.price - item.discount;
                total += rowTotal;

                const tr = document.createElement('tr');
                tr.dataset.id = id;
                tr.innerHTML = `
                    <td>${item.name}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-end">${item.price.toFixed(2)}</td>
                    <td class="text-end">${item.discount.toFixed(2)}</td>
                    <td class="text-end item-total">${rowTotal.toFixed(2)}</td>
                    <td class="text-center"><button class="btn btn-sm btn-danger remove-item">x</button></td>
                `;
                cartItemsEl.appendChild(tr);
            });
        }

        const billDiscount = parseFloat(billDiscountEl.value) || 0;
        const shipping = parseFloat(shippingEl.value) || 0;

        cartTotalEl.textContent = total.toFixed(2);
        subTotalEl.textContent = total.toFixed(2);
        summaryShippingEl.textContent = shipping.toFixed(2);
        document.getElementById('bill-discount-value').textContent = billDiscount.toFixed(2);
        grandTotalEl.textContent = (total - billDiscount + shipping).toFixed(2);
        calculatePaymentEffects(); 
    }

    document.querySelectorAll('.product-card').forEach(card => {
        const qtyInput = card.querySelector('.qty-input');
        const discountInput = card.querySelector('.discount-input');
        const addBtn = card.querySelector('.add-to-cart');
        const productId = card.dataset.id;
        const productName = card.dataset.name;
        const price = parseFloat(card.dataset.price);

        function availableStock() { return stockMap[productId] || 0; }

        function toggleAddBtn() {
            const val = parseInt(qtyInput.value) || 0;
            addBtn.classList.toggle('d-none', val <= 0 || val > availableStock());
        }

        card.querySelector('.qty-plus').addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 0;
            if(val < availableStock()) val++;
            qtyInput.value = val;
            toggleAddBtn();
        });

        card.querySelector('.qty-minus').addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 0;
            if(val > 0) val--;
            qtyInput.value = val;
            toggleAddBtn();
        });

        addBtn.addEventListener('click', () => {
            const qty = parseInt(qtyInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;

            if(qty <= 0) return alert('Quantity must be at least 1');
            if(qty > availableStock()) return alert(`Not enough stock. Remaining: ${availableStock()}`);

            if(cart[productId]) {
                const newQty = cart[productId].qty + qty;
                if(newQty > parseInt(card.dataset.stockOriginal)) return alert('Not enough stock for total quantity');
                cart[productId].qty = newQty;
                cart[productId].discount += discount;
            } else {
                cart[productId] = {name: productName, qty, price, discount};
            }

            stockMap[productId] -= qty;

            qtyInput.value = 0;
            discountInput.value = 0;
            addBtn.classList.add('d-none');

            updateCartDisplay();
        });
    });

    cartItemsEl.addEventListener('click', e => {
        if(!e.target.classList.contains('remove-item')) return;
        const row = e.target.closest('tr');
        const id = row.dataset.id;
        stockMap[id] += cart[id].qty;
        delete cart[id];
        updateCartDisplay();
    });

    billDiscountEl.addEventListener('input', updateCartDisplay);
    shippingEl.addEventListener('input', updateCartDisplay);

    checkoutBtn.addEventListener('click', async () => {
        if(Object.keys(cart).length === 0) return alert('Cart is empty');

        const paymentMethod = document.getElementById('payment-method')?.value || 'cash';

let paymentType = null;

// Detect payment type
if (paymentMethod === 'bank') {
    paymentType = document.getElementById('bank-name')?.value || null;
}

if (paymentMethod === 'mobile') {
    paymentType = document.getElementById('mobile-name')?.value || null;
}

// Get received amount
const receivedAmount = parseFloat(
    document.getElementById('received-amount')?.value
) || 0;

const payload = {
    cart: Object.entries(cart).map(([productId, item]) => ({
        product_id: parseInt(productId),
        qty: item.qty,
        price: item.price,
        discount: item.discount
    })),

    customer_id: customerSelect?.value || null,

    payment_method: paymentMethod,
    payment_type: paymentType,
    received_amount: receivedAmount ?? 0,

    bill_discount: parseFloat(billDiscountEl.value) || 0,
    shipping: parseFloat(shippingEl.value) || 0,

    receipt: false
};


        try {
            const response = await fetch("<?php echo e(route('staff.sales.checkout', ['shop' => $shopId])); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            // Show database/server error directly
            if (!response.ok || !data.success) {
                return alert(data.message || 'Checkout failed due to server error');
            }

                // Show success modal
                document.getElementById('success-message').textContent =
                    data.message || 'Sale completed successfully!';

                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();

                // Reset cart
                cart = {};
                updateCartDisplay();

                // Optional: reload after modal closes
                document.getElementById('successModal').addEventListener('hidden.bs.modal', () => {
                    window.location.reload();
                });


        } catch (err) {
            alert('Checkout failed: ' + err.message);
        }
    });

    updateCartDisplay();


    function calculatePaymentEffects() {

// PAYMENT LOGIC

const paymentMethod = document.getElementById("payment-method");
const customerSelect = document.getElementById("customer-id");
const receivedInput = document.getElementById("received-amount");

const receivedRow = document.getElementById("received-row");
const changeRow = document.getElementById("change-row");
const remainingRow = document.getElementById("remaining-row");
const bankRow = document.getElementById("bank-row");
const mobileRow = document.getElementById("mobile-row");

const changeAmountEl = document.getElementById("change-amount");
const remainingAmountEl = document.getElementById("remaining-amount");

function getGrandTotal() {
    return parseFloat(document.getElementById("summary-grand-total").textContent) || 0;
}

function resetPaymentRows() {
    receivedRow.style.display = "none";
    changeRow.style.display = "none";
    remainingRow.style.display = "none";
    bankRow.style.display = "none";
    mobileRow.style.display = "none";
}

function calculatePayment() {
    let received = parseFloat(receivedInput.value) || 0;
    let total = getGrandTotal();
    let method = paymentMethod.value;

    if (method === "cash") {
        let change = received - total;
        changeAmountEl.textContent = change > 0 ? change.toFixed(2) : "0.00";
    }

    if (method === "credit") {
        let remaining = total - received;
        remainingAmountEl.textContent = remaining > 0 ? remaining.toFixed(2) : "0.00";
    }
}

function handlePaymentChange() {

    resetPaymentRows();

    let method = paymentMethod.value;

    if (method === "cash") {
        receivedRow.style.display = "";
        changeRow.style.display = "";
    }

    if (method === "mobile") {
        mobileRow.style.display = "";
    }

    if (method === "bank") {
        bankRow.style.display = "";
    }

    if (method === "credit") {

        if (!customerSelect.value) {
            alert("Please select customer for credit sale");
            paymentMethod.value = "cash";
            handlePaymentChange();
            return;
        }

        receivedRow.style.display = "";
        remainingRow.style.display = "";
    }

    calculatePayment();
}

paymentMethod.addEventListener("change", handlePaymentChange);
receivedInput.addEventListener("input", calculatePayment);
customerSelect.addEventListener("change", () => {
    if (paymentMethod.value === "credit") {
        handlePaymentChange();
    }
});

}

});

</script>

<style>
.success-check {
    width: 80px;
    height: 80px;
    background: #28a745;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    animation: popIn 0.4s ease;
}

.success-check i {
    color: #fff;
    font-size: 40px;
}

@keyframes  popIn {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/staff/index.blade.php ENDPATH**/ ?>