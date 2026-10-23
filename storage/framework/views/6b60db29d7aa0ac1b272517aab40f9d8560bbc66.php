<?php $__env->startSection('title', 'Stores'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('pages.stores._index_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="border rounded-4 shadow-sm bg-white p-4" style="border: 1px solid #dee2e6 !important;">

                <!-- Top Toolbar -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-building me-2"></i>Stores</h5>
                        <small class="text-muted">Stores hold stock only — they don't sell at the POS. Send stock to shops with Stock Transfers, or out with distributors from Distribution.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="text" id="storeSearch" class="form-control" placeholder="Search stores..." style="width: 240px; border-radius: 10px;" aria-label="Search stores">
                        <button class="btn btn-primary px-3" id="toggleAddStore">
                            <i class="bi bi-plus-circle"></i> Add Store
                        </button>
                    </div>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success rounded-3"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger rounded-3"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <?php
                    $activeStore = session('active_shop_id') ? $stores->firstWhere('id', (int) session('active_shop_id')) : null;
                ?>

                <!-- Active Store Switcher: Product Management and Purchases follow it -->
                <div class="alert <?php echo e($activeStore ? 'active-store-banner' : 'alert-secondary'); ?> d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
                    <div>
                        <?php if($activeStore): ?>
                            <i class="bi bi-building me-1"></i> Working in <strong><?php echo e($activeStore->name); ?></strong> — Product Management and Purchases now show this store's stock.
                        <?php else: ?>
                            <i class="bi bi-box-seam me-1"></i> Pick a store to manage its products or buy stock into it.
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="<?php echo e(route('admin.activeShop.set')); ?>" class="d-flex gap-2">
                            <?php echo csrf_field(); ?>
                            <select name="shop_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px;" aria-label="Work in store">
                                <option value="">-- Work in store --</option>
                                <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s->id); ?>" <?php echo e($activeStore && $activeStore->id === $s->id ? 'selected' : ''); ?>>
                                        <?php echo e($s->name); ?><?php echo e($s->isSuspended() ? ' (Suspended)' : ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                        <?php if($activeStore): ?>
                            <form method="POST" action="<?php echo e(route('admin.activeShop.clear')); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn store-view-all-btn">Stop working in store</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Store Form -->
                <div id="addStoreForm" class="border rounded-3 p-4 mb-4 bg-light" <?php if(!$errors->any()): ?> style="display:none;" <?php endif; ?>>
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('stores.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control" placeholder="Store Name" value="<?php echo e(old('name')); ?>" required aria-label="Store name">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="location" class="form-control" placeholder="Location" value="<?php echo e(old('location')); ?>" required aria-label="Location">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success px-4">Save Store</button>
                        </div>
                    </form>
                </div>

                <!-- Stores Table -->
                <div class="border rounded-3 p-2">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center mb-0 store-table w-100">
                            <thead class="table-info">
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Products</th>
                                    <th>Stock Value (TZS)</th>
                                    <th>Staff</th>
                                    <th>Distributors Out</th>
                                    <th>Owed by Distributors (TZS)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $totalStock = 0; $totalOwed = 0; ?>

                                <?php $__empty_1 = true; $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php $stockValue = $store->stock_value; ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('stores.show', $store)); ?>" class="text-decoration-none fw-semibold"><?php echo e($store->name); ?></a>
                                        </td>
                                        <td><?php echo e($store->location); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('stores.show', $store)); ?>" class="text-decoration-none"><?php echo e(number_format($store->products_count)); ?></a>
                                        </td>
                                        <td><?php echo e(number_format($stockValue, 2)); ?></td>
                                        <td><?php echo e($store->staff_count); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('distributions.index', ['store' => $store->id, 'status' => 'out'])); ?>" class="text-decoration-none">
                                                <?php echo e($store->distributions_out_count); ?>

                                            </a>
                                        </td>
                                        <td class="<?php echo e($store->distributor_balance > 0 ? 'text-danger fw-semibold' : ''); ?>"><?php echo e(number_format($store->distributor_balance ?? 0, 2)); ?></td>
                                        <td>
                                            <?php if($store->isSuspended()): ?>
                                                <span class="badge bg-secondary">Suspended</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="<?php echo e(route('stores.show', $store)); ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStoreModal<?php echo e($store->id); ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <form method="POST" action="<?php echo e(route('stores.toggleStatus', $store->id)); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <button type="submit" class="btn btn-sm <?php echo e($store->isSuspended() ? 'btn-outline-success' : 'btn-outline-secondary'); ?>">
                                                        <?php if($store->isSuspended()): ?>
                                                            <i class="bi bi-play-circle"></i> Activate
                                                        <?php else: ?>
                                                            <i class="bi bi-pause-circle"></i> Suspend
                                                        <?php endif; ?>
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStoreModal<?php echo e($store->id); ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>

                                            <!-- Edit Store Modal -->
                                            <div class="modal fade" id="editStoreModal<?php echo e($store->id); ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form class="modal-content" method="POST" action="<?php echo e(route('stores.update', $store->id)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PUT'); ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Store</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label">Store Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo e($store->name); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Location</label>
                                                                <input type="text" name="location" class="form-control" value="<?php echo e($store->location); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Delete Store Modal -->
                                            <div class="modal fade" id="deleteStoreModal<?php echo e($store->id); ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form class="modal-content" method="POST" action="<?php echo e(route('stores.destroy', $store->id)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Delete Store</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <p class="mb-2">Delete <strong>"<?php echo e($store->name); ?>"</strong>?</p>
                                                            <p class="text-muted mb-0">This will permanently delete this store and all of its products, distributions and other related data. This cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Store</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                        $totalStock += $stockValue;
                                        $totalOwed += $store->distributor_balance ?? 0;
                                    ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-muted py-4">No stores yet. Click <strong>Add Store</strong> to create one.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php if($stores->isNotEmpty()): ?>
                                    <tr class="table-success fw-bold">
                                        <td colspan="3">Total</td>
                                        <td><?php echo e(number_format($totalStock, 2)); ?></td>
                                        <td colspan="2"></td>
                                        <td><?php echo e(number_format($totalOwed, 2)); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('pages.stores._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\stores\index.blade.php ENDPATH**/ ?>