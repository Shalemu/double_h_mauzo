@section('title', 'Dashboard')
@include('main')
@include('components/staff_header')
@include('components/mainmenu')

@php
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $shopId = auth('staff')->user()->shop_id;
@endphp


<meta name="csrf-token" content="{{ csrf_token() }}">


<div class="cat__content">


<!-- TOP ACTION BUTTONS -->
<div class="row mb-4">
<div class="col-12 d-flex flex-wrap" style="gap:12px;padding-left:20px;">

    <button class="btn btn-outline-danger">
        <i class="bi bi-cart-plus"></i> Summary
    </button>

    <!-- <button class="btn btn-outline-success">
        <i class="bi bi-bag-plus text-success"></i> Purchases
    </button> -->



     <a href="{{ route('staff.expenses.index', auth('staff')->user()->shop_id) }}" class="btn btn-outline-warning">
        <i class="bi bi-cash-stack"></i> Expenses
    </a>


      <a href="{{ route('staff.sales.index', auth('staff')->user()->shop_id) }}" class="btn btn-outline-primary">
        <i class="bi bi-shop"></i> Sales
    </a>

    <a href="{{ route('staff.products.index') }}" class="btn btn-outline-info">
        <i class="bi bi-box-seam "></i> Items
    </a>
    <a href="{{ route('staff.customers.manage') }}" class="btn btn-outline-secondary">
    <i class="bi bi-people"></i> Customers
    </a>
    <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-success">
        <i class="bi bi-cash-stack"></i> Orders
    </a>
    <a href="{{ route('staff.report.issue.index') }}" class="btn btn-outline-danger">
        <i class="bi bi-exclamation-circle"></i> Report Issue
    </a>
</div>

</div>

<div class="container-fluid mt-4">
    <div class="card border shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">My Cart</h5>
                <small class="text-muted">{{ Auth::guard('staff')->user()->shop->name ?? 'My Shop' }}</small>
            </div>
            <h4 class="mb-0 text-primary">Tsh <span id="grand-total">0.00</span></h4>
        </div>

        <div class="card-body">
            <div class="row">

                <!-- PRODUCTS -->
                <div class="col-lg-7">
                   <div class="mb-3">
    <label class="fw-semibold mb-2 text-muted">Sale Type</label>

    <div class="d-flex gap-2 sale-type-group">
        <input type="radio" class="btn-check" name="sale-type" id="retail" value="retail" checked>
        <label class="btn btn-outline-primary flex-fill py-2" for="retail">
            <i class="fa fa-shopping-basket me-1"></i> Retail
        </label>

        <input type="radio" class="btn-check" name="sale-type" id="wholesale" value="wholesale">
        <label class="btn btn-outline-success flex-fill py-2" for="wholesale">
            <i class="fa fa-cubes me-1"></i> Wholesale
        </label>
    </div><br>

                        <input type="text" id="product-search" class="form-control form-control-sm mb-3" placeholder="Search by name, ID or barcode...">
                        <h6 class="mb-3">Sales</h6>

                        @if($products->count())
                            @foreach($products as $product)
                                <div class="card mb-2 product-card"
                                     data-id="{{ $product->id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-barcode="{{ $product->barcode }}"
                                     data-retail-price="{{ $product->selling_price ?? 0 }}"
                                     data-wholesale-price="{{ $product->wholesale_price ?? 0 }}"
                                     data-price="{{ $product->selling_price ?? 0 }}"
                                     data-stock="{{ $product->quantity ?? 0 }}">

                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-2">
                                                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/product-placeholder.png') }}" class="img-fluid rounded">
                                            </div>
                                            <div class="col-4">
                                                <strong>{{ $product->name }}</strong><br>
                                                <small class="text-muted product-price-label">Tsh {{ number_format($product->selling_price ?? 0) }}</small><br>
                                                <small class="text-muted">Stock: {{ $product->quantity ?? 0 }} | Barcode: {{ $product->barcode ?? 'N/A' }}</small>
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
                                
                            @endforeach
                        @else
                            <div class="alert alert-warning text-center">No products available for this shop</div>
                        @endif
                             <div class="d-flex justify-content-end mt-2">
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0" id="product-pagination">
                                            <!-- Pagination buttons will be generated by JS -->
                                        </ul>
                                    </nav>
                                </div>
                    </div>
                    
                </div>

                <!-- CART -->
                <div class="col-lg-5">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-3">Cart</h6>
                        <table class="table table-bordered align-middle table-sm">
                        <thead class="table-warning">
                            <tr>
                                <th>Product</th>
                                <th width="120">Qty</th>
                                <th>Price</th>
                                <th width="90">Discount</th>
                                <th>Total</th>
                                <th width="50"></th>
                            </tr>
                        </thead>

                        <tbody id="cart-items">
                            <tr class="text-muted empty-cart">
                                <td colspan="6" class="text-center">Your cart is empty</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-end mt-3 fs-5 fw-bold">
                        Total: Tsh <span id="cart-total">0.00</span>
                    </div>
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
                                @forelse($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @empty
                                    <option value="">No customers yet</option>
                                @endforelse
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
                                <input type="date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" id="sale-date">
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
            @if($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success m-3">{{ session('success') }}</div>
            @endif
            <form action="{{ route('staff.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
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

<div id="js-data" data-show-customer-modal="{{ $errors->any() || session('success') ? '1' : '0' }}"></div>

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
$(document).ready(function() {
    // --- PRODUCT FILTER & PAGINATION ---
    const itemsPerPage = 5;
    let currentPage = 1;

    // Show retail or wholesale price on every product card, based on the selected tab.
    // The product itself never changes - only which price is displayed/used for the cart.
    function applySaleTypePricing() {
        const selectedType = $('input[name="sale-type"]:checked').val() || 'retail';

        $('.product-card').each(function() {
            const card = $(this);
            const price = selectedType === 'wholesale'
                ? parseFloat(card.data('wholesale-price')) || 0
                : parseFloat(card.data('retail-price')) || 0;

            card.attr('data-price', price);
            card.find('.product-price-label').text('Tsh ' + price.toLocaleString());
        });
    }

    function updateProducts() {
        const query = $('#product-search').val().toLowerCase();

        // Filter products
        const filtered = $('.product-card').filter(function() {
            const name = $(this).data('name') || '';
            const barcode = $(this).data('barcode') || '';

            return name.toLowerCase().includes(query) || barcode.includes(query);
        });

        $('.product-card').hide();

        // Pagination
        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (currentPage > totalPages) currentPage = 1;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        filtered.slice(start, end).show();

        // Pagination UI
        const pagination = $('#product-pagination');
        pagination.empty();

        pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Prev</a>
            </li>
        `);

        for (let i = 1; i <= totalPages; i++) {
            pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `);
    }

    // Initial display
    applySaleTypePricing();
    updateProducts();

    // Search/filter
    $('#product-search, #sale-type-filter').on('keyup change', function() {
        currentPage = 1;
        updateProducts();
    });

    // Switching between Retail / Wholesale updates the displayed price for every product
    $('input[name="sale-type"]').on('change', function() {
        applySaleTypePricing();
    });

    // Pagination click
    $(document).on('click', '#product-pagination .page-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (!isNaN(page)) {
            currentPage = page;
            updateProducts();
        }
    });
});

