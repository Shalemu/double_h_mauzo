<?php $__env->startSection('title', 'Brands'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-award me-2"></i>Brands</h5>
                <small class="text-muted">Renaming a brand also renames it on every product that uses it.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Products</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal" data-mode="create">
                    <i class="bi bi-plus-lg"></i> New Brand
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3" style="max-width:420px">
                <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search brands..." aria-label="Search brands">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th class="text-start">Name</th><th class="text-start">Description</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($brands->firstItem() + $loop->index); ?></td>
                                <td><?php echo e($brand->name); ?></td>
                                <td><?php echo e($brand->description ?: '—'); ?></td>
                                <td class="text-center"><?php echo e(number_format($brand->products_count)); ?></td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#brandModal"
                                            data-mode="edit" data-action="<?php echo e(route('brands.update', $brand->id)); ?>"
                                            data-name="<?php echo e($brand->name); ?>" data-description="<?php echo e($brand->description); ?>"
                                            aria-label="Edit <?php echo e($brand->name); ?>"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?php echo e(route('brands.destroy', $brand->id)); ?>" class="d-inline"
                                          onsubmit="return confirm('Delete brand <?php echo e(addslashes($brand->name)); ?>?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($brand->name); ?>"
                                                <?php echo e(($brand->products_count > 0) ? 'disabled' : ''); ?> title="<?php echo e($brand->products_count ? 'In use by products' : 'Delete'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No brands found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted"><?php if($brands->total()): ?> Showing <?php echo e($brands->firstItem()); ?>–<?php echo e($brands->lastItem()); ?> of <?php echo e(number_format($brands->total())); ?> <?php endif; ?></small>
                <?php echo e($brands->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('brands.store')); ?>" data-store="<?php echo e(route('brands.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="brandModalTitle">New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="brandName">Name</label>
                    <input type="text" name="name" id="brandName" class="form-control" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="brandDescription">Description (optional)</label>
                    <textarea name="description" id="brandDescription" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<?php echo $__env->make('pages.brands._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\brands\index.blade.php ENDPATH**/ ?>