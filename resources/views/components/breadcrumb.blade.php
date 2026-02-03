@php
$shops = $shops ?? collect();

@endphp

<header class="app-header">
 <nav class="cat__top-bar__menu d-flex align-items-center">

    <!-- Dashboard -->
    <a href="{{ url('dashboard') }}" class="cat__menu-item">
        <span class="cat__menu-icon"><i class="icmn-home"></i></span>
        <span class="cat__menu-text">Dashboard</span>
    </a>

    <!-- My Business -->
    <div class="dropdown cat__menu-item">
        <a href="javascript:void(0)" class="dropdown-toggle cat__menu-link"
           data-toggle="dropdown">
            <span class="cat__menu-icon"><i class="icmn-briefcase"></i></span>
            <span class="cat__menu-text">My Business</span>
        </a>
        <div class="dropdown-menu">
           <a class="dropdown-item" href="{{ route('dashboard.shop') }}">
            <i class="icmn-store"></i> My Shop
        </a>
            <a class="dropdown-item" href="{{ url('sale-point') }}">
                <i class="icmn-location"></i> Sale Point
            </a>
        </div>
    </div>

    <!-- Invoice & Order -->
    <div class="dropdown cat__menu-item">
        <a href="javascript:void(0)" class="dropdown-toggle cat__menu-link"
           data-toggle="dropdown">
            <span class="cat__menu-icon"><i class="icmn-file-text"></i></span>
            <span class="cat__menu-text">Invoice & Order</span>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ url('quotation') }}">
                <i class="icmn-file-plus"></i> Quotation
            </a>
            <a class="dropdown-item" href="{{ url('purchase-order') }}">
                <i class="icmn-cart"></i> Purchase Order
            </a>
            <a class="dropdown-item" href="{{ url('suppliers') }}">
                <i class="icmn-truck"></i> My Supplier
            </a>
            <a class="dropdown-item" href="{{ url('customers') }}">
                <i class="icmn-users"></i> Customer
            </a>
        </div>
    </div>

    <!-- User Management -->
    <div class="dropdown cat__menu-item">
        <a href="javascript:void(0)" class="dropdown-toggle cat__menu-link"
           data-toggle="dropdown">
            <span class="cat__menu-icon"><i class="icmn-users"></i></span>
            <span class="cat__menu-text">User Management</span>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('dashboard.staff') }}">
                <i class="icmn-user"></i> My Staff
            </a>
            <a class="dropdown-item" href="{{ route('dashboard.role') }}">
                <i class="icmn-lock"></i> Role & Permission
            </a>
        </div>
    </div>

    <!-- Reports -->
    <div class="dropdown cat__menu-item">
        <a href="javascript:void(0)" class="dropdown-toggle cat__menu-link"
           data-toggle="dropdown">
            <span class="cat__menu-icon"><i class="icmn-stats-bars"></i></span>
            <span class="cat__menu-text">Reports</span>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ url('report/sales') }}">
                <i class="icmn-stats-growth"></i> Sale Report
            </a>
            <a class="dropdown-item" href="{{ url('report/purchase') }}">
                <i class="icmn-cart"></i> Purchase Report
            </a>
            <a class="dropdown-item" href="{{ url('report/invoice') }}">
                <i class="icmn-file-text"></i> Invoice Report
            </a>
            <a class="dropdown-item" href="{{ url('report/profit') }}">
                <i class="icmn-coins"></i> Profit Report
            </a>
            <a class="dropdown-item" href="{{ url('report/stock') }}">
                <i class="icmn-box"></i> Stock Report
            </a>
            <a class="dropdown-item" href="{{ url('stock-list') }}">
                <i class="icmn-list"></i> Stock List
            </a>
        </div>
    </div>

</nav>
</header><br><br><br>


<style>

    /* ===== APP HEADER ===== */

.app-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 64px;              /* important */
    background: #ffffff;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    z-index: 1100;
    margin-top: 60px;
}


/* ===== TOP MENU WRAPPER ===== */
.cat__top-bar__menu {
    gap: 6px;
    padding-left: 30px;
}

/* ===== MENU ITEM ===== */
.cat__menu-item {
    position: relative;
    display: flex;
    align-items: center;
}

/* Menu link */
.cat__menu-link,
.cat__menu-item > a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    border-radius: 10px;
    font-weight: 600;
    color: #2d2d2d;
    transition: all 0.25s ease;
    cursor: pointer;
    text-decoration: none;
     transition: background 0.3s ease, color 0.3s ease, transform 0.2s ease;
}

/* Hover */
.cat__menu-link:hover,
.cat__menu-item > a:hover {
    background: rgba(30, 136, 229, 0.08);
    color: #1e88e5;
        transform: translateY(-1px);
}

/* ===== ICON CIRCLE ===== */
.cat__menu-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f2f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
    font-size: 15px;
}

.cat__menu-link:hover .cat__menu-icon,
.cat__menu-item > a:hover .cat__menu-icon {
    background: #1e88e5;
    color: #fff;
}

/* ===== DIVIDER ===== */
.cat__menu-item::after {
    content: '';
    position: absolute;
    right: -14px;
    height: 22px;
    width: 1px;
    background: rgba(0,0,0,0.08);
}

.cat__menu-item:last-child::after {
    display: none;
}

/* ===== DROPDOWN PANEL ===== */
.dropdown-menu {
    min-width: 230px;
    /* border-radius: 14px; */
    padding: 10px;
    border: none;
    box-shadow: 0 14px 40px rgba(0,0,0,0.12);
    animation: dropdownSmooth 0.25s ease;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    transform-origin: top;
}

/* Dropdown items */
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.2s ease;
}

/* Dropdown icons */
.dropdown-item i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #f2f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

/* Hover dropdown */
.dropdown-item:hover {
    background: rgba(30,136,229,0.12);
    color: #1e88e5;
}

.dropdown-item:hover i {
    background: #1e88e5;
    color: #fff;
}

/* Animation */
@keyframes dropdownSmooth {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


</style>