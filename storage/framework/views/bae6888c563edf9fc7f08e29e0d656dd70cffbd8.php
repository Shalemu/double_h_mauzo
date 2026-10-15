<?php $__env->startSection('title', 'POS'); ?>

<?php $__env->startSection('content'); ?>

<?php
    $staffUser = auth('staff')->user();
    $shop = $shop ?? $staffUser->shop;
    $products = $products ?? collect();
    $customers = $customers ?? collect();
    $productsTotal = $productsTotal ?? $products->count();
    $productsDisplayLimit = $productsDisplayLimit ?? null;
?>

<?php echo $__env->make('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => route('staff.sales.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('staff.customers.store'),
    'customerStoreShopId' => null,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\staff\index.blade.php ENDPATH**/ ?>