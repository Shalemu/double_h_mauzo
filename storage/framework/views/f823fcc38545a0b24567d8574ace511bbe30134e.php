<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $__env->yieldContent('title'); ?> - DOUBLE H COSMETICS Admin Panel</title>

    <?php echo $__env->make('components.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>

<body>

    
    <?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <main class="container-fluid" style="margin-top: 70px;">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/main.blade.php ENDPATH**/ ?>