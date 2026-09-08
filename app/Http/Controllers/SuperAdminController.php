<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    /**
     * Show pending registrations and existing users for approval/role management.
     */
    public function index()
    {
        $pendingUsers = Users::where('role_id', 0)->latest()->get();
        $approvedUsers = Users::where('role_id', '>', 0)->with('role')->latest()->get();
        $roles = Role::orderBy('name')->get();

        return view('dashboard.admin.user_management', compact('pendingUsers', 'approvedUsers', 'roles'));
    }

    /**
     * Approve a pending registration by assigning it a role.
     */
    public function approve(Request $request, Users $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'role_id'     => $validated['role_id'],
            'verified'    => true,
            'verified_at' => now(),
        ]);

        return back()->with('success', "{$user->name} has been approved.");
    }

    /**
     * Reject (soft delete) a pending registration.
     */
    public function reject(Users $user)
    {
        $user->delete();

        return back()->with('success', 'Registration rejected.');
    }

    /**
     * Change the role of an already-approved user.
     */
    public function updateRole(Request $request, Users $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update(['role_id' => $validated['role_id']]);

        return back()->with('success', "{$user->name}'s role has been updated.");
    }

    /**
     * Grant or revoke Super Admin status on a user.
     */
    public function toggleSuperUser(Users $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own Super Admin status.');
        }

        $user->update(['super_user' => !$user->super_user]);

        return back()->with('success', "{$user->name}'s Super Admin status has been updated.");
    }
}
