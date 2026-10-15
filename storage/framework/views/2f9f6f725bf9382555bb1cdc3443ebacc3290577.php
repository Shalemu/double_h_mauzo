<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Categories</h5>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Products</a>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-mode="create">
                    <i class="bi bi-plus-lg"></i> New Category
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 mb-3" style="max-width:420px">
                <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search categories..." aria-label="Search categories">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th class="text-start">Category</th><th class="text-start">Description</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $__currentLoopData = [$category, ...$category->children]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isChild = $row->parent_id !== null;
                                    $count = $isChild ? $row->subcategory_products_count : $row->products_count;
                                ?>
                                <tr class="<?php echo e($isChild ? '' : 'table-light'); ?>">
                                    <td class="<?php echo e($isChild ? 'ps-5' : 'fw-semibold'); ?>">
                                        <?php if($isChild): ?><i class="bi bi-arrow-return-right text-muted me-1"></i><?php endif; ?>
                                        <?php echo e($row->name); ?>

                                    </td>
                                    <td><?php echo e($row->description ?: '—'); ?></td>
                                    <td class="text-center"><?php echo e(number_format($count ?? 0)); ?></td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"
                                                data-mode="edit" data-action="<?php echo e(route('categories.update', $row->id)); ?>"
                                                data-name="<?php echo e($row->name); ?>" data-description="<?php echo e($row->description); ?>"
                                                data-parent="<?php echo e($row->parent_id); ?>" data-id="<?php echo e($row->id); ?>"
                                                aria-label="Edit <?php echo e($row->name); ?>"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="<?php echo e(route('categories.destroy', $row->id)); ?>" class="d-inline"
                                              onsubmit="return confirm('Delete category <?php echo e(addslashes($row->name)); ?>?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($row->name); ?>"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No categories found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted"><?php if($categories->total()): ?> Showing <?php echo e($categories->firstItem()); ?>–<?php echo e($categories->lastItem()); ?> of <?php echo e(number_format($categories->total())); ?> main categories <?php endif; ?></small>
                <?php echo e($categories->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('categories.store')); ?>" data-store="<?php echo e(route('categories.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="categoryName">Name</label>
                    <input type="text" name="name" id="categoryName" class="form-control" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="categoryParent">Parent category</label>
                    <select name="parent_id" id="categoryParent" class="form-select">
                        <option value="">None — this is a main category</option>
                        <?php $__currentLoopData = $parentCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($parent->id); ?>"><?php echo e($parent->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="form-text">Pick a parent to make this a subcategory.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="categoryDescription">Description (optional)</label>
                    <textarea name="description" id="categoryDescription" class="form-control" rows="2"></textarea>
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
document.getElementById('categoryModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const form = this.querySelector('form');
    const editing = trigger && trigger.dataset.mode === 'edit';
    const parent = form.querySelector('[name=parent_id]');
    form.action = editing ? trigger.dataset.action : form.dataset.store;
    form.querySelector('[name=_method]').value = editing ? 'PUT' : 'POST';
    this.querySelector('.modal-title').textContent = editing ? 'Edit Category' : 'New Category';
    form.querySelector('[name=name]').value = editing ? trigger.dataset.name : '';
    form.querySelector('[name=description]').value = editing ? (trigger.dataset.description || '') : '';
    parent.value = editing ? (trigger.dataset.parent || '') : '';
    // A category can't be its own parent.
    Array.from(parent.options).forEach(o => { o.disabled = editing && o.value === trigger.dataset.id; });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\categories\index.blade.php ENDPATH**/ ?>