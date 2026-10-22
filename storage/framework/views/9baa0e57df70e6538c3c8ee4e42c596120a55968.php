<?php $__env->startSection('title', 'Roles & Permissions'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $initials = function ($name) {
        $parts = preg_split('/[\s_]+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) return '?';
        return strtoupper(count($parts) > 1 ? $parts[0][0] . $parts[1][0] : substr($parts[0], 0, 2));
    };
    $locked = $selected && $selected->is_system;
    $granted = $selected ? ($locked ? null : $selected->permissionKeys()) : [];
    $isGranted = fn ($key) => $locked || in_array($key, $granted ?? [], true);
?>

<?php echo $__env->make('pages.role._index_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4 rp-page">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="rp-intro">
        <p class="mb-0 text-muted small"><i class="bi bi-info-circle text-primary me-1"></i> Choose a role on the left, tick what it can do, then save. Changes apply to everyone with that role.</p>
        <div class="d-flex gap-2">
            <span class="rp-intro-stat"><i class="bi bi-shield-lock text-primary"></i> <?php echo e($roles->count()); ?> roles</span>
            <span class="rp-intro-stat"><i class="bi bi-people text-primary"></i> <?php echo e($roles->sum('people_count')); ?> people</span>
        </div>
    </div>

    <div class="rp-layout">
        
        <aside class="rp-sidebar">
            <p class="rp-sidebar-label mb-0">User Roles</p>
            <ul class="list-unstyled mb-0">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('dashboard.role', ['role' => $role->id])); ?>" class="rp-role-btn rp-role-link <?php echo e($selected && $selected->id === $role->id ? 'active' : ''); ?>"
                           <?php if($selected && $selected->id === $role->id): ?> aria-current="page" <?php endif; ?>>
                            <span class="rp-role-avatar"><?php if($role->is_system): ?><i class="bi bi-lock-fill"></i><?php else: ?><?php echo e($initials($role->name)); ?><?php endif; ?></span>
                            <span class="flex-grow-1" style="min-width:0">
                                <span class="rp-role-name"><?php echo e(ucfirst($role->name)); ?></span>
                                <span class="rp-role-sub"><?php echo e($role->people_count); ?> <?php echo e($role->people_count === 1 ? 'user' : 'users'); ?></span>
                            </span>
                            <span class="rp-role-count" <?php if($selected && $selected->id === $role->id): ?> id="rpSidebarCount" <?php endif; ?>>
                                <?php echo e($role->is_system ? 'All' : $role->permissions->count()); ?>

                            </span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="rp-add-role-btn" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="bi bi-plus-lg"></i> Add Role</button>
        </aside>

        
        <section class="rp-panel">
            <?php if(!$selected): ?>
                <div class="rp-panel-header"><p class="text-muted mb-0">No roles yet.</p></div>
            <?php else: ?>
                <div class="rp-panel-header">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="rp-role-avatar" style="width:44px;height:44px;font-size:15px;">
                                <?php if($locked): ?><i class="bi bi-lock-fill"></i><?php else: ?><?php echo e($initials($selected->name)); ?><?php endif; ?>
                            </span>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0"><?php echo e(ucfirst($selected->name)); ?></h5>
                                    <?php if (! ($locked)): ?>
                                        <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Rename role" aria-label="Rename role"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" action="<?php echo e(route('dashboard.roles.destroy', $selected->id)); ?>" class="d-inline"
                                              onsubmit="return confirm('Delete the <?php echo e(addslashes($selected->name)); ?> role?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-link text-danger p-0" title="Delete role" aria-label="Delete role"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mb-0">
                                    <?php echo $locked ? 'System role &mdash; always has full access.' : e($selected->description ?: 'Tick what this role is allowed to do.'); ?>

                                </p>
                            </div>
                        </div>
                        <div class="rp-tabs" role="tablist">
                            <button type="button" class="rp-tab-btn active" data-tab="permissions" role="tab">Permissions</button>
                            <button type="button" class="rp-tab-btn" data-tab="users" role="tab">Users</button>
                        </div>
                    </div>
                </div>

                
                <form method="POST" action="<?php echo e($locked ? '#' : route('dashboard.roles.permissions.update', $selected->id)); ?>" id="rpPermissionsPanel" data-tab-panel="permissions">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="p-3 border-bottom d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width:220px;">
                            <div class="rp-progress"><div class="rp-progress-bar" id="rpProgressBar"></div></div>
                            <span class="small text-muted text-nowrap"><span id="rpGranted">0</span> / <?php echo e($totalPermissions); ?> granted</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="search" id="rpFilterInput" class="form-control form-control-sm" style="width:190px;" placeholder="Search permissions" aria-label="Search permissions">
                            <?php if (! ($locked)): ?>
                                <button type="button" class="btn btn-sm btn-link" id="rpSelectAllBtn">Select all</button>
                                <button type="button" class="btn btn-sm btn-link text-muted" id="rpClearAllBtn">Clear all</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $modules): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rp-section">
                            <div class="px-3 pt-3">
                                <p class="mb-0 small text-uppercase fw-bold text-muted" style="letter-spacing:.04em;"><?php echo e($section); ?></p>
                            </div>
                            <div class="row g-3 p-3 pt-2">
                                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $moduleKey = \Illuminate\Support\Str::slug($module); ?>
                                    <div class="col-md-6 col-xl-4 rp-module" data-module="<?php echo e($moduleKey); ?>" data-label="<?php echo e(strtolower($module)); ?>">
                                        <div class="rp-module-card">
                                            <div class="rp-module-card-header">
                                                <div style="min-width:0">
                                                    <p class="mb-0 fw-semibold text-truncate"><?php echo e($module); ?></p>
                                                    <p class="mb-0 small text-muted"><span class="rp-module-count">0</span> / <?php echo e(count($permissions)); ?></p>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input rp-module-toggle" type="checkbox" data-module="<?php echo e($moduleKey); ?>"
                                                           aria-label="All <?php echo e($module); ?> permissions" <?php echo e($locked ? 'disabled' : ''); ?>>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0 rp-module-list">
                                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="rp-permission" data-label="<?php echo e(strtolower($label)); ?>">
                                                        <label class="rp-perm-label <?php echo e($locked ? 'locked' : ''); ?>">
                                                            <input type="checkbox" class="form-check-input rp-permission-checkbox m-0" name="permission[]" value="<?php echo e($key); ?>"
                                                                   data-module="<?php echo e($moduleKey); ?>" <?php echo e($isGranted($key) ? 'checked' : ''); ?> <?php echo e($locked ? 'disabled' : ''); ?>>
                                                            <span class="flex-grow-1"><?php echo e($label); ?></span>
                                                        </label>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <p class="text-center text-muted py-4 mb-0 rp-hidden" id="rpNoMatch">No permissions match your search.</p>
                </form>

                
                <div class="p-4 rp-hidden" data-tab-panel="users">
                    <?php $__empty_1 = true; $__currentLoopData = $people; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="rp-user-row">
                            <span class="rp-user-avatar"><?php echo e($initials($person->name)); ?></span>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold small"><?php echo e($person->name); ?></div>
                                <div class="small text-muted"><?php echo e($person->detail ?: '—'); ?></div>
                            </div>
                            <span class="badge <?php echo e($person->kind === 'Staff POS' ? 'bg-light text-dark border' : 'bg-primary-subtle text-primary'); ?>"><?php echo e($person->kind); ?></span>
                            <?php if($person->shop): ?><span class="badge bg-light text-dark border"><?php echo e($person->shop); ?></span><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0">Nobody has this role yet.
                            <?php if (! ($locked)): ?><a href="<?php echo e(route('staff.manage.index')); ?>">Assign it to staff</a>.<?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>


<div class="rp-save-bar rp-hidden rp-page" id="rpSaveBar">
    <div class="rp-save-bar-inner">
        <span class="small" id="rpSaveBarText">Unsaved changes</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-light" id="rpResetBtn"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            <button type="submit" form="rpPermissionsPanel" class="btn btn-sm btn-primary" id="rpSaveBtn">Save</button>
        </div>
    </div>
</div>


<div class="modal fade rp-page" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?php echo e(route('dashboard.roles.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="newRoleName">Role name</label>
                    <input type="text" name="name" id="newRoleName" class="form-control" value="<?php echo e(old('name')); ?>" maxlength="100" required>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="newRoleDescription">Description (optional)</label>
                    <textarea name="description" id="newRoleDescription" class="form-control" rows="2" maxlength="500"><?php echo e(old('description')); ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Create role</button>
            </div>
        </form>
    </div>
</div>


<?php if($selected && !$locked): ?>
    <div class="modal fade rp-page" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="<?php echo e(route('dashboard.roles.update', $selected->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleTitle">Rename role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editRoleName">Role name</label>
                        <input type="text" name="name" id="editRoleName" class="form-control" value="<?php echo e($selected->name); ?>" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="editRoleDescription">Description (optional)</label>
                        <textarea name="description" id="editRoleDescription" class="form-control" rows="2" maxlength="500"><?php echo e($selected->description); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php echo $__env->make('pages.role._index_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php if($errors->has('name')): ?>
<script>
document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addRoleModal')).show());
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\role\index.blade.php ENDPATH**/ ?>