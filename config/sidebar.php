<?php

/*
|--------------------------------------------------------------------------
| Sidebar menu
|--------------------------------------------------------------------------
|
| Rendered by resources/views/components/app_sidebar.blade.php.
| 'admin' is shown to web users, 'staff' to the staff (POS) guard.
|
| Each menu is a list of sections: ['title' => ?string, 'items' => [...]].
| A section with no visible items is hidden, and every section after the
| first gets a divider.
|
| Item keys:
|   title       Link text.
|   route       Route name.
|   icon        Bootstrap icon class (top-level items only).
|   patterns    Route name pattern(s) that highlight the item (default: route).
|   params      Route parameters, as attribute names of the signed-in user
|               (e.g. ['shop_id']).
|   badge       Counter key: 'pending_orders' or 'open_issues'.
|   permission  Staff: permission(s) required, any one is enough (default: none).
|               Admin: defaults to the route's entry in config/panel_routes.php.
|   super_user  true = only super users see it.
|   children    Sub-links; the group is hidden when none are visible and opens
|               when one of them is active.
|
*/

return [

    'admin' => [

        [
            'items' => [
                ['title' => 'Dashboard', 'route' => 'dashboard.admin', 'icon' => 'bi-speedometer2'],
            ],
        ],

        [
            'title' => 'Administration',
            'items' => [
                [
                    'title' => 'Manage Business',
                    'icon' => 'bi-shop',
                    'children' => [
                        ['title' => 'All Shops', 'route' => 'dashboard.shop', 'patterns' => ['dashboard.shop', 'dashboard.shop.show']],
                        ['title' => 'All Stores', 'route' => 'stores.index', 'patterns' => 'stores.*'],
                    ],
                ],
                [
                    'title' => 'Human Resource',
                    'icon' => 'bi-person-vcard',
                    'children' => [
                        ['title' => 'Staff', 'route' => 'staff.manage.index', 'patterns' => 'staff.manage.*'],
                        ['title' => 'Roles & Permissions', 'route' => 'dashboard.role', 'patterns' => ['dashboard.role', 'dashboard.roles.*']],
                    ],
                ],
                ['title' => 'Reported Issues', 'route' => 'admin.issues', 'icon' => 'bi-exclamation-circle', 'badge' => 'open_issues'],
                ['title' => 'SMS', 'route' => 'sms.index', 'icon' => 'bi-chat-dots', 'patterns' => 'sms.*'],
                ['title' => 'User Management', 'route' => 'users.manage.index', 'icon' => 'bi-shield-lock', 'patterns' => 'users.manage.*', 'permission' => null, 'super_user' => true],
            ],
        ],

        [
            'title' => 'Sales & Revenue',
            'items' => [
                ['title' => 'POS', 'route' => 'admin.pos.index', 'icon' => 'bi-cash-coin', 'patterns' => 'admin.pos.*'],
                ['title' => 'Sales', 'route' => 'admin.sales.index', 'icon' => 'bi-receipt', 'patterns' => ['admin.sales.index', 'admin.sales.detail']],
                ['title' => 'Sale Items', 'route' => 'admin.sales.items', 'icon' => 'bi-basket'],
                ['title' => 'Sales Returns', 'route' => 'returns.sales', 'icon' => 'bi-arrow-return-left', 'patterns' => ['returns.sales', 'admin.sales_returns.detail']],
                ['title' => 'Credit', 'route' => 'admin.credit', 'icon' => 'bi-credit-card'],
                ['title' => 'Customers', 'route' => 'customers.index', 'icon' => 'bi-people', 'patterns' => 'customers.*'],
                ['title' => 'Revenue & Profit', 'route' => 'admin.revenue', 'icon' => 'bi-graph-up-arrow'],
                ['title' => 'Expenses', 'route' => 'admin.expenses', 'icon' => 'bi-wallet2', 'patterns' => ['admin.expenses', 'expenses.*']],
                ['title' => 'Fixed Expenses', 'route' => 'admin.fixedExpenses', 'icon' => 'bi-calendar2-check', 'patterns' => ['admin.fixedExpenses', 'fixed-expenses.*']],
            ],
        ],

        [
            'title' => 'Procurement',
            'items' => [
                ['title' => 'Order Requests', 'route' => 'admin.orders.index', 'icon' => 'bi-bag-check', 'patterns' => 'admin.orders.*', 'badge' => 'pending_orders'],
                // Stock filters (in stock, running out, expiring...) are reached from the dashboard cards and keep Product Management highlighted.
                ['title' => 'Product Management', 'route' => 'products.index', 'icon' => 'bi-box-seam', 'patterns' => [
                    'products.index', 'products.create', 'products.edit', 'products.in-stock', 'products.running-out',
                    'products.expiring', 'products.expired', 'products.out-of-stock', 'products.finished',
                ]],
                ['title' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'bi-truck', 'patterns' => 'suppliers.*'],
                ['title' => 'Supplier Products', 'route' => 'procurement.supplierProducts', 'icon' => 'bi-diagram-3'],
                ['title' => 'Price Manager', 'route' => 'procurement.prices', 'icon' => 'bi-tags', 'patterns' => 'procurement.prices*'],
                ['title' => 'Purchases', 'route' => 'purchases.index', 'icon' => 'bi-cart-check', 'patterns' => 'purchases.*'],
                ['title' => 'Purchase Returns', 'route' => 'returns.purchases', 'icon' => 'bi-box-arrow-left', 'patterns' => ['returns.purchases', 'admin.purchase_returns.detail']],
            ],
        ],

        [
            'title' => 'Inventory',
            'items' => [
                ['title' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'icon' => 'bi-arrow-left-right', 'patterns' => 'stock-transfers.*'],
                ['title' => 'Distribution', 'route' => 'distributions.index', 'icon' => 'bi-truck-flatbed', 'patterns' => 'distributions.*'],
                ['title' => 'Deleted Products', 'route' => 'products.trash', 'icon' => 'bi-trash'],
            ],
        ],

    ],

    'staff' => [

        [
            'title' => 'Sales & Revenue',
            'items' => [
                ['title' => 'POS', 'route' => 'staff.dashboard', 'icon' => 'bi-cash-coin'],
                ['title' => 'My Sales', 'route' => 'staff.sales.index', 'icon' => 'bi-receipt', 'patterns' => 'staff.sales.*', 'params' => ['shop_id'], 'permission' => 'sales.view'],
                ['title' => 'Customers & Credit', 'route' => 'staff.customers.manage', 'icon' => 'bi-people', 'patterns' => 'staff.customers.*', 'permission' => 'customers.manage'],
                ['title' => 'Expenses', 'route' => 'staff.expenses.index', 'icon' => 'bi-wallet2', 'patterns' => 'staff.expenses.*', 'params' => ['shop_id'], 'permission' => 'expenses.manage'],
            ],
        ],

        [
            'title' => 'Procurement',
            'items' => [
                ['title' => 'Request Stock', 'route' => 'staff.orders.create', 'icon' => 'bi-cart-plus', 'permission' => 'orders.create'],
                ['title' => 'My Order Requests', 'route' => 'staff.orders.index', 'icon' => 'bi-bag-check', 'patterns' => ['staff.orders.index', 'staff.orders.show'], 'permission' => 'orders.view'],
            ],
        ],

        [
            'title' => 'Inventory',
            'items' => [
                ['title' => 'Shop Items', 'route' => 'staff.products.index', 'icon' => 'bi-box-seam', 'patterns' => 'staff.products.*', 'permission' => 'products.view'],
            ],
        ],

        [
            'items' => [
                ['title' => 'Report Issue', 'route' => 'staff.report.issue.index', 'icon' => 'bi-exclamation-circle', 'patterns' => 'staff.report.issue.*', 'permission' => 'issues.report'],
            ],
        ],

    ],

];
