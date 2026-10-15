<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Roles & Permissions screen (laid out like Faramas): roles on the left,
     * the chosen role's permissions or people on the right. Roles 1 and 2
     * are admin-tier — always full access, so they're shown locked.
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->withCount(['staff', 'users'])
            ->orderBy('name')
            ->get()
            ->each(function (Role $role) {
                // Only "admin" is locked to full access; every other role is
                // whatever is ticked (manager included).
                $role->is_system = $role->id === 1;
                $role->people_count = $role->users_count + $role->staff_count;
            });

        $selected = $roles->firstWhere('id', (int) $request->query('role'))
            ?? $roles->firstWhere('is_system', false)
            ?? $roles->first();

        $sections = $this->permissionSections();
        $totalPermissions = collect($sections)->flatten(1)->sum(fn ($perms) => count($perms));

        $people = collect();
        if ($selected) {
            $people = $selected->users()->orderBy('name')->get()->map(fn ($u) => (object) [
                    'name' => $u->name ?: $u->full_name, 'detail' => $u->email, 'shop' => null, 'kind' => 'Admin panel'])
                ->concat($selected->staff()->with('shop')->orderBy('first_name')->get()->map(fn ($s) => (object) [
                    'name' => $s->full_name, 'detail' => $s->phone, 'shop' => $s->shop->name ?? null, 'kind' => 'Staff POS']));
        }

        return view('pages.role.index', compact('roles', 'selected', 'sections', 'totalPermissions', 'people'));
    }

    // Old per-role URL — the permissions now live on the Roles page itself.
    public function permissions(Role $role)
    {
        return redirect()->route('dashboard.role', ['role' => $role->id]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        abort_if($role->id === 1, 404);

        $valid = collect(config('permissions'))->flatMap(fn ($module) => array_keys($module))->all();
        $keys = array_values(array_intersect((array) $request->input('permission', []), $valid));

        $role->permissions()->whereNotIn('permission', $keys)->delete();
        foreach ($keys as $key) {
            $role->permissions()->firstOrCreate(['permission' => $key]);
        }

        return redirect()->route('dashboard.role', ['role' => $role->id])
            ->with('success', 'Permissions for "' . ucfirst($role->name) . '" saved.');
    }

    /**
     * config/permissions.php modules grouped under config/permission_sections.php
     * headings: [section => [module => [key => label]]].
     */
    private function permissionSections(): array
    {
        $modules = config('permissions');
        $sections = [];

        foreach (config('permission_sections') as $section => $names) {
            foreach ($names as $name) {
                if (isset($modules[$name])) {
                    $sections[$section][$name] = $modules[$name];
                    unset($modules[$name]);
                }
            }
        }
        if ($modules) {
            $sections['Other'] = $modules;
        }

        return $sections;
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = Role::create([
            'name' => strtolower($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard.role', ['role' => $role->id])
            ->with('success', 'Role created. Tick what it can do, then save.');
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
        ]);

        $role->update([
            'name' => strtolower($request->name),
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        // Admin/manager roles back the admin accounts themselves.
        if ($role->id <= 2) {
            return redirect()->back()
                ->withErrors(['System roles cannot be deleted.']);
        }

        // Prevent delete if role is assigned to staff
        if ($role->staff()->count() > 0) {
            return redirect()->back()
                ->withErrors(['Role is assigned to staff and cannot be deleted.']);
        }

        $role->delete();

        return redirect()->route('dashboard.role')
            ->with('success', 'Role deleted successfully.');
    }
}
