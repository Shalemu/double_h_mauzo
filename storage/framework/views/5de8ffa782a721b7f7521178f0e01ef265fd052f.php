<?php
    $isStaff = Auth::guard('staff')->check();
    $staffUser = $isStaff ? Auth::guard('staff')->user() : null;
    $adminUser = $isStaff ? null : Auth::user();
    $activeShop = (!$isStaff && $adminUser)
        ? (session('active_shop_id') ? \App\Models\Shops::find(session('active_shop_id')) : null)
        : null;
    $pendingOrders = (!$isStaff && $adminUser && $adminUser->isAdminTier())
        ? \App\Models\Order::where('status', 'pending')
            ->when($activeShop, fn ($q) => $q->where('shop_id', $activeShop->id))
            ->count()
        : 0;
    $openIssues = (!$isStaff && $adminUser && $adminUser->isAdminTier())
        ? \App\Models\Feedback::where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'resolved'))
            ->when($activeShop, fn ($q) => $q->where('shop_id', $activeShop->id))
            ->count()
        : 0;
    $can =fn ($permission) => $staffUser && $staffUser->hasPermission($permission);

    // Flat sidebar link: [route, label, icon, active-pattern(s), route params]
    $link = function ($route, $label, $icon, $active, $params = [], $badge = null) {
        $isActive = collect((array) $active)->contains(fn ($pattern) => request()->routeIs($pattern));
        return view('components.sidebar_link', compact('route', 'label', 'icon', 'isActive', 'params', 'badge'))->render();
    };
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* Dark slate sidebar: bold Outfit labels, roomy rows, soft highlight with a logo-red
   icon on the current page. The logo stays pinned; the menu scrolls
   and its scrollbar only shows while the mouse is over the sidebar. */
.premium-sidebar {
    --sidebar-bg:#1e293b;
    --sidebar-active:#334155;
    --sidebar-hover:rgba(255,255,255,.06);
    /* Logo red (#831619), lifted so it stays readable on the dark sidebar */
    --sidebar-accent:#ec4e53;
    font-family:'Outfit', system-ui, -apple-system, 'Segoe UI', sans-serif;
    background:var(--sidebar-bg) !important; border-right:none; display:flex !important; flex-direction:column;
    height:100vh; max-height:100vh; overflow-y:auto !important; overflow-x:hidden;
}
/* Firefox only: Chrome ignores the ::-webkit-scrollbar rules below (and
   shows arrow buttons) whenever scrollbar-color is set. */
@supports (-moz-appearance:none) {
    .premium-sidebar { scrollbar-width:thin; scrollbar-color:transparent transparent; }
    .premium-sidebar:hover { scrollbar-color:rgba(255,255,255,.3) transparent; }
}
.premium-sidebar::-webkit-scrollbar { width:6px; }
.premium-sidebar::-webkit-scrollbar-track { background:transparent; }
.premium-sidebar::-webkit-scrollbar-thumb { background-color:transparent; border-radius:6px; }
.premium-sidebar:hover::-webkit-scrollbar-thumb { background-color:rgba(255,255,255,.3); }
.premium-sidebar::-webkit-scrollbar-thumb:hover { background-color:rgba(255,255,255,.5); }
.premium-sidebar::-webkit-scrollbar-button { display:none; height:0; }

/* Brand */
.premium-sidebar .sidebar-brand { position:sticky; top:0; z-index:2; background:var(--sidebar-bg); flex-shrink:0; height:auto !important; overflow:visible !important;
    padding:22px 16px 18px; display:flex; flex-direction:column; align-items:center; gap:10px; border-bottom:1px solid rgba(255,255,255,.08); }
