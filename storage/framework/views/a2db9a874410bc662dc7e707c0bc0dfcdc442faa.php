<?php
$shops = $shops ?? collect();
$totalCapital = 0;
?>




<?php $__env->startSection('title', 'Business Management'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('pages.shops._shop_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                
                <!-- Premium Main Container -->
                <div class="border rounded-4 shadow-sm bg-white p-4"
                     style="width: 100%; max-width: none; margin: auto; border: 1px solid #dee2e6 !important;">

                    <!-- Top Toolbar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom">
                        
                        <!-- Left Section -->
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <input 
                                type="text" 
                                id="shopSearch" 
                                class="form-control"
                                placeholder="Search shops..."
                                style="width: 280px; border-radius: 10px;"
                            >

                            <button class="btn btn-success px-3">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>

                            <button class="btn btn-danger px-3">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        </div>

                        <!-- Right Section -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary px-3">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>

                            <button class="btn btn-primary px-3" id="toggleAddShop">
                                <i class="bi bi-plus-circle"></i> Add Shop
                            </button>
                        </div>
                    </div>

                    <?php if(session('success')): ?>
                        <div class="alert alert-success rounded-3">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger rounded-3">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <?php
                        $activeShop = session('active_shop_id') ? $shops->firstWhere('id', (int) session('active_shop_id')) : null;
                    ?>

                    <!-- Active Shop Switcher -->
                    <div class="alert <?php echo e($activeShop ? 'active-shop-banner' : 'alert-secondary'); ?> d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3">
                        <div>
                            <?php if($activeShop): ?>
                                <i class="bi bi-shop me-1"></i> Working in <strong><?php echo e($activeShop->name); ?></strong> — other pages (e.g. Manage Products) will default to this shop.
                            <?php else: ?>
                                <i class="bi bi-globe me-1"></i> Viewing <strong>all shops</strong> (no active shop selected).
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <form method="POST" action="<?php echo e(route('admin.activeShop.set')); ?>" class="d-flex gap-2">
                                <?php echo csrf_field(); ?>
                                <select name="shop_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px;">
                                    <option value="">-- Set active shop --</option>
                                    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($s->id); ?>" <?php echo e($activeShop && $activeShop->id === $s->id ? 'selected' : ''); ?>>
                                            <?php echo e($s->name); ?><?php echo e($s->isSuspended() ? ' (Suspended)' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
                            <?php if($activeShop): ?>
                                <form method="POST" action="<?php echo e(route('admin.activeShop.clear')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-outline-secondary shop-view-all-btn">View all shops</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Add Shop Form -->
                    <div id="addShopForm" class="border rounded-3 p-4 mb-4 bg-light"
                         <?php if(!$errors->any()): ?> style="display:none;" <?php endif; ?>>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger rounded-3">
                                <ul class="mb-0">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('shops.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control"
                                           placeholder="Shop Name" value="<?php echo e(old('name')); ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="text" name="location" class="form-control"
                                           placeholder="Location" value="<?php echo e(old('location')); ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="number" name="capital" class="form-control"
                                           placeholder="Initial Capital" value="<?php echo e(old('capital')); ?>">
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success px-4">
                                    Save Shop
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Table Container -->
                    <div class="border rounded-3 p-2 shop-table-container">
                        <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center mb-0 shop-table w-100">
                            <thead class="table-warning">
                                <tr>
                                    <th>Name</th>
                                    <th>Employee</th>
                                    <th>Total Wages (TZS)</th>
                                    <th>Stock Value (TZS)</th>
                                    <th>Capital (TZS)</th>
                                    <th>Real Capital (TZS)</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $totalStock = 0; $totalCapital = 0; ?>

                                <?php $__empty_1 = true; $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('dashboard.shop.show', ['shop' => $shop->id])); ?>"
                                               class="text-decoration-none fw-semibold">
                                                <?php echo e($shop->name); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($shop->total_employees); ?></td>
                                        <td><?php echo e(number_format($shop->total_wages)); ?></td>
                                        <td><?php echo e(number_format($shop->stock_value, 2)); ?></td>
                                        <td><?php echo e(number_format($shop->capital, 2)); ?></td>
                                        <td><?php echo e(number_format($shop->realCapital, 2)); ?></td>
                                        <td><?php echo e($shop->location); ?></td>
                                        <td>
                                            <?php if($shop->isSuspended()): ?>
                                                <span class="badge bg-secondary">Suspended</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editShopModal<?php echo e($shop->id); ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                <form method="POST" action="<?php echo e(route('shops.toggleStatus', $shop->id)); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <button type="submit" class="btn btn-sm <?php echo e($shop->isSuspended() ? 'btn-outline-success' : 'btn-outline-secondary'); ?>">
                                                        <?php if($shop->isSuspended()): ?>
                                                            <i class="bi bi-play-circle"></i> Activate
                                                        <?php else: ?>
                                                            <i class="bi bi-pause-circle"></i> Suspend
                                                        <?php endif; ?>
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#deleteShopModal<?php echo e($shop->id); ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>

                                            <!-- Edit Shop Modal -->
                                            <div class="modal fade" id="editShopModal<?php echo e($shop->id); ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="<?php echo e(route('shops.update', $shop->id)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('PUT'); ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Shop</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Shop Name</label>
                                                                    <input type="text" name="name" class="form-control"
                                                                           value="<?php echo e($shop->name); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Location</label>
                                                                    <input type="text" name="location" class="form-control"
                                                                           value="<?php echo e($shop->location); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Capital</label>
                                                                    <input type="number" name="capital" class="form-control"
                                                                           value="<?php echo e($shop->capital); ?>">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Shop Modal -->
                                            <div class="modal fade" id="deleteShopModal<?php echo e($shop->id); ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="<?php echo e(route('shops.destroy', $shop->id)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-danger">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i> Delete Shop
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p class="mb-2">
                                                                    Delete <strong>"<?php echo e($shop->name); ?>"</strong>?
                                                                </p>
                                                                <p class="text-muted mb-0">
                                                                    This will permanently delete this shop and all of its products,
                                                                    staff, sales, expenses and other related data. This cannot be undone.
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete Shop
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php
                                        $totalStock += $shop->stock_value;
                                        $totalCapital += $shop->realCapital;
                                    ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9">No shops found.</td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="table-success fw-bold">
                                    <td colspan="3">Total</td>
                                    <td><?php echo e(number_format($totalStock, 2)); ?></td>
                                    <td></td>
                                    <td><?php echo e(number_format($totalCapital, 2)); ?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</div>


<!-- JS: Toggle form and live search -->
<?php echo $__env->make('pages.shops._shop_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/shops/shop.blade.php ENDPATH**/ ?>