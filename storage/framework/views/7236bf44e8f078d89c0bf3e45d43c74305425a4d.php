<?php
$shops = $shops ?? collect();
?>



<?php $__env->startSection('title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>

    
    <?php echo $__env->make('components/breadcrumb', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('components/mainmenu', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <br><br><br><br><br>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <div class="cat__content">
        <div class="container-fluid">
            <div class="row g-4" style="padding-left:30px; padding-right:30px;">
                <div class="col-xl-10 mx-auto">

                    <?php if(session('success')): ?>
                        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- ================= PENDING APPROVALS ================= -->
                    <div class="cat__core__widget p-3 mb-4" style="background:#fff;">
                        <h5 class="mb-3">Pending Approvals</h5>

                        <table class="table table-bordered text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Registered</th>
                                    <th>Assign Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $pendingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($user->name); ?></td>
                                        <td><?php echo e($user->email); ?></td>
                                        <td><?php echo e($user->phone); ?></td>
                                        <td><?php echo e($user->created_at->format('Y-m-d')); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('users.manage.approve', $user->id)); ?>" method="POST" class="d-flex gap-2 justify-content-center">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <select name="role_id" class="form-control form-control-sm" style="max-width:160px;" required>
                                                    <option value="">Select Role</option>
                                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($role->id); ?>"><?php echo e(ucfirst($role->name)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="<?php echo e(route('users.manage.reject', $user->id)); ?>" method="POST" onsubmit="return confirm('Reject this registration?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-muted">No pending registrations.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= MANAGE EXISTING USERS ================= -->
                    <div class="cat__core__widget p-3" style="background:#fff;">
                        <h5 class="mb-3">Manage Existing Users</h5>

                        <table class="table table-bordered text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Super Admin</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $approvedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($user->name); ?></td>
                                        <td><?php echo e($user->email); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('users.manage.role', $user->id)); ?>" method="POST" class="d-flex gap-2 justify-content-center">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <select name="role_id" class="form-control form-control-sm" style="max-width:160px;" required>
                                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($role->id); ?>" <?php echo e($user->role_id == $role->id ? 'selected' : ''); ?>>
                                                            <?php echo e(ucfirst($role->name)); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-info">Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if($user->id === auth()->id()): ?>
                                                <span class="badge <?php echo e($user->super_user ? 'bg-success' : 'bg-secondary'); ?>">
                                                    <?php echo e($user->super_user ? 'Yes' : 'No'); ?>

                                                </span>
                                            <?php else: ?>
                                                <form action="<?php echo e(route('users.manage.superUser', $user->id)); ?>" method="POST" onsubmit="return confirm('Change Super Admin status for this user?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <button type="submit" class="btn btn-sm <?php echo e($user->super_user ? 'btn-success' : 'btn-outline-secondary'); ?>">
                                                        <?php echo e($user->super_user ? 'Yes — click to revoke' : 'No — click to grant'); ?>

                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($user->created_at->format('Y-m-d')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-muted">No approved users yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/admin/user_management.blade.php ENDPATH**/ ?>