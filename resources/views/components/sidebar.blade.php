
<!-- TOGGLE BUTTON (Place in your header/navbar) -->
<button id="menu-toggle" class="menu-toggle">
    <i class="bi bi-list"></i>
</button>

<!-- SIDEBAR -->
<aside class="sidenav" id="sidenav-main">

    <!-- SHOP INFO -->
    <div class="shop-info">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Shop Logo">
        <h6>{{ $shop->name ?? 'double h' }}</h6>
    </div>

    <!-- NAVIGATION -->
    <ul class="nav-menu">

                <li class="nav-item {{ request()->routeIs('dashboard.shop.show') ? 'active' : '' }}">
            <a href="{{ isset($shop) ? route('dashboard.shop.show', $shop->id) : '#' }}" data-content="shop-summary">
                <i class="bi bi-speedometer2"></i>
                <span>Summary Report</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('expenses*') ? 'active' : '' }}">
            <a href="#" data-content="expense-section">
                <i class="bi bi-cash"></i>
                <span>Expenses</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('fixed-expenses*') ? 'active' : '' }}">
            <a href="#" data-content="fixed-expense-section">
                <i class="bi bi-wallet2"></i>
                <span>Fixed Expenses</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('purchases*') ? 'active' : '' }}">
            <a href="#" data-content="purchases-section">
                <i class="bi bi-bag"></i>
                <span>Purchases</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('sales*') ? 'active' : '' }}">
            <a href="#" data-content="sale-section">
                <i class="bi bi-cart-check"></i>
                <span>Sales</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('credit*') ? 'active' : '' }}">
            <a href="#" data-content="credit-section">
                <i class="bi bi-cash-stack"></i>
                <span>Credit</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('credit*') ? 'active' : '' }}">
            <a href="#" data-content="order-section">
                <i class="bi bi-cash-stack"></i>
                <span>Orders</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('sales-returns*') ? 'active' : '' }}">
            <a href="#" data-content="sales-return-section">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Sales Return</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('purchases-return*') ? 'active' : '' }}">
            <a href="{{ url('purchases-return') }}">
                <i class="bi bi-arrow-return-left"></i>
                <span>Purchases Return</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('items*') ? 'active' : '' }}">
            <a href="{{ url('items') }}" data-content="product-section">
                <i class="bi bi-box-seam"></i>
                <span>Items</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('trash*') ? 'active' : '' }}">
            <a href="{{ url('deleted_products') }}" data-content="deleted_products-section">
                <i class="bi bi-trash"></i>
                <span>Deleted Products</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('report*') ? 'active' : '' }}">
            <a href="{{ url('report_issue') }}" data-content="report_issue-section">
                <i class="bi bi-box-seam"></i>
                <span>Report_issue</span>
            </a>
        </li>

    </ul>
</aside>

<style>
    /* TOGGLE BUTTON */
.menu-toggle {
    position: fixed;
    top: 15px;
    left: 15px;
    background: #f97316;
    color: #fff;
    border: none;
    padding: 8px 10px;
    border-radius: 6px;
    z-index: 1100;
    display: none;
}

/* SIDEBAR */
.sidenav {
    width: 260px;
    height: calc(100vh - 180px);
    position: fixed;
    left: 0;
    top: 180px; 
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    z-index: 1000;
    overflow-y: auto;
    transition: all 0.3s ease;
}

/* SHOP INFO */
.shop-info {
    text-align: center;
    padding: 25px 15px;
    border-bottom: 1px solid #e5e7eb;
}

.shop-info img {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
}

.shop-info h6 {
    margin-top: 10px;
    font-weight: 600;
}

/* MENU */
.nav-menu {
    list-style: none;
    padding: 15px 10px;
}

.nav-item {
    margin-bottom: 6px;
}

.nav-item a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    text-decoration: none;
    transition: 0.2s;
}

.nav-item i {
    font-size: 16px;
}

/* ACTIVE */
.nav-item.active a {
    background: #fff3e6;
    color: #f97316;
    font-weight: 600;
}

/* HOVER */
.nav-item a:hover {
    background: #f9fafb;
}

/* MAIN CONTENT */
.main-content {
    margin-left: 260px;
    max-width: calc(100% - 260px);
    padding: 20px 16px 40px;
    padding-top: 140px;
    min-height: calc(100vh - 140px);
    box-sizing: border-box;
    overflow-x: hidden;
    transition: all 0.3s ease;
}

/* TABLET */
@media (max-width: 992px) {
    .sidenav {
        width: 220px;
    }

    .main-content {
        margin-left: 220px;
        max-width: calc(100% - 220px);
    }
}

/* MOBILE */
@media (max-width: 768px) {

    .menu-toggle {
        display: block;
    }

    .sidenav {
        left: -260px;
    }

    .sidenav.active {
        left: 0;
    }

    .main-content {
        margin-left: 0;
        max-width: 100%;
        padding-top: 160px;
    }

    .nav-item a {
        font-size: 13px;
        padding: 8px 10px;
    }

    .shop-info img {
        width: 50px;
        height: 50px;
    }
}
</style>