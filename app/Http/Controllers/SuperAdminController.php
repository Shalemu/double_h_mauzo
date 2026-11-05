<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        return view('pages.admin.user_management', compact('pendingUsers', 'approvedUsers', 'roles'));
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

    /**
     * Show one user's details with an edit form.
     */
    public function show(Users $user)
    {
        $user->load('role', 'shop');
        $roles = Role::orderBy('name')->get();

        return view('pages.admin.user_show', compact('user', 'roles'));
    }

    /**
     * Update a user's details (and optionally set a new password).
     */
    public function update(Request $request, Users $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:20',
            'role_id'       => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'password'      => 'nullable|string|min:6|confirmed',
        ]);

        $user->name    = $validated['name'];
        $user->email   = $validated['email'];
        $user->phone   = $validated['phone'] ?? null;
        $user->role_id = $validated['role_id'];

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.manage.show', $user->id)
            ->with('success', "{$user->name}'s details have been updated.");
    }

    /**
     * Delete (soft delete) a user account.
     */
    public function destroy(Users $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.manage.index')->with('success', "{$name} has been deleted.");
    }
}