// --- CART & CHECKOUT ---
document.addEventListener('DOMContentLoaded', () => {
    const cartItemsEl = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-total');
    const subTotalEl = document.getElementById('sub-total');
    const grandTotalEl = document.getElementById('summary-grand-total');
    const billDiscountEl = document.getElementById('bill-discount');
    const shippingEl = document.getElementById('shipping');
    const summaryShippingEl = document.getElementById('summary-shipping');
    const checkoutBtn = document.getElementById('checkout-btn');
    const customerSelect = document.getElementById('customer-id');
    const paymentMethodEl = document.getElementById('payment-method');
    const receivedInput = document.getElementById('received-amount');
    const changeAmountEl = document.getElementById('change-amount');
    const remainingAmountEl = document.getElementById('remaining-amount');
    const receivedRow = document.getElementById('received-row');
    const changeRow = document.getElementById('change-row');
    const remainingRow = document.getElementById('remaining-row');
    const bankRow = document.getElementById('bank-row');
    const mobileRow = document.getElementById('mobile-row');

    let cart = {};
    const stockMap = {};

    // Initialize stock map
    document.querySelectorAll('.product-card').forEach(card => {
        const id = card.dataset.id;
        stockMap[id] = parseInt(card.dataset.stock);
        card.dataset.stockOriginal = card.dataset.stock;
    });

    // --- CART DISPLAY ---
    function updateCartDisplay() {
        if (!cartItemsEl) return;

        cartItemsEl.innerHTML = '';
        let total = 0;
        const cartIds = Object.keys(cart);

        if (cartIds.length === 0) {
            cartItemsEl.innerHTML = '<tr class="text-muted empty-cart"><td colspan="6" class="text-center">Your cart is empty</td></tr>';
        } else {
            cartIds.forEach(id => {
                const item = cart[id];
                const rowTotal = item.qty * item.price - item.discount;
                total += rowTotal;

                const tr = document.createElement('tr');
                tr.dataset.id = id;
                tr.innerHTML = `
                    <td class="fw-semibold">${item.name}</td>
                    <td>
                        <div class="input-group input-group-sm qty-control">
                            <button class="btn btn-outline-secondary cart-minus">−</button>
                            <input type="number" class="form-control text-center cart-qty" value="${item.qty}" min="1">
                            <button class="btn btn-outline-secondary cart-plus">+</button>
                        </div>
                    </td>
                    <td class="text-end">${item.price.toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm cart-discount text-end" value="${item.discount}">
                    </td>
                    <td class="text-end item-total">${rowTotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-danger remove-item"><i class="fa fa-trash"></i></button>
                    </td>
                `;
                cartItemsEl.appendChild(tr);
            });
        }

        const billDiscount = parseFloat(billDiscountEl?.value) || 0;
        const shipping = parseFloat(shippingEl?.value) || 0;

        cartTotalEl && (cartTotalEl.textContent = total.toFixed(2));
        subTotalEl && (subTotalEl.textContent = total.toFixed(2));
        summaryShippingEl && (summaryShippingEl.textContent = shipping.toFixed(2));
        document.getElementById('bill-discount-value') && (document.getElementById('bill-discount-value').textContent = billDiscount.toFixed(2));
        grandTotalEl && (grandTotalEl.textContent = (total - billDiscount + shipping).toFixed(2));

        calculatePaymentEffects();
    }

    // --- ADD TO CART ---
    document.querySelectorAll('.product-card').forEach(card => {
        const qtyInput = card.querySelector('.qty-input');
        const discountInput = card.querySelector('.discount-input');
        const addBtn = card.querySelector('.add-to-cart');
        const productId = card.dataset.id;
        const productName = card.dataset.name;

        function availableStock() { return stockMap[productId] || 0; }

        function toggleAddBtn() {
            const val = parseInt(qtyInput.value) || 0;
            addBtn.classList.toggle('d-none', val <= 0 || val > availableStock());
        }

        card.querySelector('.qty-plus').addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 0;
            if (val < availableStock()) val++;
            qtyInput.value = val;
            toggleAddBtn();
        });

        card.querySelector('.qty-minus').addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 0;
            if (val > 0) val--;
            qtyInput.value = val;
            toggleAddBtn();
        });

        addBtn.addEventListener('click', () => {
            const qty = parseInt(qtyInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const price = parseFloat(card.dataset.price) || 0;
            const saleType = $('input[name="sale-type"]:checked').val() || 'retail';

            if (qty <= 0) {
                Swal.fire({ icon: 'warning', title: 'Invalid Quantity', text: 'Quantity must be at least 1', confirmButtonColor: '#3085d6' });
                return;
            }
            if (qty > availableStock()) {
                Swal.fire({ icon: 'error', title: 'Stock Not Available', text: `Only ${availableStock()} items remaining in stock`, confirmButtonColor: '#d33' });
                return;
            }

            if (cart[productId]) {
                const newQty = cart[productId].qty + qty;
                if (newQty > parseInt(card.dataset.stockOriginal)) {
                    Swal.fire({ icon: 'warning', title: 'Stock Limit Reached', text: 'Not enough stock for total quantity', confirmButtonColor: '#f39c12' });
                    return;
                }
                cart[productId].qty = newQty;
                cart[productId].discount += discount;
                cart[productId].price = price;
                cart[productId].sale_type = saleType;
            } else {
                cart[productId] = { name: productName, qty, price, discount, sale_type: saleType };
            }

            stockMap[productId] -= qty;
            qtyInput.value = 0;
            discountInput.value = 0;
            addBtn.classList.add('d-none');

            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: productName + ' added to cart', showConfirmButton: false, timer: 1500 });

            updateCartDisplay();
        });
    });

    // --- CART ACTIONS (remove/plus/minus/qty-input) ---
    cartItemsEl?.addEventListener('click', e => {
        const row = e.target.closest('tr');
        if (!row) return;
        const id = row.dataset.id;

        if (e.target.closest('.remove-item')) {
            stockMap[id] += cart[id].qty;
            delete cart[id];
            updateCartDisplay();
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Item removed from cart', showConfirmButton: false, timer: 1500 });
        }

        if (e.target.classList.contains('cart-plus')) {
            const originalStock = parseInt(document.querySelector(`.product-card[data-id="${id}"]`).dataset.stockOriginal);
            if (cart[id].qty + 1 > originalStock) {
                Swal.fire({ icon: 'warning', title: 'Stock Limit Reached', text: 'You cannot add more than available stock.', confirmButtonColor: '#3085d6' });
                return;
            }
            cart[id].qty++;
            updateCartDisplay();
        }

        if (e.target.classList.contains('cart-minus')) {
            if (cart[id].qty > 1) cart[id].qty--;
            updateCartDisplay();
        }
    });

    cartItemsEl?.addEventListener('input', e => {
        if (!e.target.classList.contains('cart-qty')) return;
        const row = e.target.closest('tr');
        const id = row.dataset.id;
        const originalStock = parseInt(document.querySelector(`.product-card[data-id="${id}"]`).dataset.stockOriginal);

        let newQty = parseInt(e.target.value) || 1;
        if (newQty > originalStock) {
            Swal.fire({ icon: 'error', title: 'Invalid Quantity', text: `Maximum available stock is ${originalStock}`, confirmButtonColor: '#d33' });
            newQty = originalStock;
        }

        cart[id].qty = newQty;
        updateCartDisplay();
    });

    // --- DISCOUNT & SHIPPING INPUT ---
    billDiscountEl?.addEventListener('input', updateCartDisplay);
    shippingEl?.addEventListener('input', updateCartDisplay);

    // --- PAYMENT CALCULATION ---
    function calculatePaymentEffects() {
        if (!paymentMethodEl || !receivedInput) return;

        const total = parseFloat(grandTotalEl?.textContent) || 0;
        const method = paymentMethodEl.value;
        let received = parseFloat(receivedInput.value) || 0;

        [receivedRow, changeRow, remainingRow, bankRow, mobileRow].forEach(el => el && (el.style.display = 'none'));

        if (method === 'cash') {
            receivedRow && (receivedRow.style.display = '');
            changeRow && (changeRow.style.display = '');
            changeAmountEl && (changeAmountEl.textContent = Math.max(received - total, 0).toFixed(2));
        }

        if (method === 'credit') {
            if (!customerSelect?.value) {
                Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a customer for credit sale', confirmButtonColor: '#3085d6' });
                paymentMethodEl.value = 'cash';
                return calculatePaymentEffects();
            }
            receivedRow && (receivedRow.style.display = '');
            remainingRow && (remainingRow.style.display = '');
            remainingAmountEl && (remainingAmountEl.textContent = Math.max(total - received, 0).toFixed(2));
        }

        if (method === 'mobile') mobileRow && (mobileRow.style.display = '');
        if (method === 'bank') bankRow && (bankRow.style.display = '');
    }

    paymentMethodEl?.addEventListener('change', calculatePaymentEffects);
    receivedInput?.addEventListener('input', calculatePaymentEffects);
    customerSelect?.addEventListener('change', calculatePaymentEffects);

    calculatePaymentEffects();

    // --- CHECKOUT ---
    checkoutBtn?.addEventListener('click', async () => {
        if (Object.keys(cart).length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart is empty', text: 'Please add items to the cart before checkout', confirmButtonColor: '#3085d6' });
            return;
        }

        const paymentMethod = paymentMethodEl?.value || 'cash';
        const paymentType = paymentMethod === 'bank' ? document.getElementById('bank-name')?.value
            : paymentMethod === 'mobile' ? document.getElementById('mobile-name')?.value
            : null;
        const receivedAmount = parseFloat(receivedInput?.value) || 0;

        const payload = {
            cart: Object.entries(cart).map(([productId, item]) => ({
                product_id: parseInt(productId),
                qty: item.qty,
                price: item.price,
                discount: item.discount,
                sale_type: item.sale_type || 'retail'
            })),
            customer_id: customerSelect?.value || null,
            payment_method: paymentMethod,
            payment_type: paymentType,
            received_amount: receivedAmount,
            bill_discount: parseFloat(billDiscountEl?.value) || 0,
            shipping: parseFloat(shippingEl?.value) || 0,
            receipt: false
        };

        try {
            const response = await fetch("{{ route('staff.sales.checkout', ['shop' => $shopId]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                Swal.fire({ icon: 'error', title: 'Checkout Failed', text: data.message || 'Server error during checkout', confirmButtonColor: '#d33' });
                return;
            }

            Swal.fire({ icon: 'success', title: 'Sale Completed', text: data.message || 'Sale submitted successfully!', confirmButtonColor: '#3085d6' });

            cart = {};
            updateCartDisplay();
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Checkout Error', text: 'Checkout failed: ' + err.message, confirmButtonColor: '#d33' });
        }
    });

    updateCartDisplay();
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

@keyframes popIn {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.sale-type-group .btn {
    font-weight: 600;
    border-radius: 8px !important;
}

.sale-type-group .btn-check:checked + .btn {
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.qty-control button{
    width:32px;
}

.cart-qty{
    max-width:60px;
}

#cart-items tr{
    transition:0.2s;
}

#cart-items tr:hover{
    background:#f8f9fa;
}
</style>

