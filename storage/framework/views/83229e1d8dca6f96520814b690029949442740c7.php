<?php $__env->startSection('title', 'Purchases'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('pages.purchases.index', [
    'shop' => $shop,
    'purchases' => $purchases ?? collect(),
    'purchasesByDate' => $purchasesByDate ?? collect(),
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\purchases\page.blade.php ENDPATH**/ ?>