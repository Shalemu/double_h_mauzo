<?php $__env->startSection('title', 'Manage Product'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('pages.products.index', [
    'products' => $products,
    'categories' => $categories,
    'units' => $units,
    'shops' => $shops ?? collect(),
    'isAdminTier' => $isAdminTier ?? false,
    'productsTotal' => $productsTotal ?? null,
    'productsDisplayLimit' => $productsDisplayLimit ?? null,
    'activeShop' => $activeShop ?? null,
    'globalMode' => $globalMode ?? false,
    'productFilter' => $productFilter ?? 'all',
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\page.blade.php ENDPATH**/ ?>