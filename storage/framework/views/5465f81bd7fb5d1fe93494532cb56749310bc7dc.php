

<?php $__env->startSection('title', 'Role Dashboard'); ?>

<?php $__env->startSection('content'); ?>


<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<br><br><br><br><br>

<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4 px-4">
            <div class="col-xl-10 mx-auto">
                <div class="cat__core__widget p-3 h-100 bg-white">

                    
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        
                        <input type="text"
                               id="roleSearch"
                               class="form-control form-control-sm w-25"
                               placeholder="Search role...">

                        
                        <div>
                            <a href="<?php echo e(url()->previous()); ?>"
                               class="btn btn-secondary btn-sm me-2">
                                Back
                            </a>

                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addRoleModal">
                                Add Role
                            </button>
                        </div>
                    </div>

                    
                    <table class="table table-bordered text-center staff-table mx-auto w-75">
                        <thead class="table-warning text-uppercase">
                            <tr>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(ucfirst($role->name)); ?></td>
                                <td><?php echo e($role->description ?? '—'); ?></td>
<td>
    <div class="d-flex justify-content-center">
        
        <button class="btn btn-sm btn-outline-primary"
                style="margin-right: 8px;"   
                data-bs-toggle="modal"
                data-bs-target="#editRoleModal<?php echo e($role->id); ?>">
            <i class="bi bi-pencil"></i>
        </button>

        
        <form action="<?php echo e(route('dashboard.roles.destroy', $role->id)); ?>" method="POST" class="d-inline" style="margin-left: 0;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
</td>


                            </tr>

                            
                            <div class="modal fade"
                                 id="editRoleModal<?php echo e($role->id); ?>"
                                 tabindex="-1">
                                <div class="modal-dialog modal-md" style="margin-top: 200px;">
                                    <div class="modal-content">
                                        <form method="POST" action="<?php echo e(route('dashboard.roles.update', $role->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>

                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Role</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Role Name</label>
                                                    <input type="text"
                                                           name="name"
                                                           class="form-control"
                                                           value="<?php echo e($role->name); ?>"
                                                           required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description"
                                                              class="form-control"
                                                              rows="3"><?php echo e($role->description); ?></textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-success">Update</button>
                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3">No roles found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div><br><br><br><br>



<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-md" style="margin-top: 200px;">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('dashboard.roles.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="modal-header">
                    <h5 class="modal-title">Add Role</h5>
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

                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="<?php echo e(old('name')); ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"><?php echo e(old('description')); ?></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save Role</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </form>
        </div>
    </div>
</div>


<?php if(session('success') || $errors->any()): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('addRoleModal')).show();
});
</script>
<?php endif; ?>


<script>
document.getElementById('roleSearch').addEventListener('keyup', function () {
    let filter = this.value.toUpperCase();
    document.querySelectorAll('.staff-table tbody tr').forEach(row => {
        row.style.display =
            row.textContent.toUpperCase().includes(filter) ? '' : 'none';
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/role/index.blade.php ENDPATH**/ ?>