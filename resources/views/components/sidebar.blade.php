<aside class="sidenav" id="sidenav-main">

    <!-- SHOP INFO -->
    <div class="shop-info">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Shop Logo">
        <h6>{{ $shop->name ?? 'double h' }}</h6>
    </div>

    <!-- NAVIGATION -->
    <ul class="nav-menu">

        <li class="nav-item {{ request()->routeIs('shops.show') ? 'active' : '' }}">
            <a href="{{ route('shops.show', $shop->id ?? 1) }}" data-content="shop-summary">
                <i class="bi bi-speedometer2"></i>
                <span>Summary Report</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('expenses*') ? 'active' : '' }}">
            <a href="{{ url('expenses') }}">
                <i class="bi bi-cash"></i>
                <span>Expenses</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('fixed-expenses*') ? 'active' : '' }}">
            <a href="{{ url('fixed-expenses') }}">
                <i class="bi bi-wallet2"></i>
                <span>Fixed Expenses</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('purchases*') ? 'active' : '' }}">
            <a href="{{ url('purchases') }}">
                <i class="bi bi-bag"></i>
                <span>Purchases</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('sales*') ? 'active' : '' }}">
            <a href="{{ url('sales') }}">
                <i class="bi bi-cart-check"></i>
                <span>Sales</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('sales-return*') ? 'active' : '' }}">
            <a href="{{ url('sales-return') }}">
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

        <li class="nav-item {{ request()->is('items*') ? 'active' : '' }}" >
            <a href="{{ url('items') }}" data-content="product-section">
                <i class="bi bi-box-seam"></i>
                <span>Items</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('deleted-products*') ? 'active' : '' }}">
            <a href="{{ url('deleted-products') }}">
                <i class="bi bi-trash"></i>
                <span>Deleted Products</span>
            </a>
        </li>

    </ul>

</aside>



<style>
.sidenav {
    width: 260px;
    height: calc(100vh - 80px);
    position: fixed;
    left: 0;
    top: 180px; /* below header */
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    font-family: 'Poppins', sans-serif;
    z-index: 1000;
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
    margin-bottom: 10px;
}

.shop-info h6 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    color: #111827;
}

/* MENU */
.nav-menu {
    list-style: none;
    padding: 15px 10px;
    margin: 0;
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
    transition: all 0.2s ease;
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

</style>
