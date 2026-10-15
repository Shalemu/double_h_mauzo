<?php $__env->startSection('title', 'Sales on ' . \Carbon\Carbon::parse($date)->format('d M Y')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('pages.sales.detail', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/sales/detail_page.blade.php ENDPATH**/ ?>