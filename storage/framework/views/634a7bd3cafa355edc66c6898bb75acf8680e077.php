

<?php $__env->startSection('title', 'Edit Staff'); ?>

<?php echo $__env->make('components/breadcrumb', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Employee Details</h2>
        <small class="text-muted">Update your employee information</small>
    </div>

    
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('staff.update', $staff->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row g-4">
            <!-- Profile Information -->
            <div class="col-lg-6">
                <div class="card h-100 border-light shadow-sm">
                    <div class="card-header bg-light">
                        <i class="bi bi-person"></i> Profile Information
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="<?php echo e(asset('images/default-avatar.png')); ?>" alt="Avatar" class="rounded-circle mb-3" width="100">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo e(old('first_name', $staff->first_name)); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo e(old('last_name', $staff->last_name)); ?>">
                            </div>

                            <div class="col-md-6">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone', $staff->phone)); ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $staff->email)); ?>">
                            </div>

                            <div class="col-md-6">
                                <label>Joined</label>
                                <input type="text" class="form-control" value="<?php echo e($staff->created_at->format('M d, Y')); ?>" disabled>
                            </div>

                            <div class="col-md-6">
                                <label>Shop</label>
                                <select name="shop_id" class="form-control">
                                    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($shop->id); ?>" <?php if($staff->shop_id == $shop->id): ?> selected <?php endif; ?>><?php echo e($shop->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="col-lg-6">
                <div class="card h-100 border-light shadow-sm">
                    <div class="card-header bg-light">
                        <i class="bi bi-shield-lock"></i> Roles & Permissions
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Role</label>
                                <select name="role_id" class="form-control">
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>" <?php if($staff->role_id == $role->id): ?> selected <?php endif; ?>><?php echo e($role->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Wages</label>
                                <input type="number" name="wages" class="form-control" value="<?php echo e(old('wages', $staff->wages)); ?>">
                            </div>

                            <div class="col-md-6">
                                <label>Password (leave blank to keep current)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save & Back Buttons -->
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Save Changes
            </button>
            <a href="<?php echo e(route('staff.index')); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/admin/staff_edit.blade.php ENDPATH**/ ?>