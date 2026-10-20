<?php

/*
|--------------------------------------------------------------------------
| Role Permissions
|--------------------------------------------------------------------------
| Every permission a role can be given, grouped by module for the Roles &
| Permissions screen (config/permission_sections.php puts the modules under
| headings).
|
| - Admin-panel permissions are enforced by PanelPermissionMiddleware using
|   the route map in config/panel_routes.php. The "admin" role (id 1) and
|   super users always have all of them.
| - "POS …" permissions are for staff POS logins, enforced on staff routes
|   with the `permission:<key>` middleware.
*/

return [
    // ---------------- Admin panel ----------------
    'Dashboard' => [
        'dashboard.view'           => 'View Dashboard',
    ],
    'Manage Business' => [
        'shops.view'               => 'List Shops',
        'shops.create'             => 'Create Shop',
        'shops.edit'               => 'Edit / Suspend Shop',
        'shops.delete'             => 'Delete Shop',
    ],
    'Stores' => [
        'stores.view'              => 'List Stores',
        'stores.create'            => 'Create Store',
        'stores.edit'              => 'Edit / Suspend Store',
        'stores.delete'            => 'Delete Store',
    ],
    'Human Resource' => [
        'staff.view'               => 'List Staff',
        'staff.create'             => 'Create Staff',
        'staff.edit'               => 'Edit Staff',
        'staff.delete'             => 'Delete Staff',
        'roles.manage'             => 'Manage Roles & Permissions',
    ],
    'Reported Issues' => [
        'issues.view'              => 'List Issues',
        'issues.resolve'           => 'Resolve Issues',
    ],
    'Point of Sale' => [
        'admin-pos.sell'           => 'Sell from Admin POS',
    ],
    'Sales' => [
        'sales.list'               => 'List Sales & Sale Items',
        'sales.export'             => 'Export Sales',
    ],
    'Sales Returns' => [
        'sales-returns.view'       => 'List Sales Returns',
        'sales-returns.create'     => 'Record Sales Return',
    ],
    'Credit & Customers' => [
        'customers.view'           => 'List Customers',
        'customers.payment'        => 'Record Customer Payment',
        'credit.view'              => 'View Credit (Customers & Suppliers)',
        'credit.supplier-pay'      => 'Pay Supplier Credit',
    ],
    'Revenue' => [
        'revenue.view'             => 'View Revenue & Profit',
    ],
    'Expenses' => [
        'expenses.view'            => 'List Expenses',
        'expenses.create'          => 'Create Expense',
        'expenses.edit'            => 'Edit Expense',
        'expenses.delete'          => 'Delete Expense',
    ],
    'Fixed Expenses' => [
        'fixed-expenses.view'      => 'List Fixed Expenses',
        'fixed-expenses.create'    => 'Create Fixed Expense',
        'fixed-expenses.edit'      => 'Edit Fixed Expense',
        'fixed-expenses.delete'    => 'Delete Fixed Expense',
    ],
    'Order Requests' => [
        'order-requests.view'      => 'List Order Requests',
        'order-requests.approve'   => 'Approve / Reject Orders',
        'order-requests.purchased' => 'Mark Orders Purchased',
    ],
    'Products' => [
        'products.list'            => 'List Products & Stock',
        'products.create'          => 'Create Product',
        'products.edit'            => 'Edit Product / Assign Shop',
        'products.delete'          => 'Delete Product',
        'products.import'          => 'Import & Export Products',
        'products.trash'           => 'Restore / Purge Deleted Products',
    ],
    'Categories, Brands & Units' => [
        'catalog.view'             => 'List',
        'catalog.create'           => 'Create',
        'catalog.edit'             => 'Edit',
        'catalog.delete'           => 'Delete',
    ],
    'Suppliers' => [
        'suppliers.view'           => 'List Suppliers',
        'suppliers.create'         => 'Create Supplier',
        'suppliers.edit'           => 'Edit Supplier',
        'suppliers.delete'         => 'Delete Supplier',
        'suppliers.products'       => 'View Supplier Products',
    ],
    'Price Manager' => [
        'prices.view'              => 'View Prices',
        'prices.edit'              => 'Change Prices',
    ],
    'Purchases' => [
        'purchases.view'           => 'List Purchases & Invoices',
        'purchases.create'         => 'Create Purchase',
    ],
    'Purchase Returns' => [
        'purchase-returns.view'    => 'List Purchase Returns',
        'purchase-returns.create'  => 'Record Purchase Return',
    ],
    'Stock Transfers' => [
        'transfers.view'           => 'List Stock Transfers',
        'transfers.create'         => 'Transfer Stock',
    ],
    'Distribution' => [
        'distributions.view'       => 'List Distributions',
        'distributions.create'     => 'Issue Stock to Distributor',
        'distributions.settle'     => 'Settle Distribution & Record Payments',
    ],
    'SMS' => [
        'sms.view'                 => 'View SMS & History',
        'sms.send'                 => 'Send SMS',
    ],

    // ---------------- Staff POS app ----------------
    'POS Selling' => [
        'pos.sell'                 => 'Sell from POS',
        'sales.view'               => 'List Own Sales',
    ],
    'POS Customers' => [
        'customers.manage'         => 'Manage Customers & Credit Payments',
    ],
    'POS Expenses' => [
        'expenses.manage'          => 'Create & Manage Expenses',
    ],
    'POS Stock Requests' => [
        'orders.create'            => 'Create Order Request',
        'orders.view'              => 'List Own Order Requests',
    ],
    'POS Products' => [
        'products.view'            => 'List Shop Products & Stock',
    ],
    'POS Report Issue' => [
        'issues.report'            => 'Report Issues to Admin',
    ],
];
