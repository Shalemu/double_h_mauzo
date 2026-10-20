@extends('main')

@section('title', 'Roles & Permissions')

@section('content')
@php
    $initials = function ($name) {
        $parts = preg_split('/[\s_]+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) return '?';
        return strtoupper(count($parts) > 1 ? $parts[0][0] . $parts[1][0] : substr($parts[0], 0, 2));
    };
    $locked = $selected && $selected->is_system;
    $granted = $selected ? ($locked ? null : $selected->permissionKeys()) : [];
    $isGranted = fn ($key) => $locked || in_array($key, $granted ?? [], true);
@endphp

<style>
/* Logo maroon instead of Bootstrap blue across this screen */
.rp-page{ --rp-brand:#831619; --rp-brand-dark:#6b1215; --rp-brand-soft:rgba(131,22,25,.1); }
.rp-page .text-primary{ color:var(--rp-brand) !important; }
.rp-page .btn-link{ color:var(--rp-brand); }
.rp-page .btn-link:hover{ color:var(--rp-brand-dark); }
.rp-page .btn-primary{ background:var(--rp-brand); border-color:var(--rp-brand); }
.rp-page .btn-primary:hover, .rp-page .btn-primary:focus, .rp-page .btn-primary:active{ background:var(--rp-brand-dark) !important; border-color:var(--rp-brand-dark) !important; }
.rp-page .form-check-input:checked{ background-color:var(--rp-brand); border-color:var(--rp-brand); }
.rp-page .form-check-input:focus, .rp-page .form-control:focus{ border-color:rgba(131,22,25,.45); box-shadow:0 0 0 .2rem rgba(131,22,25,.15); }
.rp-page .form-switch .form-check-input:focus:not(:checked){ background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba(131,22,25,.45)'/%3e%3c/svg%3e"); }
.rp-page .bg-primary-subtle{ background:var(--rp-brand-soft) !important; }
html[data-bs-theme="dark"] .rp-page{ --rp-brand:#e0565b; --rp-brand-dark:#c94449; }
.rp-intro{ display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; background:#fff; border:1px solid #eef0f6; border-radius:12px; padding:12px 16px; margin-bottom:14px; }
.rp-intro-stat{ display:inline-flex; align-items:center; gap:6px; border:1px solid #e6e8f0; background:#f8fafc; border-radius:999px; padding:4px 12px; font-size:12px; font-weight:600; color:#45506b; }
.rp-layout{ display:grid; grid-template-columns:280px 1fr; gap:16px; align-items:start; }
@media (max-width:991px){ .rp-layout{ grid-template-columns:1fr; } }
.rp-sidebar{ background:#fff; border:1px solid #eef0f6; border-radius:14px; padding:8px; position:sticky; top:90px; }
.rp-sidebar-label{ font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#9aa3b5; padding:8px 10px 4px; }
.rp-role-btn{ display:flex; align-items:center; gap:10px; width:100%; border-radius:10px; padding:9px 10px; text-decoration:none; transition:background-color .12s; }
.rp-role-btn:hover{ background:#f8fafc; }
.rp-role-btn.active{ background:rgba(131,22,25,.08); box-shadow:inset 0 0 0 1px rgba(131,22,25,.18); }
.rp-role-avatar{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; flex-shrink:0; border-radius:9px; background:rgba(131,22,25,.12); color:var(--rp-brand); font-size:12px; font-weight:700; }
.rp-role-name{ display:block; font-size:13.5px; font-weight:600; color:#1d2433; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rp-role-btn.active .rp-role-name{ color:var(--rp-brand); }
.rp-role-sub{ font-size:11px; color:#8891a3; }
.rp-role-count{ flex-shrink:0; font-size:11px; font-weight:600; color:#9aa3b5; }
.rp-add-role-btn{ margin-top:6px; width:100%; border:1px dashed #d7dbe6; border-radius:10px; background:transparent; color:var(--rp-brand); font-size:13px; font-weight:600; padding:9px 10px; }
.rp-add-role-btn:hover{ border-color:var(--rp-brand); background:rgba(131,22,25,.05); }
.rp-panel{ background:#fff; border:1px solid #eef0f6; border-radius:14px; min-width:0; }
.rp-panel-header{ border-bottom:1px solid #eef0f6; padding:16px; }
.rp-tabs{ display:inline-flex; background:#f1f2f8; border-radius:10px; padding:3px; }
.rp-tab-btn{ border:none; background:transparent; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; color:#6b7280; }
.rp-tab-btn.active{ background:#fff; color:#1d2433; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.rp-progress{ height:6px; flex:1; border-radius:999px; background:#eef0f6; overflow:hidden; }
.rp-progress-bar{ height:100%; background:var(--rp-brand); border-radius:999px; transition:width .2s; }
.rp-module-card{ border:1px solid #eef0f6; border-radius:12px; transition:border-color .15s, background-color .15s; height:100%; }
.rp-module-card.has-checked{ border-color:rgba(131,22,25,.3); background:rgba(131,22,25,.03); }
.rp-module-card-header{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; }
.rp-module-list{ border-top:1px solid #f1f2f8; padding:6px 8px; max-height:220px; overflow-y:auto; }
.rp-perm-label{ display:flex; align-items:center; gap:8px; padding:5px 6px; border-radius:8px; font-size:13px; cursor:pointer; margin:0; }
.rp-perm-label:hover{ background:#f8fafc; }
.rp-perm-label.locked{ cursor:default; }
.rp-save-bar{ position:fixed; left:0; right:0; bottom:18px; display:flex; justify-content:center; z-index:1040; pointer-events:none; }
.rp-save-bar-inner{ pointer-events:auto; display:flex; align-items:center; gap:14px; background:#1d2433; color:#fff; border-radius:14px; padding:10px 16px; box-shadow:0 10px 24px rgba(29,36,51,.28); }
.rp-user-row{ display:flex; align-items:center; gap:10px; padding:8px 4px; border-bottom:1px solid #f1f2f8; }
.rp-user-avatar{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; background:#f1f2f8; color:#6b7280; font-size:12px; font-weight:700; flex-shrink:0; }
.rp-hidden{ display:none !important; }
html[data-bs-theme="dark"] .rp-intro, html[data-bs-theme="dark"] .rp-sidebar, html[data-bs-theme="dark"] .rp-panel{ background:#1f2937; border-color:#374151; }
html[data-bs-theme="dark"] .rp-role-name{ color:#e5e7eb; }
html[data-bs-theme="dark"] .rp-role-btn:hover, html[data-bs-theme="dark"] .rp-perm-label:hover{ background:#273449; }
</style>

<div class="container-fluid py-4 rp-page">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="rp-intro">
        <p class="mb-0 text-muted small"><i class="bi bi-info-circle text-primary me-1"></i> Choose a role on the left, tick what it can do, then save. Changes apply to everyone with that role.</p>
        <div class="d-flex gap-2">
            <span class="rp-intro-stat"><i class="bi bi-shield-lock text-primary"></i> {{ $roles->count() }} roles</span>
            <span class="rp-intro-stat"><i class="bi bi-people text-primary"></i> {{ $roles->sum('people_count') }} people</span>
        </div>
    </div>

    <div class="rp-layout">
        {{-- Roles --}}
        <aside class="rp-sidebar">
            <p class="rp-sidebar-label mb-0">User Roles</p>
            <ul class="list-unstyled mb-0">
                @foreach($roles as $role)
                    <li>
                        <a href="{{ route('dashboard.role', ['role' => $role->id]) }}" class="rp-role-btn rp-role-link {{ $selected && $selected->id === $role->id ? 'active' : '' }}"
                           @if($selected && $selected->id === $role->id) aria-current="page" @endif>
                            <span class="rp-role-avatar">@if($role->is_system)<i class="bi bi-lock-fill"></i>@else{{ $initials($role->name) }}@endif</span>
                            <span class="flex-grow-1" style="min-width:0">
                                <span class="rp-role-name">{{ ucfirst($role->name) }}</span>
                                <span class="rp-role-sub">{{ $role->people_count }} {{ $role->people_count === 1 ? 'user' : 'users' }}</span>
                            </span>
                            <span class="rp-role-count" @if($selected && $selected->id === $role->id) id="rpSidebarCount" @endif>
                                {{ $role->is_system ? 'All' : $role->permissions->count() }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <button type="button" class="rp-add-role-btn" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="bi bi-plus-lg"></i> Add Role</button>
        </aside>

        {{-- Selected role --}}
        <section class="rp-panel">
            @if(!$selected)
                <div class="rp-panel-header"><p class="text-muted mb-0">No roles yet.</p></div>
            @else
                <div class="rp-panel-header">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="rp-role-avatar" style="width:44px;height:44px;font-size:15px;">
                                @if($locked)<i class="bi bi-lock-fill"></i>@else{{ $initials($selected->name) }}@endif
                            </span>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0">{{ ucfirst($selected->name) }}</h5>
                                    @unless($locked)
                                        <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Rename role" aria-label="Rename role"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" action="{{ route('dashboard.roles.destroy', $selected->id) }}" class="d-inline"
                                              onsubmit="return confirm('Delete the {{ addslashes($selected->name) }} role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger p-0" title="Delete role" aria-label="Delete role"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>
                                <p class="text-muted small mb-0">
                                    {!! $locked ? 'System role &mdash; always has full access.' : e($selected->description ?: 'Tick what this role is allowed to do.') !!}
                                </p>
                            </div>
                        </div>
                        <div class="rp-tabs" role="tablist">
                            <button type="button" class="rp-tab-btn active" data-tab="permissions" role="tab">Permissions</button>
                            <button type="button" class="rp-tab-btn" data-tab="users" role="tab">Users</button>
                        </div>
                    </div>
                </div>

                {{-- Permissions tab --}}
                <form method="POST" action="{{ $locked ? '#' : route('dashboard.roles.permissions.update', $selected->id) }}" id="rpPermissionsPanel" data-tab-panel="permissions">
                    @csrf
                    @method('PUT')
                    <div class="p-3 border-bottom d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width:220px;">
                            <div class="rp-progress"><div class="rp-progress-bar" id="rpProgressBar"></div></div>
                            <span class="small text-muted text-nowrap"><span id="rpGranted">0</span> / {{ $totalPermissions }} granted</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="search" id="rpFilterInput" class="form-control form-control-sm" style="width:190px;" placeholder="Search permissions" aria-label="Search permissions">
                            @unless($locked)
                                <button type="button" class="btn btn-sm btn-link" id="rpSelectAllBtn">Select all</button>
                                <button type="button" class="btn btn-sm btn-link text-muted" id="rpClearAllBtn">Clear all</button>
                            @endunless
                        </div>
                    </div>

                    @foreach($sections as $section => $modules)
                        <div class="rp-section">
                            <div class="px-3 pt-3">
                                <p class="mb-0 small text-uppercase fw-bold text-muted" style="letter-spacing:.04em;">{{ $section }}</p>
                            </div>
                            <div class="row g-3 p-3 pt-2">
                                @foreach($modules as $module => $permissions)
                                    @php $moduleKey = \Illuminate\Support\Str::slug($module); @endphp
                                    <div class="col-md-6 col-xl-4 rp-module" data-module="{{ $moduleKey }}" data-label="{{ strtolower($module) }}">
                                        <div class="rp-module-card">
                                            <div class="rp-module-card-header">
                                                <div style="min-width:0">
                                                    <p class="mb-0 fw-semibold text-truncate">{{ $module }}</p>
                                                    <p class="mb-0 small text-muted"><span class="rp-module-count">0</span> / {{ count($permissions) }}</p>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input rp-module-toggle" type="checkbox" data-module="{{ $moduleKey }}"
                                                           aria-label="All {{ $module }} permissions" {{ $locked ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0 rp-module-list">
                                                @foreach($permissions as $key => $label)
                                                    <li class="rp-permission" data-label="{{ strtolower($label) }}">
                                                        <label class="rp-perm-label {{ $locked ? 'locked' : '' }}">
                                                            <input type="checkbox" class="form-check-input rp-permission-checkbox m-0" name="permission[]" value="{{ $key }}"
                                                                   data-module="{{ $moduleKey }}" {{ $isGranted($key) ? 'checked' : '' }} {{ $locked ? 'disabled' : '' }}>
                                                            <span class="flex-grow-1">{{ $label }}</span>
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <p class="text-center text-muted py-4 mb-0 rp-hidden" id="rpNoMatch">No permissions match your search.</p>
                </form>

                {{-- Users tab --}}
                <div class="p-4 rp-hidden" data-tab-panel="users">
                    @forelse($people as $person)
                        <div class="rp-user-row">
                            <span class="rp-user-avatar">{{ $initials($person->name) }}</span>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-semibold small">{{ $person->name }}</div>
                                <div class="small text-muted">{{ $person->detail ?: '—' }}</div>
                            </div>
                            <span class="badge {{ $person->kind === 'Staff POS' ? 'bg-light text-dark border' : 'bg-primary-subtle text-primary' }}">{{ $person->kind }}</span>
                            @if($person->shop)<span class="badge bg-light text-dark border">{{ $person->shop }}</span>@endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nobody has this role yet.
                            @unless($locked)<a href="{{ route('staff.manage.index') }}">Assign it to staff</a>.@endunless
                        </p>
                    @endforelse
                </div>
            @endif
        </section>
    </div>
</div>

{{-- Floating save bar --}}
<div class="rp-save-bar rp-hidden rp-page" id="rpSaveBar">
    <div class="rp-save-bar-inner">
        <span class="small" id="rpSaveBarText">Unsaved changes</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-light" id="rpResetBtn"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            <button type="submit" form="rpPermissionsPanel" class="btn btn-sm btn-primary" id="rpSaveBtn">Save</button>
        </div>
    </div>
</div>

{{-- Add role --}}
<div class="modal fade rp-page" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('dashboard.roles.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="newRoleName">Role name</label>
                    <input type="text" name="name" id="newRoleName" class="form-control" value="{{ old('name') }}" maxlength="100" required>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="newRoleDescription">Description (optional)</label>
                    <textarea name="description" id="newRoleDescription" class="form-control" rows="2" maxlength="500">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Create role</button>
            </div>
        </form>
    </div>
</div>

{{-- Rename role --}}
@if($selected && !$locked)
    <div class="modal fade rp-page" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('dashboard.roles.update', $selected->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleTitle">Rename role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editRoleName">Role name</label>
                        <input type="text" name="name" id="editRoleName" class="form-control" value="{{ $selected->name }}" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="editRoleDescription">Description (optional)</label>
                        <textarea name="description" id="editRoleDescription" class="form-control" rows="2" maxlength="500">{{ $selected->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rpPermissionsPanel');
    if (!form) return;

    const locked = @json($locked);
    const total = {{ $totalPermissions }};
    const boxes = Array.from(form.querySelectorAll('.rp-permission-checkbox'));
    const saved = new Set(boxes.filter(b => b.checked).map(b => b.value));
    const saveBar = document.getElementById('rpSaveBar');
    const sidebarCount = document.getElementById('rpSidebarCount');

    function changes() {
        return boxes.filter(b => b.checked !== saved.has(b.value)).length;
    }

    function refresh() {
        const granted = boxes.filter(b => b.checked).length;
        document.getElementById('rpGranted').textContent = granted;
        document.getElementById('rpProgressBar').style.width = (total ? Math.round(granted / total * 100) : 0) + '%';
        if (sidebarCount && !locked) sidebarCount.textContent = granted;

        form.querySelectorAll('.rp-module').forEach(function (module) {
            const own = boxes.filter(b => b.dataset.module === module.dataset.module);
            const on = own.filter(b => b.checked).length;
            module.querySelector('.rp-module-count').textContent = on;
            module.querySelector('.rp-module-card').classList.toggle('has-checked', on > 0);
            module.querySelector('.rp-module-toggle').checked = on === own.length;
        });

        const changed = changes();
        saveBar.classList.toggle('rp-hidden', locked || changed === 0);
        document.getElementById('rpSaveBarText').textContent = changed + ' unsaved change' + (changed === 1 ? '' : 's');
    }

    boxes.forEach(b => b.addEventListener('change', refresh));

    form.querySelectorAll('.rp-module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            boxes.filter(b => b.dataset.module === toggle.dataset.module).forEach(b => { b.checked = toggle.checked; });
            refresh();
        });
    });

    const selectAll = document.getElementById('rpSelectAllBtn');
    const clearAll = document.getElementById('rpClearAllBtn');
    if (selectAll) selectAll.addEventListener('click', () => { boxes.forEach(b => { b.checked = true; }); refresh(); });
    if (clearAll) clearAll.addEventListener('click', () => { boxes.forEach(b => { b.checked = false; }); refresh(); });

    document.getElementById('rpResetBtn').addEventListener('click', function () {
        boxes.forEach(b => { b.checked = saved.has(b.value); });
        refresh();
    });

    // Search: a module matching by name shows all its permissions;
    // otherwise only matching permissions; empty modules/sections hide.
    document.getElementById('rpFilterInput').addEventListener('input', function () {
        const text = this.value.trim().toLowerCase();
        let anyVisible = false;
        form.querySelectorAll('.rp-section').forEach(function (section) {
            let sectionVisible = false;
            section.querySelectorAll('.rp-module').forEach(function (module) {
                const moduleMatch = !text || module.dataset.label.includes(text);
                let moduleVisible = false;
                module.querySelectorAll('.rp-permission').forEach(function (item) {
                    const show = moduleMatch || item.dataset.label.includes(text);
                    item.classList.toggle('rp-hidden', !show);
                    moduleVisible = moduleVisible || show;
                });
                module.classList.toggle('rp-hidden', !moduleVisible);
                sectionVisible = sectionVisible || moduleVisible;
            });
            section.classList.toggle('rp-hidden', !sectionVisible);
            anyVisible = anyVisible || sectionVisible;
        });
        document.getElementById('rpNoMatch').classList.toggle('rp-hidden', anyVisible);
    });

    // Permissions / Users tabs
    document.querySelectorAll('.rp-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.rp-tab-btn').forEach(b => b.classList.toggle('active', b === btn));
            document.querySelectorAll('[data-tab-panel]').forEach(p => p.classList.toggle('rp-hidden', p.dataset.tabPanel !== btn.dataset.tab));
            saveBar.classList.toggle('rp-hidden', btn.dataset.tab !== 'permissions' || locked || changes() === 0);
        });
    });

    // Don't lose ticks by switching role mid-edit.
    document.querySelectorAll('.rp-role-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (changes() > 0 && !confirm('Discard unsaved changes? Switching roles now will lose what you just changed.')) {
                event.preventDefault();
            }
        });
    });
    form.addEventListener('submit', function () {
        if (locked) return false;
        document.getElementById('rpSaveBtn').textContent = 'Saving...';
    });

    refresh();
});
</script>

@if($errors->has('name'))
<script>
document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addRoleModal')).show());
</script>
@endif
@endsection
