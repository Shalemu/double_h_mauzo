<?php
    $isStaff = Auth::guard('staff')->check();
    $staffUser = $isStaff ? Auth::guard('staff')->user() : null;
    $adminUser = $isStaff ? null : Auth::user();
    $displayName = $isStaff
        ? trim($staffUser->first_name . ' ' . $staffUser->last_name)
        : ($adminUser->name ?? 'User');
    $showShopPill = !$isStaff && $adminUser && $adminUser->isAdminTier();
    $switchShops = $showShopPill ? \App\Models\Shops::orderBy('name')->get(['id', 'name']) : collect();
    $activeShopName = $showShopPill && session('active_shop_id')
        ? optional($switchShops->firstWhere('id', session('active_shop_id')))->name
        : null;
    $notifyUser = $staffUser ?? $adminUser;
    $unreadCount = $notifyUser ? $notifyUser->unreadNotifications()->count() : 0;
    $recentNotifications = $notifyUser ? $notifyUser->notifications()->latest()->limit(6)->get() : collect();

    // Admins: everything from staff still waiting for a decision. Read live,
    // so items drop off as soon as they're approved/rejected/resolved.
    $pendingOrders = collect();
    $openIssues = collect();
    $pendingOrderCount = $openIssueCount = 0;
    if ($showShopPill) {
        $activeShopId = session('active_shop_id');
        $ordersQuery = \App\Models\Order::with('staff', 'shop')->where('status', 'pending')
            ->when($activeShopId, fn ($q) => $q->where('shop_id', $activeShopId));
        $issuesQuery = \App\Models\Feedback::with('staff', 'shop')
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'resolved'))
            ->when($activeShopId, fn ($q) => $q->where('shop_id', $activeShopId));
        if ($adminUser->hasPermission('order-requests.view')) {
            $pendingOrderCount = (clone $ordersQuery)->count();
            $pendingOrders = $ordersQuery->latest()->limit(5)->get();
        }
        if ($adminUser->hasPermission('issues.view')) {
            $openIssueCount = (clone $issuesQuery)->count();
            $openIssues = $issuesQuery->latest()->limit(5)->get();
        }
    }
    $pendingTotal = $pendingOrderCount + $openIssueCount;
    $bellCount = $showShopPill ? $pendingTotal : $unreadCount;
?>

