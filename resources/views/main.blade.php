<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - DOUBLE H COSMETICS Admin Panel</title>

    @include('components.header')

    <link rel="stylesheet" href="{{ asset('admin/css/adminlte.css') }}">

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
    </style>

    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

{{-- GLOBAL PAGE-LOAD PROGRESS BAR --}}
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
    @include('components.app_navbar')
    @include('components.app_sidebar')

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-3">
                    <div class="col-sm-6">
                        <h3 class="mb-0">@yield('title')</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item"><a href="{{ Auth::guard('staff')->check() ? route('staff.dashboard') : route('dashboard.admin') }}">Home</a></li>
                            <li class="breadcrumb-item active">@yield('title')</li>
                        </ol>
                    </div>
                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </main>

    @include('components.footer')
</div>

<!-- Unit / Category quick-add modals (shared globally, was duplicated per-page before) -->
@include('components.quick_add_modals')

<script src="{{ asset('admin/js/adminlte.js') }}"></script>

@stack('scripts')

@include('components.dashboard_scripts')

</body>
</html>
