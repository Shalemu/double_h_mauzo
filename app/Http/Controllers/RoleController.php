<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display roles list
     */
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('dashboard.role.index', compact('roles'));
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

        Role::create([
            'name' => strtolower($request->name),
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Role created successfully.');
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
        // Prevent delete if role is assigned to staff
        if ($role->staff()->count() > 0) {
            return redirect()->back()
                ->withErrors(['Role is assigned to staff and cannot be deleted.']);
        }

        $role->delete();

        return redirect()->back()
            ->with('success', 'Role deleted successfully.');
    }
}
