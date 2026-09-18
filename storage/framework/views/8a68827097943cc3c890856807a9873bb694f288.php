<?php $__env->startSection('title', 'POS — ' . $shop->name); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2" style="max-width: 1500px; margin: auto;">
        <h5 class="mb-0"><i class="bi bi-cart-check me-2 text-success"></i>POS: <?php echo e($shop->name); ?></h5>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="<?php echo e(route('admin.pos.index')); ?>" class="d-flex align-items-center gap-2">
                <label class="mb-0 small text-muted">Switch shop</label>
                <select name="shop" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 200px;">
                    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php echo e($s->id === $shop->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>

            <a href="<?php echo e(route('dashboard.admin')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

</div>

<?php echo $__env->make('components.pos_core', [
    'shop' => $shop,
    'products' => $products,
    'customers' => $customers,
    'productsTotal' => $productsTotal,
    'productsDisplayLimit' => $productsDisplayLimit,
    'checkoutUrl' => route('admin.pos.checkout', ['shop' => $shop->id]),
    'customerStoreUrl' => route('admin.pos.customers.store'),
    'customerStoreShopId' => $shop->id,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/admin/pos.blade.php ENDPATH**/ ?>