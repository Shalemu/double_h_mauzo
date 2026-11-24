{{-- Tab bar shared by the Settings pages; hides tabs the user may not open. --}}
@php
    $settingsUser = Auth::user();
    $settingsTabs = collect(\App\Http\Controllers\Web\SettingsController::TABS)->filter(function ($tab, $route) use ($settingsUser) {
        $permission = \App\Http\Middleware\PanelPermissionMiddleware::permissionFor($route);
        return $permission === null || collect((array) $permission)->contains(fn ($p) => $settingsUser->hasPermission($p));
    });
@endphp

<style>
.settings-tabs { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:18px; border-bottom:1px solid var(--bs-border-color); }
.settings-tabs a { display:flex; align-items:center; gap:8px; padding:10px 16px; color:var(--bs-secondary-color); text-decoration:none;
    font-weight:600; border-bottom:2px solid transparent; margin-bottom:-1px; }
.settings-tabs a:hover { color:var(--bs-body-color); }
.settings-tabs a.active { color:#831619; border-bottom-color:#831619; }
html[data-bs-theme="dark"] .settings-tabs a.active { color:#ec4e53; border-bottom-color:#ec4e53; }
.settings-help { color:var(--bs-secondary-color); font-size:13px; margin:0; }
</style>

<nav class="settings-tabs" aria-label="Settings">
    @foreach($settingsTabs as $route => [$label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.index') . '*') ? 'active' : '' }}"
           @if(request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.index') . '*')) aria-current="page" @endif>
            <i class="bi {{ $icon }}"></i> {{ $label }}
        </a>
    @endforeach
</nav>
