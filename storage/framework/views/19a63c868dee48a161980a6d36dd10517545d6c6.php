
<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-sm-6 col-md-3">
        <label class="form-label small mb-1">Shop</label>
        <select name="shop" class="form-select form-select-sm">
            <option value="">All shops</option>
            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($shop->id); ?>" <?php echo e(((string) $shopId === (string) $shop->id) ? 'selected' : ''); ?>><?php echo e($shop->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="from" value="<?php echo e($from->toDateString()); ?>" class="form-control form-control-sm">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="to" value="<?php echo e($to->toDateString()); ?>" class="form-control form-control-sm">
    </div>
    <?php if(isset($search)): ?>
        <div class="col-sm-6 col-md-3">
            <label class="form-label small mb-1">Product</label>
            <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search product...">
        </div>
    <?php endif; ?>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button>
    </div>
</form>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/sales_revenue/_filters.blade.php ENDPATH**/ ?>