<?php
    $shops = $shops ?? collect();
?>

<!-- Header -->
<header class="cat__header">

    <!-- Left: Menu -->
    <nav class="cat__header__menu">
        <ul class="list-unstyled d-flex m-0">
            <li>
                <a href="<?php echo e(url('dashboard')); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(url('profile')); ?>">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(url('settings')); ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Right: User Info -->
    <div class="cat__header__user">
        <div class="text-end me-2">
            <div class="fw-bold">
                <?php echo e(Auth::user()->name ?? 'User'); ?>

            </div>
            <div class="text-muted small">
                <?php echo e(Auth::user()->role->name ?? 'User'); ?>

            </div>
        </div>

        <i class="bi bi-person-circle"></i>
    </div>

</header>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
       /* Sidebar container */
/* Header container */
.cat__header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 64px;
    background-color: #1b1b2f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1000;
}

/* Menu */
.cat__header__menu ul {
    gap: 10px;
}

.cat__header__menu a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.95rem;
}

.cat__header__menu a:hover {
    background-color: rgba(255, 255, 255, 0.12);
}

.cat__header__menu i {
    font-size: 1.2rem;
}

/* User section */
.cat__header__user {
    display: flex;
    align-items: center;
}

.cat__header__user i {
    font-size: 2rem;
    margin-left: 10px;
}

    </style>

</body>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/components/mainmenu.blade.php ENDPATH**/ ?>