.premium-sidebar .brand-image-wrap { width:64px; height:64px; border-radius:14px; background:#fff; display:flex; align-items:center; justify-content:center; padding:8px; box-shadow:0 4px 14px rgba(0,0,0,.25); }
.premium-sidebar .brand-image { width:100%; height:100%; object-fit:contain; }
.premium-sidebar .brand-text { color:#fff !important; font-weight:700; font-size:17px; letter-spacing:.01em; text-align:center; }

/* Menu */
.premium-sidebar .sidebar-wrapper { flex:0 0 auto; height:auto !important; overflow:visible !important; padding:14px 14px 24px !important; }
.premium-sidebar .nav-item { margin-bottom:6px !important; }
.premium-sidebar .nav-link {
    display:flex; align-items:center; gap:14px;
    padding:13px 16px !important; border-radius:10px;
    color:#fff !important; font-size:16px; font-weight:600; letter-spacing:.005em;
    transition:background .15s, color .15s;
}
.premium-sidebar .nav-link:hover { background:var(--sidebar-hover); }
.premium-sidebar .nav-link.active { background:var(--sidebar-active) !important; }
.premium-sidebar .nav-link[aria-expanded="true"] { background:var(--sidebar-hover) !important; }
.premium-sidebar .nav-icon { font-size:20px; width:24px; margin:0 !important; text-align:center; color:#fff; flex-shrink:0; }
.premium-sidebar .nav-link.active .nav-icon,
.premium-sidebar .nav-link[aria-expanded="true"] .nav-icon { color:var(--sidebar-accent); }
.premium-sidebar .nav-link .bi-chevron-down { font-size:14px; margin-left:auto; transition:transform .2s; }
.premium-sidebar .nav-link[aria-expanded="true"] .bi-chevron-down { transform:rotate(180deg); }

/* Sub-menu */
.premium-sidebar .nav-submenu { list-style:none; padding:4px 0 6px; margin:0; }
.premium-sidebar .nav-submenu .nav-link { padding:10px 16px 10px 54px !important; font-size:15px; font-weight:500; color:#cbd5e1 !important; }
.premium-sidebar .nav-submenu .nav-link:hover { color:#fff !important; }
.premium-sidebar .nav-submenu .nav-link.active { background:var(--sidebar-active) !important; color:#fff !important; }

/* Category headings + dividers */
.premium-sidebar .nav-section-title { font-size:11.5px; font-weight:600; text-transform:uppercase; letter-spacing:.1em; color:#94a3b8; padding:14px 16px 8px; list-style:none; }
.premium-sidebar .nav-divider { height:1px; background:rgba(255,255,255,.08); margin:12px 6px 4px; list-style:none; }

.premium-sidebar .nav-badge { margin-left:auto; background:var(--sidebar-accent); color:#fff; font-size:11.5px; font-weight:700; border-radius:10px; padding:1px 8px; }
</style>

<aside class="app-sidebar premium-sidebar shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <div class="brand-image-wrap">
            <img src="<?php echo e(asset('assets/img/logo.png')); ?>" alt="Logo" class="brand-image">
        </div>
        <span class="brand-text">Double H Cosmetics</span>
    </div>


    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav flex-column" id="sidebarAccordion">

                <?php if($isStaff): ?>
                    
                    <li class="nav-section-title">Sales &amp; Revenue</li>
                    <?php echo $link('staff.dashboard', 'POS / Sell', 'bi-cash-coin', 'staff.dashboard'); ?>

                    <?php if($can('sales.view')): ?>
                        <?php echo $link('staff.sales.index', 'My Sales', 'bi-receipt', 'staff.sales.*', [$staffUser->shop_id]); ?>

                    <?php endif; ?>
                    <?php if($can('customers.manage')): ?>
                        <?php echo $link('staff.customers.manage', 'Customers & Credit', 'bi-people', 'staff.customers.*'); ?>

                    <?php endif; ?>
                    <?php if($can('expenses.manage')): ?>
                        <?php echo $link('staff.expenses.index', 'Expenses', 'bi-wallet2', 'staff.expenses.*', [$staffUser->shop_id]); ?>

                    <?php endif; ?>

                    <?php if($can('orders.create') || $can('orders.view')): ?>
                        <li class="nav-divider"></li>
                        <li class="nav-section-title">Procurement</li>
                        <?php if($can('orders.create')): ?>
                            <?php echo $link('staff.orders.create', 'Request Stock', 'bi-cart-plus', 'staff.orders.create'); ?>

                        <?php endif; ?>
                        <?php if($can('orders.view')): ?>
                            <?php echo $link('staff.orders.index', 'My Order Requests', 'bi-bag-check', ['staff.orders.index', 'staff.orders.show']); ?>

                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($can('products.view')): ?>
                        <li class="nav-divider"></li>
                        <li class="nav-section-title">Inventory</li>
                        <?php echo $link('staff.products.index', 'Shop Items', 'bi-box-seam', 'staff.products.*'); ?>

                    <?php endif; ?>

                    <?php if($can('issues.report')): ?>
                        <li class="nav-divider"></li>
                        <?php echo $link('staff.report.issue.index', 'Report Issue', 'bi-exclamation-circle', 'staff.report.issue.*'); ?>

                    <?php endif; ?>
                <?php else: ?>
                    
                    <?php
                        // Links the signed-in role may open (same map the server enforces).
                        $allowed = function ($route) use ($adminUser) {
                            $permission = \App\Http\Middleware\PanelPermissionMiddleware::permissionFor($route);
                            return $permission === null
                                || collect((array) $permission)->contains(fn ($p) => $adminUser->hasPermission($p));
                        };
                        $panelLink = fn (...$args) => $allowed($args[0]) ? $link(...$args) : '';
                        $section = function ($title, array $links) {
                            $html = implode('', array_filter($links));
                            return $html === '' ? '' : '<li class="nav-divider"></li><li class="nav-section-title">' . e($title) . '</li>' . $html;
                        };
                        $subLink = fn ($route, $label, $patterns) => $allowed($route)
                            ? '<li><a href="' . route($route) . '" class="nav-link d-flex align-items-center py-2 ' . (request()->routeIs(...(array) $patterns) ? 'active' : '') . '"><span>' . e($label) . '</span></a></li>'
                            : '';
                        $group = function ($id, $label, $icon, $openPatterns, array $subLinks) {
                            $items = implode('', array_filter($subLinks));
                            if ($items === '') return '';
                            $open = request()->routeIs(...(array) $openPatterns);
                            return '<li class="nav-item mb-1"><a href="#' . $id . '" class="nav-link d-flex align-items-center px-3 py-2 ' . ($open ? 'active' : '') . '"'
                                . ' data-bs-toggle="collapse" role="button" aria-expanded="' . ($open ? 'true' : 'false') . '">'
                                . '<i class="nav-icon bi ' . $icon . '"></i><span class="flex-grow-1">' . e($label) . '</span><i class="bi bi-chevron-down"></i></a>'
                                . '<div class="collapse ' . ($open ? 'show' : '') . '" id="' . $id . '" data-bs-parent="#sidebarAccordion"><ul class="nav-submenu">' . $items . '</ul></div></li>';
                        };
                    ?>

                    <?php echo $panelLink('dashboard.admin', 'Dashboard', 'bi-speedometer2', 'dashboard.admin'); ?>


                    <?php echo $section('Administration', [
                        $group('businessSubmenu', 'Manage Business', 'bi-shop', ['dashboard.shop', 'dashboard.shop.show'], [
                            $subLink('dashboard.shop', 'All Shops', ['dashboard.shop', 'dashboard.shop.show']),
                        ]),
                        $group('hrSubmenu', 'Human Resource', 'bi-person-vcard', ['staff.manage.*', 'dashboard.role', 'dashboard.roles.*'], [
                            $subLink('staff.manage.index', 'Staff', 'staff.manage.*'),
                            $subLink('dashboard.role', 'Roles & Permissions', ['dashboard.role', 'dashboard.roles.*']),
                        ]),
                        $panelLink('admin.issues', 'Reported Issues', 'bi-exclamation-circle', 'admin.issues', [], $openIssues ?: null),
                        ($adminUser && $adminUser->super_user) ? $link('users.manage.index', 'User Management', 'bi-shield-lock', 'users.manage.*') : '',
                    ]); ?>


                    <?php echo $section('Sales & Revenue', [
                        $panelLink('admin.pos.index', 'POS / Sell', 'bi-cash-coin', 'admin.pos.*'),
                        $panelLink('admin.sales.index', 'Sales', 'bi-receipt', ['admin.sales.index', 'admin.sales.detail']),
                        $panelLink('admin.sales.items', 'Sale Items', 'bi-basket', 'admin.sales.items'),
                        $panelLink('returns.sales', 'Sales Returns', 'bi-arrow-return-left', ['returns.sales', 'admin.sales_returns.detail']),
                        $panelLink('admin.credit', 'Credit', 'bi-credit-card', 'admin.credit'),
                        $panelLink('customers.index', 'Customers', 'bi-people', 'customers.*'),
                        $panelLink('admin.revenue', 'Revenue & Profit', 'bi-graph-up-arrow', 'admin.revenue'),
                        $panelLink('admin.expenses', 'Expenses', 'bi-wallet2', ['admin.expenses', 'expenses.*']),
                        $panelLink('admin.fixedExpenses', 'Fixed Expenses', 'bi-calendar2-check', ['admin.fixedExpenses', 'fixed-expenses.*']),
                    ]); ?>


                    
                    <?php echo $section('Procurement', [
                        $panelLink('admin.orders.index', 'Order Requests', 'bi-bag-check', 'admin.orders.*', [], $pendingOrders ?: null),
                        $panelLink('products.index', 'Product Management', 'bi-box-seam', ['products.index', 'products.create', 'products.edit', 'products.in-stock', 'products.running-out', 'products.expiring', 'products.expired', 'products.out-of-stock', 'products.finished']),
                        $panelLink('suppliers.index', 'Suppliers', 'bi-truck', 'suppliers.*'),
                        $panelLink('procurement.supplierProducts', 'Supplier Products', 'bi-diagram-3', 'procurement.supplierProducts'),
                        $panelLink('procurement.prices', 'Price Manager', 'bi-tags', 'procurement.prices*'),
                        $panelLink('purchases.index', 'Purchases', 'bi-cart-check', 'purchases.*'),
                        $panelLink('returns.purchases', 'Purchase Returns', 'bi-box-arrow-left', ['returns.purchases', 'admin.purchase_returns.detail']),
                    ]); ?>


                    <?php echo $section('Inventory', [
                        $panelLink('stock-transfers.index', 'Stock Transfers', 'bi-arrow-left-right', 'stock-transfers.*'),
                        $panelLink('products.trash', 'Deleted Products', 'bi-trash', 'products.trash'),
                    ]); ?>

                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/components/app_sidebar.blade.php ENDPATH**/ ?>