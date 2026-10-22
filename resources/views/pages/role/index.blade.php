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

@include('pages.role._index_styles')

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

@include('pages.role._index_scripts')

@if($errors->has('name'))
<script>
document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addRoleModal')).show());
</script>
@endif
@endsection
