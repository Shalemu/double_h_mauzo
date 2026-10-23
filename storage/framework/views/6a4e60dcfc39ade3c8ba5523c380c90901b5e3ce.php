<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - DOUBLE H COSMETICS Admin Panel</title>

    <?php echo $__env->make('components.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <script>
    (function () {
        if (!window.Swal) return;

        const toastDefaults = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (el) => {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
            },
        };
        const originalFire = Swal.fire.bind(Swal);

        // notify('success'|'error'|'warning'|'info', 'Title', 'optional detail')
        window.notify = function (icon, title, text) {
            return originalFire(Object.assign({}, toastDefaults, {
                icon: icon,
                title: title,
                text: text || undefined,
                // Problems stay up a little longer so they can be read.
                timer: icon === 'error' || icon === 'warning' ? 6000 : 3500,
            }));
        };

        // Plain "done" pop-ups become toasts; anything that asks a question stays a dialog.
        Swal.fire = function () {
            let opts = arguments[0];
            if (typeof opts === 'string') {
                opts = { title: arguments[0], text: arguments[1], icon: arguments[2] };
            }
            const asks = opts && (opts.showCancelButton || opts.showDenyButton || opts.input || opts.preConfirm);
            if (opts && typeof opts === 'object' && opts.icon === 'success' && !opts.toast && !asks) {
                const both = opts.title && opts.text;
                return window.notify('success', both ? opts.title : (opts.text || opts.title), both ? opts.text : undefined);
            }
            return originalFire.apply(null, arguments);
        };

        // Messages from the server after saving (session flash + first validation error).
        <?php
            $flashMessages = array_filter([
                'success' => session('success'),
                'error'   => session('error') ?? ((isset($errors) && $errors->any()) ? $errors->first() : null),
                'warning' => session('warning'),
                'info'    => session('info'),
            ], fn ($m) => is_string($m) && $m !== '');
        ?>
        const flashes = <?php echo json_encode((object) $flashMessages, 15, 512) ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const texts = Object.values(flashes).map(t => t.replace(/\s+/g, ' ').trim());
            // Pages that still print the same message as a box: hide that copy (the toast replaces it).
            document.querySelectorAll('.alert').forEach(function (box) {
                const text = box.textContent.replace(/\s+/g, ' ').trim();
                if (text && texts.includes(text)) box.remove();
            });
            Object.entries(flashes).forEach(([icon, text], i) => setTimeout(() => window.notify(icon, text), i * 400));
        });
    })();
    </script>

    <link rel="stylesheet" href="<?php echo e(asset('admin/css/adminlte.css')); ?>">

    <style>
        /* components.header (shared with the legacy .cat__top-bar layout
           still used by the shop-detail page) loads top-bar.cleanui.css,
           which sets a global `body { padding-top: 4.64rem }` meant to
           clear that old *fixed* top bar. This layout doesn't use that
           header at all (AdminLTE's own grid positions .app-header), so
           that padding was just leaving a blank gap above the sidebar.
           Cancel it here, scoped to this layout only. */
        body { padding-top: 0 !important; }

        html[data-bs-theme="dark"] body {
            background:#111827 !important;
            color:#e5e7eb;
        }
        html[data-bs-theme="dark"] .app-main,
        html[data-bs-theme="dark"] .app-content,
        html[data-bs-theme="dark"] .app-content-header {
            background:#111827 !important;
            color:#e5e7eb;
        }
        html[data-bs-theme="dark"] .app-header,
        html[data-bs-theme="dark"] .premium-navbar {
            background:#1f2937 !important;
            border-color:#374151 !important;
            color:#e5e7eb;
        }
        html[data-bs-theme="dark"] .premium-navbar .navbar-icon-btn,
        html[data-bs-theme="dark"] .premium-navbar .navbar-user-trigger,
        html[data-bs-theme="dark"] .premium-navbar .navbar-search-box {
            background:#273449 !important;
            border-color:#3b4759 !important;
            color:#e5e7eb !important;
        }
        html[data-bs-theme="dark"] .premium-navbar .navbar-search-box input { color:#f3f4f6 !important; }
        html[data-bs-theme="dark"] .premium-navbar .navbar-kbd { background:#1f2937; border-color:#4b5563; color:#cbd5e1; }
        /* Sidebar is already dark; just separate it from the dark page. */
        html[data-bs-theme="dark"] .premium-sidebar {
            border-right:1px solid #273449;
        }
        html[data-bs-theme="dark"] .card,
        html[data-bs-theme="dark"] .cat__core__widget,
        html[data-bs-theme="dark"] .dashboard-card,
        html[data-bs-theme="dark"] .dashboard-panel,
        html[data-bs-theme="dark"] .products-page-card,
        html[data-bs-theme="dark"] .shop-table-container,
        html[data-bs-theme="dark"] .customer-contact-item,
        html[data-bs-theme="dark"] .bg-white {
            background-color:#1f2937 !important;
            background-image:none !important;
            border-color:#374151 !important;
            color:#e5e7eb !important;
        }
        html[data-bs-theme="dark"] .card-header,
        html[data-bs-theme="dark"] .products-page-card .card-header,
        html[data-bs-theme="dark"] .customer-detail-page .history-header {
            background:#273449 !important;
            border-color:#374151 !important;
            color:#f3f4f6 !important;
        }
        html[data-bs-theme="dark"] .dashboard-title,
        html[data-bs-theme="dark"] .dashboard-panel-title,
        html[data-bs-theme="dark"] .customer-stat-label,
        html[data-bs-theme="dark"] .text-muted {
            color:#aeb9c8 !important;
        }
        html[data-bs-theme="dark"] .table {
            --bs-table-bg:#1f2937;
            --bs-table-color:#e5e7eb;
            --bs-table-border-color:#374151;
            --bs-table-hover-bg:#273449;
            --bs-table-hover-color:#fff;
        }
        html[data-bs-theme="dark"] .table-warning {
            --bs-table-bg:#493c1b;
            --bs-table-color:#f8e4a1;
            --bs-table-border-color:#66552b;
        }
        html[data-bs-theme="dark"] .form-control,
        html[data-bs-theme="dark"] .form-select {
            background-color:#111827;
            border-color:#4b5563;
            color:#e5e7eb;
        }

        /* GLOBAL PAGE-LOAD PROGRESS BAR */
        #global-loader{
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:3px;
            z-index:99999;
            background: transparent;
            pointer-events: none;
            opacity: 0;
            transition: opacity .2s ease;
        }
        #global-loader.is-active{
            opacity: 1;
        }
        #global-loader .bar{
            height:100%;
            width:0%;
            background: linear-gradient(90deg, #a02128, #d4454c);
            box-shadow: 0 0 10px rgba(160,33,40,.6);
            transition: width .4s ease;
        }
        #global-loader.is-active .bar{
            width: 78%;
            transition: width 3.5s cubic-bezier(.1,.6,.2,1);
        }
        #global-loader.is-done .bar{
            width: 100%;
            transition: width .2s ease;
        }

        /* Primary buttons use the logo red (#831619) instead of Bootstrap blue. */
        .btn-primary {
            --bs-btn-bg: #831619;
            --bs-btn-border-color: #831619;
            --bs-btn-hover-bg: #6b1215;
            --bs-btn-hover-border-color: #6b1215;
            --bs-btn-active-bg: #5a0f12;
            --bs-btn-active-border-color: #5a0f12;
            --bs-btn-disabled-bg: #831619;
            --bs-btn-disabled-border-color: #831619;
            --bs-btn-focus-shadow-rgb: 131, 22, 25;
        }
        .btn-outline-primary {
            --bs-btn-color: #831619;
            --bs-btn-border-color: #831619;
            --bs-btn-hover-bg: #831619;
            --bs-btn-hover-border-color: #831619;
            --bs-btn-active-bg: #6b1215;
            --bs-btn-active-border-color: #6b1215;
            --bs-btn-disabled-color: #831619;
            --bs-btn-disabled-border-color: #831619;
            --bs-btn-focus-shadow-rgb: 131, 22, 25;
        }
        /* The legacy cleanui theme (vendors/bootstrap/css/buttons.css) hard-codes
           .btn.btn-primary to blue, some states with !important — match its
           selectors so the logo red wins. */
        .btn.btn-primary, .show > .btn.btn-primary {
            background-color: #831619;
            border-color: #831619;
            color: #fff;
        }
        .btn.btn-primary:hover, .btn.btn-primary:active, .show > .btn.btn-primary:hover, .show > .btn.btn-primary:active {
            background-color: #6b1215;
            border-color: #6b1215;
            color: #fff;
        }
        .btn.btn-primary:hover:active, .btn.btn-primary:focus, .btn.btn-primary.active,
        .show > .btn.btn-primary:hover:active, .show > .btn.btn-primary:focus, .show > .btn.btn-primary.active {
            background: #5a0f12 !important;
            border-color: #5a0f12 !important;
            color: #fff;
        }
        .btn.btn-primary:disabled, .btn.btn-primary.disabled {
            background-color: #831619;
            border-color: #831619;
            opacity: .6;
        }
        .btn.btn-outline-primary, .show > .btn.btn-outline-primary {
            border-color: #831619;
            color: #831619;
        }
        .btn.btn-outline-primary:hover, .btn.btn-outline-primary:active, .btn.btn-outline-primary.active,
        .show > .btn.btn-outline-primary:hover, .show > .btn.btn-outline-primary:active {
            background-color: #831619 !important;
            border-color: #831619 !important;
            color: #fff !important;
        }
        .pagination {
            --bs-pagination-color: #831619;
            --bs-pagination-hover-color: #6b1215;
            --bs-pagination-focus-color: #6b1215;
            --bs-pagination-active-bg: #831619;
            --bs-pagination-active-border-color: #831619;
        }
        .pagination .page-item.active .page-link {
            background-color: #831619 !important;
            border-color: #831619 !important;
            color: #fff !important;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">


<div id="global-loader"><div class="bar"></div></div>
<script>
(function () {
    var loader = document.getElementById('global-loader');
    if (!loader) return;
    loader.classList.add('is-active');
    window.addEventListener('load', function () {
        loader.classList.remove('is-active');
        loader.classList.add('is-done');
        setTimeout(function () { loader.classList.remove('is-done'); }, 250);
    });
    window.addEventListener('beforeunload', function () {
        loader.classList.remove('is-done');
        loader.classList.add('is-active');
    });
})();
</script>

<div class="app-wrapper">
    <?php echo $__env->make('components.app_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components.app_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-3">
                    <div class="col-sm-6">
                        <h3 class="mb-0"><?php echo $__env->yieldContent('title'); ?></h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo e(Auth::guard('staff')->check() ? route('staff.dashboard') : route('dashboard.admin')); ?>">Home</a></li>
                            <li class="breadcrumb-item active"><?php echo $__env->yieldContent('title'); ?></li>
                        </ol>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </main>

    <?php echo $__env->make('components.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>

<!-- Unit / Category quick-add modals (shared globally, was duplicated per-page before) -->
<?php echo $__env->make('components.quick_add_modals', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<script src="<?php echo e(asset('admin/js/adminlte.js')); ?>"></script>

<?php echo $__env->yieldPushContent('scripts'); ?>

<?php echo $__env->make('components.dashboard_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\main.blade.php ENDPATH**/ ?>