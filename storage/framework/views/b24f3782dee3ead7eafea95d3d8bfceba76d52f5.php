<?php $__env->startSection('title', 'Unit of Measure'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('pages.units._index_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
                        <tr><th>#</th><th class="text-start">Name</th><th>Short name</th><th>Allow decimal</th><th>Products</th><th style="width:120px">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($units->firstItem() + $loop->index); ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo e($unit->name); ?></div>
                                    <?php if($unit->conversion): ?>
                                        <span class="unit-conversion"><i class="bi bi-arrow-repeat"></i> <?php echo e($unit->conversion); ?></span>
                                    <?php elseif($unit->multiples_count): ?>
                                        <span class="unit-base-tag"><i class="bi bi-diagram-3"></i> Base unit</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary font-monospace"><?php echo e($unit->short_name); ?></span></td>
                                <td class="text-center"><?php echo e($unit->allow_decimal ? 'Yes' : 'No'); ?></td>
                                <td class="text-center"><?php echo e(number_format($unit->products_count)); ?></td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#unitModal"
                                            data-mode="edit" data-action="<?php echo e(route('units.update', $unit->id)); ?>"
                                            data-id="<?php echo e($unit->id); ?>"
                                            data-name="<?php echo e($unit->name); ?>" data-short-name="<?php echo e($unit->short_name); ?>"
                                            data-allow-decimal="<?php echo e($unit->allow_decimal ? 1 : 0); ?>"
                                            data-base-unit="<?php echo e($unit->base_unit_id); ?>" data-multiplier="<?php echo e($unit->multiplier); ?>"
                                            data-is-base="<?php echo e($unit->multiples_count ? 1 : 0); ?>"
                                            aria-label="Edit <?php echo e($unit->name); ?>"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" action="<?php echo e(route('units.destroy', $unit->id)); ?>" class="d-inline"
                                          onsubmit="return confirm('Delete unit <?php echo e(addslashes($unit->name)); ?>?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <?php $locked = $unit->products_count > 0 || $unit->multiples_count > 0; ?>
                                        <button class="btn btn-sm btn-outline-danger" aria-label="Delete <?php echo e($unit->name); ?>"
                                                <?php echo e($locked ? 'disabled' : ''); ?>

                                                title="<?php echo e($unit->products_count ? 'In use by products' : ($unit->multiples_count ? 'Other units are multiples of it' : 'Delete')); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No units found.</td></tr>
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
    <div class="modal-dialog modal-dialog-centered unit-dialog">
        <form class="modal-content unit-modal" method="POST" action="<?php echo e(route('units.store')); ?>" data-store="<?php echo e(route('units.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            
            <input type="hidden" name="_edit_action" value="">

            <div class="modal-header unit-modal-header">
                <div class="unit-modal-icon"><i class="bi bi-rulers"></i></div>
                <h5 class="modal-title mb-0" id="unitModalTitle">Register Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label d-block" for="unitName">Unit Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="unitName" class="form-control" maxlength="100" value="<?php echo e(old('name')); ?>" placeholder="e.g. Pieces, Dozen, Carton, Kilograms" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label d-block" for="unitShort">Short Name <span class="text-danger">*</span></label>
                        <input type="text" name="short_name" id="unitShort" class="form-control" maxlength="10" value="<?php echo e(old('short_name')); ?>" placeholder="e.g. pcs, dzn, ctn, kg" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label d-block" id="decimalLabel" for="decimalNo">Allow Decimal? <span class="text-danger">*</span></label>
                        <div class="unit-segment" role="radiogroup" aria-labelledby="decimalLabel">
                            <input type="radio" class="btn-check" name="allow_decimal" id="decimalNo" value="0" <?php echo e(old('allow_decimal', '0') == '0' ? 'checked' : ''); ?>>
                            <label for="decimalNo">No</label>
                            <input type="radio" class="btn-check" name="allow_decimal" id="decimalYes" value="1" <?php echo e(old('allow_decimal') == '1' ? 'checked' : ''); ?>>
                            <label for="decimalYes">Yes</label>
                        </div>
                    </div>
                </div>

                <div class="unit-multiple">
                    <div class="form-check form-switch m-0 unit-switch">
                        <input type="hidden" name="is_multiple" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_multiple" value="1" id="isMultiple" <?php echo e(old('is_multiple') ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="isMultiple">Register as a multiple of other unit?</label>
                    </div>
                    <div class="unit-note" id="isBaseNote" hidden>
                        <i class="bi bi-info-circle"></i> Other units are multiples of this one, so it must stay a base unit.
                    </div>

                    <div class="unit-equation" id="multipleRow" hidden>
                        <span class="unit-eq-one">1</span>
                        <span class="unit-eq-name" id="unitNameCopyLabel">Unit</span>
                        <input type="hidden" id="unitNameCopy" value="Unit">
                        <span class="unit-eq-sign">=</span>
                        <input type="number" name="multiplier" id="multiplier" class="form-control form-control-sm unit-eq-multiplier" min="2" step="1" value="<?php echo e(old('multiplier')); ?>" placeholder="24" aria-label="How many base units">
                        <select name="base_unit_id" id="baseUnit" class="form-select form-select-sm unit-eq-base" aria-label="Base unit">
                            <option value="">-- select unit --</option>
                            <?php $__currentLoopData = $baseUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $base): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($base->id); ?>" <?php echo e(old('base_unit_id') == $base->id ? 'selected' : ''); ?>><?php echo e($base->name); ?> (<?php echo e($base->short_name); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="unit-preview" id="conversionPreviewWrap" hidden>
                        <i class="bi bi-check2-circle"></i> <span id="conversionPreview"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4" id="unitSubmit"><i class="bi bi-check-lg me-1"></i><span>Register</span></button>
            </div>
        </form>
    </div>
</div>

<?php echo $__env->make('pages.units._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/units/index.blade.php ENDPATH**/ ?>