<?php $__env->startSection('title', 'User Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">User Details</h4>
        <a href="<?php echo e(route('users.manage.index')); ?>" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
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

    <div class="row g-4">

        
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <?php if($user->profile_image): ?>
                        <img src="<?php echo e(asset('storage/' . $user->profile_image)); ?>" alt="" class="rounded-circle mb-3"
                             style="width:110px;height:110px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:110px;height:110px;background:#134AA4;color:#fff;font-size:40px;font-weight:700;">
                            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?>

                    <h5 class="mb-1"><?php echo e($user->name); ?></h5>
                    <div class="mb-2">
                        <span class="badge bg-info text-dark"><?php echo e($user->role ? ucfirst($user->role->name) : 'No role'); ?></span>
                        <?php if($user->super_user): ?>
                            <span class="badge bg-success">Super Admin</span>
                        <?php endif; ?>
                    </div>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Email</span><span class="text-break ms-2"><?php echo e($user->email); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Phone</span><span><?php echo e($user->phone ?: '—'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Shop</span><span><?php echo e($user->shop->name ?? '—'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Verified</span>
                        <span><?php echo e($user->verified ? 'Yes' . ($user->verified_at ? ' (' . $user->verified_at->format('Y-m-d') . ')' : '') : 'No'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Registered</span><span><?php echo e($user->created_at?->format('Y-m-d H:i')); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Last updated</span><span><?php echo e($user->updated_at?->format('Y-m-d H:i')); ?></span>
                    </li>
                </ul>

                <?php if (! ($user->id === auth()->id())): ?>
                    <div class="card-body">
                        <form action="<?php echo e(route('users.manage.destroy', $user->id)); ?>" method="POST"
                              onsubmit="return confirm('Delete <?php echo e(addslashes($user->name)); ?>? They will no longer be able to log in.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="col-lg-8" id="edit">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <i class="bi bi-pencil"></i> Edit Details
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('users.manage.update', $user->id)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone', $user->phone)); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role_id" class="form-control" required>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>" <?php echo e((int) old('role_id', $user->role_id) === $role->id ? 'selected' : ''); ?>>
                                            <?php echo e(ucfirst($role->name)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">JPG, PNG or WEBP, up to 3 MB. Leave empty to keep the current photo.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Set New Password <small class="text-muted fw-normal">(leave empty to keep the current password)</small></h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/admin/user_show.blade.php ENDPATH**/ ?>