<style>
.premium-navbar{ background:#fff !important; border-bottom:1px solid #edf0f7; padding:12px 0; }
.navbar-search-box{ display:flex; align-items:center; gap:10px; background:#f7f8fa; border:1px solid #edf0f7; border-radius:10px; padding:9px 14px; min-width:320px; color:#8891a3; position:relative; }
.navbar-search-box input{ background:transparent; border:none; outline:none; font-size:14px; color:#1d2433; flex:1; min-width:0; }
.navbar-search-box input::placeholder{ color:#9aa3b2; }
.navbar-search-results{ position:absolute; top:calc(100% + 8px); left:0; right:0; z-index:1080; display:none; max-height:min(60vh, 420px); overflow:auto; padding:6px; background:#fff; border:1px solid #e5e9f0; border-radius:10px; box-shadow:0 12px 28px rgba(15,23,42,.16); }
.navbar-search-results.is-open{ display:block; }
.navbar-search-result{ display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:7px; color:#263247; text-decoration:none; font-size:13px; }
.navbar-search-result:hover,.navbar-search-result:focus{ background:#f1f5f9; color:#1d4ed8; }
.navbar-search-empty{ padding:10px; color:#7b8494; font-size:13px; }
.navbar-mobile-search-open{ display:flex !important; position:absolute; top:100%; left:12px; right:12px; z-index:1070; min-width:0; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.12); }
.navbar-kbd{ font-size:11px; font-weight:600; color:#8891a3; background:#fff; border:1px solid #e3e7ef; border-radius:6px; padding:2px 6px; }
.navbar-icon-btn{ width:38px; height:38px; border-radius:50%; background:#f7f8fa; border:1px solid #edf0f7; display:flex; align-items:center; justify-content:center; color:#4b5563; text-decoration:none; position:relative; transition:.15s; }
.navbar-icon-btn:hover{ background:#eef1f6; color:#1d2433; }
.navbar-notify-count{ position:absolute; top:-4px; right:-4px; min-width:18px; height:18px; padding:0 5px; border-radius:9px; background:#f97316; border:2px solid #fff; color:#fff; font-size:10px; font-weight:700; line-height:14px; text-align:center; }
.navbar-notify-menu{ width:min(380px, calc(100vw - 24px)); overflow:hidden; border-radius:12px; }
.navbar-notify-scroll{ max-height:min(70vh, 520px); overflow-y:auto; padding:4px 0 8px; }
.navbar-notify-section{ display:flex; align-items:center; justify-content:space-between; padding:10px 16px 4px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#8891a3; }
.navbar-notify-item{ display:flex; gap:12px; align-items:flex-start; padding:9px 16px; color:#1d2433; text-decoration:none; }
.navbar-notify-item:hover{ background:#f5f7fb; color:#1d2433; }
.navbar-notify-item .min-w-0{ min-width:0; }
.navbar-notify-icon{ width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:15px; }
.navbar-notify-icon.bg-order{ background:#eef2ff; color:#4f46e5; }
.navbar-notify-icon.bg-issue{ background:#fdeceb; color:#dc2626; }
.navbar-notify-icon.bg-info-soft{ background:#f1f5f9; color:#475569; }
.navbar-notify-more{ display:block; padding:4px 16px 8px 62px; font-size:12.5px; font-weight:600; text-decoration:none; }
.navbar-notify-unread{ background:#eff6ff; }
html[data-bs-theme="dark"] .navbar-notify-unread{ background:#1e3a5f; }
html[data-bs-theme="dark"] .navbar-notify-item{ color:#e5e7eb; }
html[data-bs-theme="dark"] .navbar-notify-item:hover{ background:#273449; }
.navbar-user-trigger{ display:flex; align-items:center; gap:10px; text-decoration:none; color:#1d2433; }
.navbar-user-avatar{ width:38px; height:38px; border-radius:50%; object-fit:cover; background:#134AA4; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; }
</style>

<nav class="app-header navbar navbar-expand premium-navbar">
    <div class="container-fluid d-flex align-items-center">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-5"></i>
                </a>
            </li>
            <?php if($showShopPill): ?>
                
                <li class="nav-item dropdown d-none d-md-block">
                    <a href="#" class="nav-link navbar-shop-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Switch shop">
                        <i class="bi bi-shop me-1" aria-hidden="true"></i>
                        Shop : <?php echo e($activeShopName ?? 'All shops'); ?>

                    </a>
                    <div class="dropdown-menu shadow-sm py-1" style="min-width:230px">
                        <form method="POST" action="<?php echo e(route('admin.activeShop.clear')); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="dropdown-item d-flex align-items-center gap-2 <?php echo e($activeShopName ? '' : 'active'); ?>">
                                <i class="bi bi-grid"></i> All shops
                                <?php if (! ($activeShopName)): ?><i class="bi bi-check2 ms-auto"></i><?php endif; ?>
                            </button>
                        </form>
                        <div class="dropdown-divider"></div>
                        <?php $__currentLoopData = $switchShops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $switchShop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $isCurrent = (string) session('active_shop_id') === (string) $switchShop->id; ?>
                            <form method="POST" action="<?php echo e(route('admin.activeShop.set')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="shop_id" value="<?php echo e($switchShop->id); ?>">
                                <button class="dropdown-item d-flex align-items-center gap-2 <?php echo e($isCurrent ? 'active' : ''); ?>">
                                    <i class="bi bi-shop"></i> <?php echo e($switchShop->name); ?>

                                    <?php if($isCurrent): ?><i class="bi bi-check2 ms-auto"></i><?php endif; ?>
                                </button>
                            </form>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item small text-muted" href="<?php echo e(route('dashboard.shop')); ?>"><i class="bi bi-gear me-1"></i> Manage shops</a>
                    </div>
                </li>
            <?php endif; ?>
        </ul>

        <div class="navbar-search-box d-none d-md-flex ms-3" id="navbar-search-box">
            <i class="bi bi-search"></i>
            <input type="search" id="navbar-search-input" placeholder="Search pages..." autocomplete="off" aria-label="Search pages">
            <span class="navbar-kbd">&#8984; K</span>
            <div class="navbar-search-results" id="navbar-search-results" role="listbox"></div>
        </div>

        <ul class="navbar-nav ms-auto align-items-center gap-2">

            <li class="nav-item d-md-none">
                <a class="nav-link" id="mobile-search-toggle" href="#" role="button" aria-label="Search pages">
                    <i class="bi bi-search"></i>
                </a>
            </li>

            <li class="nav-item">
                <a class="navbar-icon-btn" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
                </a>
            </li>

            <li class="nav-item">
                <button type="button" class="navbar-icon-btn" id="theme-toggle" aria-label="Switch to dark theme" title="Switch theme">
                    <i class="bi bi-sun-fill" id="theme-toggle-icon" aria-hidden="true"></i>
                </button>
            </li>

            <li class="nav-item dropdown">
                <a class="navbar-icon-btn" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                   aria-label="Notifications<?php echo e($bellCount ? ' (' . $bellCount . ($showShopPill ? ' waiting' : ' unread') . ')' : ''); ?>">
                    <i class="bi bi-bell"></i>
                    <?php if($bellCount): ?>
                        <span class="navbar-notify-count"><?php echo e($bellCount > 9 ? '9+' : $bellCount); ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0 navbar-notify-menu">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <strong>Notifications</strong>
                        <?php if($unreadCount): ?>
                            <form method="POST" action="<?php echo e(route('notifications.readAll')); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-link btn-sm p-0 small text-decoration-none">Mark all read</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="navbar-notify-scroll">
                        <?php if($showShopPill): ?>
                            
                            <div class="navbar-notify-section">
                                <span>Waiting for you</span>
                                <?php if($pendingTotal): ?><span class="badge rounded-pill bg-danger"><?php echo e($pendingTotal); ?></span><?php endif; ?>
                            </div>

                            <?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="navbar-notify-item" href="<?php echo e(route('admin.orders.show', $order->id)); ?>">
                                    <span class="navbar-notify-icon bg-order"><i class="bi bi-bag-check"></i></span>
                                    <span class="flex-grow-1 min-w-0">
                                        <span class="d-block fw-semibold small">Order request #<?php echo e($order->id); ?></span>
                                        <span class="d-block small text-muted text-truncate">
                                            <?php echo e($order->staff->full_name ?? 'Staff'); ?> · <?php echo e($order->shop->name ?? '—'); ?> · Tsh <?php echo e(number_format($order->total_amount)); ?>

                                        </span>
                                        <span class="d-block small text-muted"><?php echo e($order->created_at->diffForHumans()); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($pendingOrderCount > $pendingOrders->count()): ?>
                                <a class="navbar-notify-more" href="<?php echo e(route('admin.orders.index')); ?>">
                                    See all <?php echo e($pendingOrderCount); ?> pending order requests <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>

                            <?php $__currentLoopData = $openIssues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="navbar-notify-item" href="<?php echo e(route('admin.issues')); ?>">
                                    <span class="navbar-notify-icon bg-issue"><i class="bi bi-exclamation-circle"></i></span>
                                    <span class="flex-grow-1 min-w-0">
                                        <span class="d-block fw-semibold small">Issue: <?php echo e(ucfirst($issue->type)); ?></span>
                                        <span class="d-block small text-muted text-truncate">
                                            <?php echo e($issue->staff->full_name ?? 'Staff'); ?> · <?php echo e(\Illuminate\Support\Str::limit($issue->message, 60)); ?>

                                        </span>
                                        <span class="d-block small text-muted"><?php echo e($issue->created_at->diffForHumans()); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($openIssueCount > $openIssues->count()): ?>
                                <a class="navbar-notify-more" href="<?php echo e(route('admin.issues')); ?>">
                                    See all <?php echo e($openIssueCount); ?> open issues <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!$pendingTotal): ?>
                                <div class="px-3 py-2 small text-muted"><i class="bi bi-check2-circle text-success me-1"></i>Nothing waiting — all caught up.</div>
                            <?php endif; ?>

                            <div class="navbar-notify-section">Recent</div>
                        <?php endif; ?>

                        <?php $__empty_1 = true; $__currentLoopData = $recentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a class="navbar-notify-item <?php echo e($notification->read_at ? '' : 'navbar-notify-unread'); ?>"
                               href="<?php echo e(route('notifications.open', $notification->id)); ?>">
                                <span class="navbar-notify-icon bg-info-soft"><i class="bi bi-bell"></i></span>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="d-block fw-semibold small"><?php echo e($notification->data['title'] ?? 'Notification'); ?></span>
                                    <span class="d-block small text-muted text-truncate"><?php echo e($notification->data['message'] ?? ''); ?></span>
                                    <span class="d-block small text-muted"><?php echo e($notification->created_at->diffForHumans()); ?></span>
                                </span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="px-3 py-2 small text-muted">No notifications yet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="navbar-user-trigger dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="navbar-user-avatar">
                        <?php if(!$isStaff && $adminUser && $adminUser->profile_image): ?>
                            <img src="<?php echo e(asset('storage/' . $adminUser->profile_image)); ?>" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr($displayName, 0, 1))); ?>

                        <?php endif; ?>
                    </span>
                    <span class="d-none d-md-inline fw-semibold"><?php echo e($displayName); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (! ($isStaff)): ?>
                        <li><a class="dropdown-item" href="<?php echo e(route('profile')); ?>"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li>
                        <form action="<?php echo e(route('logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
(function () {
    const root = document.documentElement;
    const toggle = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-toggle-icon');
    const storageKey = 'double-h-color-theme';
    let theme = 'light';

    try {
        theme = localStorage.getItem(storageKey) || 'light';
    } catch (error) {
        // Keep the toggle usable when browser storage is unavailable.
    }

    function applyTheme(value) {
        theme = value;
        root.setAttribute('data-bs-theme', theme);
        if (icon) icon.className = theme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        if (toggle) toggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            try {
                localStorage.setItem(storageKey, nextTheme);
            } catch (error) {
                // Apply the choice for this page even if it cannot be saved.
            }
            applyTheme(nextTheme);
        });
    }

    applyTheme(theme === 'dark' ? 'dark' : 'light');
})();
</script>

<script>
(function () {
    const box = document.getElementById('navbar-search-box');
    const input = document.getElementById('navbar-search-input');
    const results = document.getElementById('navbar-search-results');
    const mobileToggle = document.getElementById('mobile-search-toggle');
    if (!box || !input || !results) return;

    const destinations = Array.from(document.querySelectorAll('.premium-sidebar a.nav-link[href]'))
        .map(function (link) {
            return { label: link.textContent.trim().replace(/\s+/g, ' '), href: link.href };
        })
        .filter(function (item, index, all) {
            return item.label && item.href !== window.location.href.split('#')[0]
                && all.findIndex(function (candidate) { return candidate.href === item.href && candidate.label === item.label; }) === index;
        });

    function closeResults() {
        results.classList.remove('is-open');
    }

    function renderResults() {
        const query = input.value.trim().toLocaleLowerCase();
        results.replaceChildren();
        if (!query) { closeResults(); return; }

        const matches = destinations.filter(function (item) {
            return item.label.toLocaleLowerCase().includes(query);
        }).slice(0, 10);

        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'navbar-search-empty';
            empty.textContent = 'No matching pages';
            results.appendChild(empty);
        } else {
            matches.forEach(function (item) {
                const link = document.createElement('a');
                link.className = 'navbar-search-result';
                link.href = item.href;
                link.setAttribute('role', 'option');
                const icon = document.createElement('i');
                icon.className = 'bi bi-arrow-up-right';
                const label = document.createElement('span');
                label.textContent = item.label;
                link.append(icon, label);
                results.appendChild(link);
            });
        }
        results.classList.add('is-open');
    }

    input.addEventListener('input', renderResults);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeResults();
        if (event.key === 'Enter') {
            const firstResult = results.querySelector('a[href]');
            if (firstResult) window.location.assign(firstResult.href);
        }
    });
    document.addEventListener('click', function (event) {
        if (!box.contains(event.target)) closeResults();
    });

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function (event) {
            event.preventDefault();
            const isOpen = box.classList.toggle('navbar-mobile-search-open');
            box.classList.toggle('d-none', !isOpen);
            if (isOpen) input.focus();
            else closeResults();
        });
    }

    document.addEventListener('keydown', function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            if (window.matchMedia('(max-width: 767px)').matches) {
                box.classList.remove('d-none');
                box.classList.add('navbar-mobile-search-open');
            }
            input.focus();
        }
    });
})();
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/components/app_navbar.blade.php ENDPATH**/ ?>