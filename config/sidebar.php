<?php

/*
|--------------------------------------------------------------------------
| Sidebar menu
|--------------------------------------------------------------------------
|
| Rendered by resources/views/components/app_sidebar.blade.php.
| 'admin' is shown to admin accounts, 'staff' to staff accounts (user_type 'staff').
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
|   badge       Counter key: 'pending_orders', 'open_issues' or 'pending_purchases'
|               (also on sub-links).
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

        // Ordered by how often the work happens: selling every day first,
        // then stock, buying, money, and setup/admin last.

        [
            'title' => 'Sales',
            'items' => [
                ['title' => 'POS', 'route' => 'admin.pos.index', 'icon' => 'bi-cash-coin', 'patterns' => 'admin.pos.*'],
                ['title' => 'Sales', 'route' => 'admin.sales.index', 'icon' => 'bi-receipt', 'patterns' => ['admin.sales.index', 'admin.sales.detail']],
                ['title' => 'Sale Items', 'route' => 'admin.sales.items', 'icon' => 'bi-basket'],
                ['title' => 'Sales Returns', 'route' => 'returns.sales', 'icon' => 'bi-arrow-return-left', 'patterns' => ['returns.sales', 'admin.sales_returns.detail']],
                ['title' => 'Customers', 'route' => 'customers.index', 'icon' => 'bi-people', 'patterns' => 'customers.*'],
                ['title' => 'Credit', 'route' => 'admin.credit', 'icon' => 'bi-credit-card'],
            ],
        ],

        [
            'title' => 'Inventory',
            'items' => [
                // Stock filters (in stock, running out, expiring...) are reached from the dashboard cards and keep Product Management highlighted.
                ['title' => 'Product Management', 'route' => 'products.index', 'icon' => 'bi-box-seam', 'patterns' => [
                    'products.index', 'products.create', 'products.edit', 'products.in-stock', 'products.running-out',
                    'products.expiring', 'products.expired', 'products.out-of-stock', 'products.finished',
                ]],
                ['title' => 'Order Requests', 'route' => 'admin.orders.index', 'icon' => 'bi-bag-check', 'patterns' => 'admin.orders.*', 'badge' => 'pending_orders'],
                ['title' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'icon' => 'bi-arrow-left-right', 'patterns' => 'stock-transfers.*'],
                ['title' => 'Distribution', 'route' => 'distributions.index', 'icon' => 'bi-truck-flatbed', 'patterns' => 'distributions.*'],
                ['title' => 'Deleted Products', 'route' => 'products.trash', 'icon' => 'bi-trash'],
            ],
        ],

        [
            'title' => 'Procurement',
            'items' => [
                [
                    'title' => 'Purchases',
                    'icon' => 'bi-cart-check',
                    'children' => [
                        ['title' => 'Purchase List', 'route' => 'purchases.index', 'patterns' => [
                            'purchases.index', 'purchases.detail', 'purchases.history', 'purchases.print', 'purchases.invoice_*',
                        ]],
                        ['title' => 'New Purchase Order', 'route' => 'purchases.create'],
                        ['title' => 'New Item Purchase', 'route' => 'purchases.new_product'],
                        ['title' => 'Receive Orders', 'route' => 'purchases.receiving', 'patterns' => ['purchases.receiving', 'purchases.receive'], 'badge' => 'pending_purchases'],
                        ['title' => 'Purchase Returns', 'route' => 'returns.purchases', 'patterns' => ['returns.purchases*', 'admin.purchase_returns.detail']],
                    ],
                ],
                ['title' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'bi-truck', 'patterns' => 'suppliers.*'],
                ['title' => 'Supplier Products', 'route' => 'procurement.supplierProducts', 'icon' => 'bi-diagram-3'],
                ['title' => 'Price Manager', 'route' => 'procurement.prices', 'icon' => 'bi-tags', 'patterns' => 'procurement.prices*'],
            ],
        ],

        [
            'title' => 'Finance',
            'items' => [
                ['title' => 'Expenses', 'route' => 'admin.expenses', 'icon' => 'bi-wallet2', 'patterns' => ['admin.expenses', 'expenses.*']],
                ['title' => 'Fixed Expenses', 'route' => 'admin.fixedExpenses', 'icon' => 'bi-calendar2-check', 'patterns' => ['admin.fixedExpenses', 'fixed-expenses.*']],
                ['title' => 'Revenue & Profit', 'route' => 'admin.revenue', 'icon' => 'bi-graph-up-arrow'],
            ],
        ],

        [
            'title' => 'Administration',
            'items' => [
                ['title' => 'Reported Issues', 'route' => 'admin.issues', 'icon' => 'bi-exclamation-circle', 'badge' => 'open_issues'],
                ['title' => 'SMS', 'route' => 'sms.index', 'icon' => 'bi-chat-dots', 'patterns' => 'sms.*'],
                [
                    'title' => 'Human Resource',
                    'icon' => 'bi-person-vcard',
                    'children' => [
                        ['title' => 'Staff', 'route' => 'staff.manage.index', 'patterns' => 'staff.manage.*'],
                        ['title' => 'Roles & Permissions', 'route' => 'dashboard.role', 'patterns' => ['dashboard.role', 'dashboard.roles.*']],
                    ],
                ],
                [
                    'title' => 'Manage Business',
                    'icon' => 'bi-shop',
                    'children' => [
                        ['title' => 'All Shops', 'route' => 'dashboard.shop', 'patterns' => ['dashboard.shop', 'dashboard.shop.show']],
                        ['title' => 'All Stores', 'route' => 'stores.index', 'patterns' => 'stores.*'],
                    ],
                ],
                ['title' => 'User Management', 'route' => 'users.manage.index', 'icon' => 'bi-shield-lock', 'patterns' => 'users.manage.*', 'permission' => null, 'super_user' => true],
            ],
        ],

        [
            'title' => 'Configuration',
            'items' => [
                [
                    'title' => 'Settings',
                    'icon' => 'bi-gear',
                    'children' => [
                        ['title' => 'Business Profile', 'route' => 'settings.business', 'patterns' => 'settings.business*'],
                        ['title' => 'Payment Methods', 'route' => 'settings.payment-methods.index', 'patterns' => 'settings.payment-methods.*'],
                        ['title' => 'Financial Years', 'route' => 'settings.financial-years.index', 'patterns' => 'settings.financial-years.*'],
                    ],
                ],
            ],
        ],

    ],

    'staff' => [

        [
            'title' => 'Sales',
            'items' => [
                ['title' => 'POS', 'route' => 'staff.dashboard', 'icon' => 'bi-cash-coin'],
                ['title' => 'My Sales', 'route' => 'staff.sales.index', 'icon' => 'bi-receipt', 'patterns' => 'staff.sales.*', 'params' => ['shop_id'], 'permission' => 'sales.view'],
                ['title' => 'Customers & Credit', 'route' => 'staff.customers.manage', 'icon' => 'bi-people', 'patterns' => 'staff.customers.*', 'permission' => 'customers.manage'],
            ],
        ],

        [
            'title' => 'Stock',
            'items' => [
                ['title' => 'Shop Items', 'route' => 'staff.products.index', 'icon' => 'bi-box-seam', 'patterns' => 'staff.products.*', 'permission' => 'products.view'],
                ['title' => 'Request Stock', 'route' => 'staff.orders.create', 'icon' => 'bi-cart-plus', 'permission' => 'orders.create'],
                ['title' => 'My Order Requests', 'route' => 'staff.orders.index', 'icon' => 'bi-bag-check', 'patterns' => ['staff.orders.index', 'staff.orders.show'], 'permission' => 'orders.view'],
            ],
        ],

        [
            'title' => 'Other',
            'items' => [
                ['title' => 'Expenses', 'route' => 'staff.expenses.index', 'icon' => 'bi-wallet2', 'patterns' => 'staff.expenses.*', 'params' => ['shop_id'], 'permission' => 'expenses.manage'],
                ['title' => 'Report Issue', 'route' => 'staff.report.issue.index', 'icon' => 'bi-exclamation-circle', 'patterns' => 'staff.report.issue.*', 'permission' => 'issues.report'],
            ],
        ],

    ],

];
