<?php $__env->startSection('title', 'Distribution'); ?>

<?php $__env->startSection('content'); ?>
<style>
.dist-results { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; max-height:260px; overflow:auto; display:none; }
.dist-results.is-open { display:block; }
</style>

<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-truck-flatbed me-2"></i>Distribution</h5>
                <small class="text-muted">A distributor takes stock out of a store. When they come back, returned units go back into the store and the rest is recorded as a sale.</small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#issueModal" <?php echo e($stores->isEmpty() ? 'disabled' : ''); ?>>
                <i class="bi bi-box-arrow-right"></i> Issue stock
            </button>
        </div>
        <div class="card-body">
            <?php if($stores->isEmpty()): ?>
                <div class="alert alert-info">You don't have a store yet. <a href="<?php echo e(route('stores.index')); ?>">Add a store</a> under Manage Business → All Stores.</div>
            <?php endif; ?>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Distributors still out</div>
                        <div class="fs-4 fw-semibold"><?php echo e($totals['out']); ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Owed by distributors (TZS)</div>
                        <div class="fs-4 fw-semibold <?php echo e($totals['owing'] > 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format($totals['owing'], 2)); ?></div>
                    </div>
                </div>
            </div>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-sm-4 col-lg-3">
                    <select name="store" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Store">
                        <option value="">All stores</option>
                        <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>" <?php echo e((string) $storeId === (string) $s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-sm-4 col-lg-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Status">
                        <option value="">All statuses</option>
                        <option value="out" <?php echo e($status === 'out' ? 'selected' : ''); ?>>Still out</option>
                        <option value="settled" <?php echo e($status === 'settled' ? 'selected' : ''); ?>>Settled</option>
                        <option value="owing" <?php echo e($status === 'owing' ? 'selected' : ''); ?>>Owing money</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>Store</th><th>Distributor</th><th>Taken</th><th>Status</th><th>Sold (TZS)</th><th>Balance (TZS)</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $distributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($d->id); ?></td>
                                <td class="text-nowrap"><?php echo e($d->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($d->store->name ?? '—'); ?></td>
                                <td><?php echo e($d->staff->full_name ?? '—'); ?></td>
                                <td>
                                    <?php $__currentLoopData = $d->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="small"><?php echo e($item->product_name); ?> × <?php echo e($item->quantity_taken); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td class="text-center">
                                    <?php if($d->isOut()): ?>
                                        <span class="badge bg-warning text-dark">Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Settled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo e($d->isOut() ? '—' : number_format($d->total_value, 2)); ?></td>
                                <td class="text-end <?php echo e($d->balance > 0 ? 'text-danger fw-semibold' : ''); ?>"><?php echo e($d->isOut() ? '—' : number_format($d->balance, 2)); ?></td>
                                <td class="text-center">
                                    <a href="<?php echo e(route('distributions.show', $d)); ?>" class="btn btn-sm <?php echo e($d->isOut() ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                                        <?php echo e($d->isOut() ? 'Settle' : 'View'); ?>

                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No distributions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($distributions->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>

<div class="modal fade" id="issueModal" tabindex="-1" aria-labelledby="issueTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('distributions.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="issueTitle">Issue stock to a distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="distStore">From store</label>
                        <select name="store_id" id="distStore" class="form-select" required>
                            <option value="">Choose...</option>
                            <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->id); ?>" <?php echo e((string) $storeId === (string) $s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="distStaff">Distributor</label>
                        <select name="staff_id" id="distStaff" class="form-select" required>
                            <option value="">Choose...</option>
                            <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($person->id); ?>"><?php echo e($person->full_name); ?><?php echo e($person->shop ? ' — ' . $person->shop->name : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-8 position-relative">
                        <input type="search" id="distSearch" class="form-control" autocomplete="off"
                               placeholder="Search product in the store..." aria-label="Search product" disabled>
                        <div class="list-group shadow dist-results" id="distResults"></div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" id="distQty" class="form-control" min="1" placeholder="Qty" aria-label="Quantity" disabled>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="distAdd" disabled>Add</button>
                    </div>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light"><tr><th>Product</th><th class="text-center">In store</th><th class="text-center">Taking</th><th></th></tr></thead>
                    <tbody id="distLines"><tr><td colspan="4" class="text-muted text-center small">No products added yet.</td></tr></tbody>
                </table>

                <label class="form-label" for="distNote">Note (optional)</label>
                <textarea name="note" id="distNote" class="form-control" rows="2" maxlength="1000" placeholder="e.g. route, vehicle, area"></textarea>
                <input type="hidden" name="items" id="distItems">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="distSubmit" disabled>Issue stock</button>
            </div>
        </form>
    </div>
</div>

<?php echo $__env->make('pages.distributions._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\distributions\index.blade.php ENDPATH**/ ?>