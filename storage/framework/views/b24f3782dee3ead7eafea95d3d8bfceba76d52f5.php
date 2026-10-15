<?php $__env->startSection('title', 'Unit of Measure'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-rulers me-2"></i>Unit of Measure</h5>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Products</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#unitModal" data-mode="create">
                    <i class="bi bi-plus-lg"></i> New Unit
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3" style="max-width:420px">
                <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search units..." aria-label="Search units">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th class="text-start">Name</th><th>Short name</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($units->firstItem() + $loop->index); ?></td>
                                <td><?php echo e($unit->name); ?></td>
                                <td class="text-center"><?php echo e($unit->short_name); ?></td>
                                <td class="text-center"><?php echo e(number_format($unit->products_count)); ?></td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#unitModal"
                                            data-mode="edit" data-action="<?php echo e(route('units.update', $unit->id)); ?>"
                                            data-name="<?php echo e($unit->name); ?>" data-short-name="<?php echo e($unit->short_name); ?>"
                                            aria-label="Edit <?php echo e($unit->name); ?>"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?php echo e(route('units.destroy', $unit->id)); ?>" class="d-inline"
                                          onsubmit="return confirm('Delete unit <?php echo e(addslashes($unit->name)); ?>?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($unit->name); ?>"
                                                <?php echo e(($unit->products_count > 0) ? 'disabled' : ''); ?> title="<?php echo e($unit->products_count ? 'In use by products' : 'Delete'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No units found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted"><?php if($units->total()): ?> Showing <?php echo e($units->firstItem()); ?>–<?php echo e($units->lastItem()); ?> of <?php echo e(number_format($units->total())); ?> <?php endif; ?></small>
                <?php echo e($units->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('units.store')); ?>" data-store="<?php echo e(route('units.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="unitModalTitle">New Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="unitName">Name</label>
                    <input type="text" name="name" id="unitName" class="form-control" maxlength="100" placeholder="e.g. Piece, Box, Litre" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="unitShort">Short name</label>
                    <input type="text" name="short_name" id="unitShort" class="form-control" maxlength="10" placeholder="e.g. pc, bx, L" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('unitModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const form = this.querySelector('form');
    const editing = trigger && trigger.dataset.mode === 'edit';
    form.action = editing ? trigger.dataset.action : form.dataset.store;
    form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
    this.querySelector('.modal-title').textContent = editing ? 'Edit Unit' : 'New Unit';
    form.querySelector('[name=name]').value = editing ? trigger.dataset.name : '';
    form.querySelector('[name=short_name]').value = editing ? trigger.dataset.shortName : '';
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/units/index.blade.php ENDPATH**/ ?>