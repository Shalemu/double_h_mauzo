<?php

/*
|--------------------------------------------------------------------------
| Admin-panel route → permission map
|--------------------------------------------------------------------------
| PanelPermissionMiddleware checks the signed-in admin-panel user against
| this map (route name patterns, `*` wildcard, first match wins). A list
| means "any of these". Routes not listed (profile, notifications, shop
| switcher…) only need a panel login.
*/

return [
    'dashboard.admin'               => 'dashboard.view',

    // Manage Business
    'dashboard.shop'                => 'shops.view',
    'dashboard.shop.show'           => 'shops.view',
    'shops.store'                   => 'shops.create',
    'shops.update'                  => 'shops.edit',
    'shops.toggleStatus'            => 'shops.edit',
    'shops.destroy'                 => 'shops.delete',

    // Human Resource
    'staff.manage.index'            => 'staff.view',
    'staff.manage.edit'             => 'staff.view',
    'staff.manage.store'            => 'staff.create',
    'staff.manage.update'           => 'staff.edit',
    'staff.manage.destroy'          => 'staff.delete',
    'dashboard.role'                => 'roles.manage',
    'dashboard.roles.*'             => 'roles.manage',

    // Reported issues
    'admin.issues'                  => 'issues.view',
    'admin.feedback.resolve'        => 'issues.resolve',

    // Sales & Revenue
    'admin.pos.*'                   => 'admin-pos.sell',
    'admin.sales.index'             => 'sales.list',
    'admin.sales.items'             => 'sales.list',
    'admin.sales.show'              => 'sales.list',
    'admin.sales.detail'            => 'sales.list',
    'sales.index'                   => 'sales.list',
    'sales.show'                    => 'sales.list',
    'transactions.*'                => 'sales.list',
    'sales.export.*'                => 'sales.export',
    'returns.sales'                 => 'sales-returns.view',
    'admin.sales_returns.detail'    => 'sales-returns.view',
    'returns.saleItemLookup'        => 'sales-returns.create',
    'returns.sales.create'          => 'sales-returns.create',
    'returns.sales.store'           => 'sales-returns.create',
    'sales-returns.store'           => 'sales-returns.create',
    'customers.index'               => 'customers.view',
    'customers.show'                => 'customers.view',
    'customers.recordPayment'       => 'customers.payment',
    'admin.credit'                  => 'credit.view',
    'purchases.credit'              => 'credit.view',
    'purchases.deposit'             => 'credit.supplier-pay',
    'admin.revenue'                 => 'revenue.view',
    'admin.expenses'                => 'expenses.view',
    'expenses.index'                => 'expenses.view',
    'expenses.detail'               => 'expenses.view',
    'expenses.create'               => 'expenses.create',
    'expenses.store'                => 'expenses.create',
    'expenses.edit'                 => 'expenses.edit',
    'expenses.update'               => 'expenses.edit',
    'expenses.destroy'              => 'expenses.delete',
    'admin.fixedExpenses'           => 'fixed-expenses.view',
    'fixed-expenses.index'          => 'fixed-expenses.view',
    'fixed-expenses.store'          => 'fixed-expenses.create',
    'fixed-expenses.edit'           => 'fixed-expenses.edit',
    'fixed-expenses.update'         => 'fixed-expenses.edit',
    'fixed-expenses.destroy'        => 'fixed-expenses.delete',

    // Procurement
    'admin.orders.index'            => 'order-requests.view',
    'admin.orders.show'             => 'order-requests.view',
    'admin.shops.orders'            => 'order-requests.view',
    // The controller checks approve vs purchased for the exact status.
    'admin.orders.updateStatus'     => ['order-requests.approve', 'order-requests.purchased'],
    'products.trash'                => 'products.trash',
    'products.restore'              => 'products.trash',
    'products.forceDelete'          => 'products.trash',
    'products.export.*'             => 'products.import',
    'products.download.template'    => 'products.import',
    'products.import.excel'         => 'products.import',
    'products.create'               => 'products.create',
    'products.store'                => 'products.create',
    'products.edit'                 => 'products.edit',
    'products.update'               => 'products.edit',
    'products.bulkAssignShop'       => 'products.edit',
    'products.destroy'              => 'products.delete',
    'products.*'                    => 'products.list',
    'categories.index'              => 'catalog.view',
    'units.index'                   => 'catalog.view',
    'brands.index'                  => 'catalog.view',
    'categories.store'              => 'catalog.create',
    'units.store'                   => 'catalog.create',
    'brands.store'                  => 'catalog.create',
    'categories.update'             => 'catalog.edit',
    'units.update'                  => 'catalog.edit',
    'brands.update'                 => 'catalog.edit',
    'categories.destroy'            => 'catalog.delete',
    'units.destroy'                 => 'catalog.delete',
    'brands.destroy'                => 'catalog.delete',
    'suppliers.index'               => 'suppliers.view',
    'suppliers.create'              => 'suppliers.create',
    'suppliers.store'               => 'suppliers.create',
    'suppliers.update'              => 'suppliers.edit',
    'suppliers.destroy'             => 'suppliers.delete',
    'procurement.supplierProducts'  => 'suppliers.products',
    'procurement.prices'            => 'prices.view',
    'procurement.prices.update'     => 'prices.edit',
    'purchases.create'              => 'purchases.create',
    'purchases.store'               => 'purchases.create',
    'purchases.new_product'         => 'purchases.create',
    'purchases.store_new_product'   => 'purchases.create',
    'purchases.*'                   => 'purchases.view',
    'invoices.*'                    => 'purchases.view',
    'returns.purchases'             => 'purchase-returns.view',
    'admin.purchase_returns.detail' => 'purchase-returns.view',
    'returns.purchaseItemLookup'    => 'purchase-returns.create',
    'purchase-returns.store'        => 'purchase-returns.create',

    // Inventory
    'stock-transfers.index'         => 'transfers.view',
    'stock-transfers.*'             => 'transfers.create',
];
