
<?php
$shops = $shops ?? collect();
?>



<?php $__env->startSection('title', 'Staff Dashboard'); ?>

<?php $__env->startSection('content'); ?>

    
    <?php echo $__env->make('components/breadcrumb', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('components/mainmenu', ['shops' => $shops], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <br><br><br><br><br>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <div class="cat__content">
        <div class="container-fluid">
            <div class="row g-4" style="padding-left:30px; padding-right:30px;">
                <div class="col-xl-10 mx-auto">
                    <div class="cat__core__widget p-3 h-100" style="background:#fff;">

                        <!-- Top Bar -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                            <!-- Left: Search & Export -->
                            <div class="d-flex align-items-center mb-2">
                                <div style="margin-right:8px;">
                                    <input type="text" id="staffSearch" class="form-control form-control-sm" placeholder="Search staff...">
                                </div>
                                <div style="margin-right:8px;">
                                    <button class="btn btn-sm btn-success">Export Excel</button>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-danger">Export PDF</button>
                                </div>
                            </div>

                            <!-- Right: Back & Add Staff -->
                            <div class="d-flex align-items-center mb-2">
                                <div style="margin-right:8px;">
                                    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary btn-sm">Back</a>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                        Add Staff
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Staff Table -->
                        <table class="table table-bordered text-center w-75 mx-auto staff-table">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Shop</th>
                                    <th>Wages</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
    $totalWages = 0;
?>

<?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($s->first_name); ?> <?php echo e($s->last_name); ?></td>
        <td><?php echo e($s->phone); ?></td>
        <td><?php echo e($s->shop->name ?? '-'); ?></td>
        <td><?php echo e(number_format($s->wages, 2)); ?></td>
        <td><?php echo e($s->created_at->format('Y-m-d')); ?></td>
        <td>
    
            <a href="<?php echo e(route('staff.edit', $s->id)); ?>" class="btn btn-sm btn-info">Edit</a>


        <form action="<?php echo e(route('staff.destroy', $s->id)); ?>" method="POST" style="display:inline-block;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this staff?')">Delete</button>
        </form>

        </td>
    </tr>
    <?php
        $totalWages += $s->wages;
    ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<tr class="table-success">
    <td colspan="3"><strong>Total Wages</strong></td>
    <td><strong><?php echo e(number_format($totalWages, 2)); ?></strong></td>
    <td colspan="2"></td>
</tr>
</tbody>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ADD STAFF MODAL ================= -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-lg" style="margin-top:160px;">
            <div class="modal-content">

                <form method="POST" action="<?php echo e(route('staff.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="modal-header">
                        <h5 class="modal-title">Register Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <?php if(session('success')): ?>
                            <div class="alert alert-success">
                                <?php echo e(session('success')); ?>

                            </div>
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

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
<select name="shop_id" class="form-control" required>
    <option value="">Select Shop</option>
    <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($shop->id); ?>" <?php echo e(old('shop_id') == $shop->id ? 'selected' : ''); ?>>
            <?php echo e($shop->name); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

                            </div>
                            <div class="col-md-6">
      <select name="role_id" class="form-control" required>
    <option value="">Select Role</option>
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($role->id); ?>"
            <?php echo e(old('role_id') == $role->id ? 'selected' : ''); ?>>
            <?php echo e(ucfirst($role->name)); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>


                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Staff</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- SEARCH SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('staffSearch');
            if(searchInput){
                searchInput.addEventListener('keyup', function () {
                    const filter = this.value.toUpperCase();
                    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
                        if(row.classList.contains('table-success')) return;
                        const nameCell = row.querySelector('td:first-child');
                        row.style.display = nameCell && nameCell.textContent.toUpperCase().includes(filter) ? '' : 'none';
                    });
                });
            }
        });
    </script>

    <!-- REQUIRED BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/admin/staff.blade.php ENDPATH**/ ?>