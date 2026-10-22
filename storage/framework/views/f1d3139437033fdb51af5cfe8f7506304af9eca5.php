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
            <?php
                // Menu definition lives in config/sidebar.php.
                $user = $staffUser ?? $adminUser;
                $badges = ['pending_orders' => $pendingOrders, 'open_issues' => $openIssues];

                $allowed = function (array $item) use ($isStaff, $can, $adminUser) {
                    if (!empty($item['super_user']) && !($adminUser && $adminUser->super_user)) return false;
                    if ($isStaff) {
                        $permission = $item['permission'] ?? null;
                        return $permission === null || collect((array) $permission)->contains($can);
                    }
                    // Admin links default to the same map the server enforces.
                    $permission = array_key_exists('permission', $item)
                        ? $item['permission']
                        : \App\Http\Middleware\PanelPermissionMiddleware::permissionFor($item['route']);
                    return $permission === null
                        || collect((array) $permission)->contains(fn ($p) => $adminUser->hasPermission($p));
                };
                $isActive = fn (array $item) => request()->routeIs(...(array) ($item['patterns'] ?? $item['route']));
                $params = fn (array $item) => collect($item['params'] ?? [])->map(fn ($attr) => $user->{$attr})->all();

                // Drop links the user may not open, and groups left with no children.
                $sections = collect(config('sidebar.' . ($isStaff ? 'staff' : 'admin'), []))
                    ->map(function ($section) use ($allowed) {
                        $section['items'] = collect($section['items'] ?? [])->map(function ($item) use ($allowed) {
                            if (isset($item['children'])) {
                                $item['children'] = array_values(array_filter($item['children'], $allowed));
                                return $item['children'] ? $item : null;
                            }
                            return $allowed($item) ? $item : null;
                        })->filter()->values();
                        return $section;
                    })
                    ->filter(fn ($section) => $section['items']->isNotEmpty())
                    ->values();
            ?>

            <ul class="nav flex-column" id="sidebarAccordion">
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (! ($loop->first)): ?>
                        <li class="nav-divider"></li>
                    <?php endif; ?>
                    <?php if(!empty($section['title'])): ?>
                        <li class="nav-section-title"><?php echo e($section['title']); ?></li>
                    <?php endif; ?>

                    <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(isset($item['children'])): ?>
                            <?php
                                $groupId = \Illuminate\Support\Str::camel($item['title']) . 'Submenu';
                                $open = collect($item['children'])->contains($isActive);
                            ?>
                            <li class="nav-item mb-1">
                                <a href="#<?php echo e($groupId); ?>" class="nav-link d-flex align-items-center px-3 py-2 <?php echo e($open ? 'active' : ''); ?>"
                                   data-bs-toggle="collapse" role="button" aria-expanded="<?php echo e($open ? 'true' : 'false'); ?>">
                                    <i class="nav-icon bi <?php echo e($item['icon'] ?? ''); ?>"></i><span class="flex-grow-1"><?php echo e($item['title']); ?></span><i class="bi bi-chevron-down"></i>
                                </a>
                                <div class="collapse <?php echo e($open ? 'show' : ''); ?>" id="<?php echo e($groupId); ?>" data-bs-parent="#sidebarAccordion">
                                    <ul class="nav-submenu">
                                        <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>
                                                <a href="<?php echo e(route($child['route'], $params($child))); ?>" class="nav-link d-flex align-items-center py-2 <?php echo e($isActive($child) ? 'active' : ''); ?>">
                                                    <span><?php echo e($child['title']); ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php echo $__env->make('components.sidebar_link', [
                                'route' => $item['route'],
                                'label' => $item['title'],
                                'icon' => $item['icon'] ?? '',
                                'isActive' => $isActive($item),
                                'params' => $params($item),
                                'badge' => isset($item['badge']) ? ($badges[$item['badge']] ?: null) : null,
                            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\components\app_sidebar.blade.php ENDPATH**/ ?>