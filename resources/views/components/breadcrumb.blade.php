@php
    $shops = $shops ?? collect();
@endphp

<header class="app-header">
    <nav class="cat__top-bar__menu d-flex align-items-center w-100">

        <!-- MY BUSINESS -->
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-briefcase"></i></span>
                <span class="cat__menu-text">My Business</span>
            </a>

            <ul class="dropdown-menu">
                <li>
          <a class="dropdown-item" href="{{ isset($shop) ? route('dashboard.shop.show', $shop->id) : route('dashboard.shop') }}">
    <i class="icmn-store"></i> My Shop
</a>

           </li>
                <li><a class="dropdown-item" href="{{ url('sale-point') }}"><i class="icmn-location"></i> Sale Point</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="icmn-list"></i> Product Categories</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#unitModal"><i class="icmn-meter"></i> Units</a></li>
            </ul>
        </div>

        <!-- INVOICE & ORDER -->
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-file-text"></i></span>
                <span class="cat__menu-text">Invoice & Order</span>
            </a>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ url('quotation') }}"><i class="icmn-file-plus"></i> Quotation</a></li>
                <li><a class="dropdown-item" href="{{ url('purchase-order') }}"><i class="icmn-cart"></i> Purchase Order</a></li>
                <li><a class="dropdown-item" href="{{ url('suppliers') }}"><i class="icmn-truck"></i> My Supplier</a></li>
                <li><a class="dropdown-item" href="{{ url('customers') }}"><i class="icmn-users"></i> Customer</a></li>
            </ul>
        </div>

        <!-- USER MANAGEMENT -->
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-users"></i></span>
                <span class="cat__menu-text">User Management</span>
            </a>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('staff.manage.index') }}"><i class="icmn-user"></i> My Staff</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.role') }}"><i class="icmn-lock"></i> Role & Permission</a></li>
            </ul>
        </div>

        <!-- REPORTS -->
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-stats-bars"></i></span>
                <span class="cat__menu-text">Reports</span>
            </a>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ url('report/sales') }}"><i class="icmn-stats-growth"></i> Sale Report</a></li>
                <li><a class="dropdown-item" href="{{ url('report/purchase') }}"><i class="icmn-cart"></i> Purchase Report</a></li>
                <li><a class="dropdown-item" href="{{ url('report/invoice') }}"><i class="icmn-file-text"></i> Invoice Report</a></li>
                <li><a class="dropdown-item" href="{{ url('report/profit') }}"><i class="icmn-coins"></i> Profit Report</a></li>
                <li><a class="dropdown-item" href="{{ url('report/stock') }}"><i class="icmn-box"></i> Stock Report</a></li>
                <li><a class="dropdown-item" href="{{ url('stock-list') }}"><i class="icmn-list"></i> Stock List</a></li>
            </ul>
        </div>

        <!-- USER PROFILE & LOGOUT -->
        <div class="cat__logout ms-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cat__logout-btn" title="Logout" onclick="return confirm('Are you sure you want to logout?');">
                    <i class="icmn-exit"></i>
                </button>
            </form>
        </div>
    </nav>
</header>

<br><br><br>

<!-- UNIT MODAL -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <label>Unit Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Short Name</label>
                        <input type="text" name="short_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CATEGORY MODAL -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Add Product Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g Beverages" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    @if(isset($parentCategories) && count($parentCategories))
                        <div class="mb-3">
                            <label>Parent Category (optional)</label>
                            <select name="parent_id" class="form-control">
                                <option value="">None</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Optional: Auto-close modal after success -->
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        categoryModal.hide();
        alert("{{ session('success') }}");
    });
</script>
@endif





<style>

    /* ===== APP HEADER ===== */
.app-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100px;
    background: #ffffff;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    z-index: 1100;
    margin-top: 60px;
}

.cat__top-bar__menu {
    gap: 6px;
    padding-left: 30px;
}

.cat__menu-item {
    position: relative;
    display: flex;
    align-items: center;
    margin-top: 20px;
}

.cat__menu-item > a,
.cat__menu-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    border-radius: 10px;
    font-weight: 600;
    color: #2d2d2d;
    text-decoration: none;
    transition: all 0.25s ease;
}

.cat__menu-item > a:hover,
.cat__menu-link:hover {
    background: rgba(30,136,229,0.08);
    color: #1e88e5;
}

.cat__menu-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f2f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cat__menu-item > a:hover .cat__menu-icon,
.cat__menu-link:hover .cat__menu-icon {
    background: #1e88e5;
    color: #fff;
}

/* USER MENU */
.cat__user-menu {
    margin-left: auto;
    padding-right: 25px;
}

.cat__user-toggle {
    padding: 6px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: #2d2d2d;
}

.cat__user-toggle:hover {
    background: rgba(30,136,229,0.08);
}

.cat__user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #1e88e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cat__user-menu .dropdown-toggle::after {
    display: none;
}

.dropdown-menu {
    border-radius: 14px;
    padding: 10px;
    border: none;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Logout container aligned right */
.cat__logout {
    margin-left: auto;
    padding-right: 15px;
}

/* Logout button styling */
.cat__logout-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #f2f4f8;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #e53935;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.cat__logout-btn:hover {
    background: #e53935;
    color: #fff;
    transform: scale(1.05);
}

.cat__logout-btn:focus {
    outline: none;
}

/* Responsive: adjust on smaller screens */
@media (max-width: 768px) {
    .cat__logout {
        padding-right: 10px;
    }

    .cat__logout-btn {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .cat__logout {
        padding-right: 5px;
    }

    .cat__logout-btn {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
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
    border-radius: 1px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    transform-origin: top;
    margin-top: 30px;
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
    margin-top: 20px;
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