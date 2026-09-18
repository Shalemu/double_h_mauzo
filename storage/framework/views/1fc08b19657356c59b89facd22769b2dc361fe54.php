<?php $__env->startSection('title', 'POS — Choose Shop'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="border rounded-4 shadow-sm bg-white p-4" style="max-width: 1100px; margin: auto;">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-3 border-bottom">
                    <h4 class="mb-0"><i class="bi bi-cart-check me-2 text-success"></i>POS — Choose a Shop</h4>
                    <a href="<?php echo e(route('dashboard.admin')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <p class="text-muted mb-4">
                    Pick which shop you're selling for. Stock, prices, and the sale record will all belong to that shop.
                </p>

                <?php if($shops->isEmpty()): ?>
                    <div class="alert alert-warning">No shops exist yet. Create a shop first.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-4 col-sm-6">
                                <a href="<?php echo e(route('admin.pos.index', ['shop' => $shop->id])); ?>"
                                   class="text-decoration-none">
                                    <div class="card h-100 shadow-sm shop-pick-card">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-1"><?php echo e($shop->name); ?></h6>
                                            <small class="text-muted"><?php echo e($shop->location); ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
.shop-pick-card {
    border: 1px solid #e5e7eb;
    transition: 0.15s;
    color: #212529;
}
.shop-pick-card:hover {
    border-color: #f97316;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
    transform: translateY(-2px);
}
</style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/admin/pos_select_shop.blade.php ENDPATH**/ ?>