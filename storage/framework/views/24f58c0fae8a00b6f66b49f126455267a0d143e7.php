<?php
    $shops = $shops ?? collect();
     $staff = Auth::guard('staff')->user();
?>

<!-- APP HEADER -->
<!-- STAFF HEADER -->
<header class="app-header fixed-top bg-white shadow-sm">
    <nav class="cat__top-bar__menu d-flex align-items-center w-100 px-3 flex-nowrap">

        
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link d-flex align-items-center gap-2"
               data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-file-text"></i></span>
                <span class="cat__menu-text">Invoice & Order</span>
            </a>
            <ul class="dropdown-menu shadow-sm">
                <li><a class="dropdown-item" href="<?php echo e(url('quotation')); ?>"><i class="icmn-file-plus"></i> Quotation</a></li>
                <li><a class="dropdown-item" href="<?php echo e(url('purchase-order')); ?>"><i class="icmn-cart"></i> Purchase Order</a></li>
                <li><a class="dropdown-item" href="<?php echo e(url('suppliers')); ?>"><i class="icmn-truck"></i> Supplier</a></li>
                <li><a class="dropdown-item" href="<?php echo e(url('customers')); ?>"><i class="icmn-users"></i> Customer</a></li>

                
                <?php $staff = Auth::guard('staff')->user(); ?>
                <?php if($staff && $staff->can_wholesale): ?> 
                    <li><a class="dropdown-item" href="<?php echo e(url('wholesale')); ?>"><i class="icmn-basket"></i> Wholesale Sale</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="dropdown cat__menu-item">
            <a href="#" class="dropdown-toggle cat__menu-link d-flex align-items-center gap-2"
               data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat__menu-icon"><i class="icmn-basket"></i></span>
                <span class="cat__menu-text">Wholesale Sale</span>
            </a>
            
        </div>

       <!-- LOGOUT -->
<?php if(Auth::guard('staff')->check()): ?>
<div class="cat__logout ms-auto">
    <form id="logoutForm" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
        <?php echo csrf_field(); ?>
    </form>

    <button type="button" class="btn btn-outline-danger cat__logout-btn" id="logoutBtn" title="Logout">
        <i class="icmn-exit"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutForm = document.getElementById('logoutForm');

    logoutBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                logoutForm.submit();
            }
        });
    });
});
</script>

            </form>
        </div>
        <?php endif; ?>

    </nav>
</header>


<!-- Include Bootstrap JS at the end of body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<br><br><br>

<!-- UNIT MODAL -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?php echo e(route('units.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
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

            <form action="<?php echo e(route('categories.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g Beverages" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <?php if(isset($parentCategories) && count($parentCategories)): ?>
                        <div class="mb-3">
                            <label>Parent Category (optional)</label>
                            <select name="parent_id" class="form-control">
                                <option value="">None</option>
                                <?php $__currentLoopData = $parentCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($parent->id); ?>"><?php echo e($parent->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    min-width: 260px;
    padding: 10px 0;
    border: none;
    border-radius: 12px; 
    background: #fff;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    transform-origin: top;
    margin-top: 10px;
}

/* Dropdown items */
.dropdown-item {
    display: flex !important;        /* force flex layout */
    align-items: center !important;  /* vertical center */
    justify-content: flex-start;     /* left align */
    gap: 12px;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;              /* prevent wrapping */
    line-height: 1;                   /* make icon and text aligned */
}

/* Dropdown icons */
.dropdown-item i {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 50%;
    background: #f2f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    line-height: 1;                  /* vertical align inside circle */
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

/* Optional: prevent icon/text wrapping on small screens */
.dropdown-item > * {
    white-space: nowrap;
}
/* Animation */
@keyframes  dropdownSmooth {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


</style><?php /**PATH E:\PROJECT\double h\double h\resources\views/components/staff_header.blade.php ENDPATH**/ ?>