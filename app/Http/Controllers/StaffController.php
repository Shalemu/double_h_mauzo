<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Shops;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display the staff dashboard.
     */

public function index()
{
    $staff = Staff::with(['shop', 'role'])->latest()->get();
    $shops = Shops::orderBy('name')->get();

    // Only show roles for normal staff
    $roles = Role::whereNotIn('name', ['admin', 'super admin'])
                 ->orderBy('name')
                 ->get();

    return view('dashboard.admin.staff', compact('staff', 'shops', 'roles'));
}



    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20|unique:staff,phone',
            'email'      => 'nullable|email|max:255',
            'shop_id'    => 'required|exists:shops,id',
            'role_id'    => 'required|exists:roles,id',
            'password'   => 'required|confirmed|min:4',
            'wages'      => 'nullable|numeric|min:0',
        ]);

        // Create staff
        Staff::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'phone'      => $validated['phone'],
            'email'      => $validated['email'],
            'shop_id'    => $validated['shop_id'],
            'role_id'    => $validated['role_id'],
            'wages'      => $validated['wages'] ?? 0,
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Staff registered successfully.');
    }

  // Show edit form
public function edit($id)
{
    $staff = Staff::findOrFail($id);
    $shops = Shops::orderBy('name')->get();
    $roles = Role::whereNotIn('name', ['admin', 'super admin'])->orderBy('name')->get();

    return view('dashboard.admin.staff_edit', compact('staff', 'shops', 'roles'));
}

// Update staff
public function update(Request $request, $id)
{
    $staff = Staff::findOrFail($id);

    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name'  => 'required|string|max:255',
        'phone'      => "required|string|max:20|unique:staff,phone,$id",
        'email'      => 'nullable|email|max:255',
        'shop_id'    => 'required|exists:shops,id',
        'role_id'    => 'required|exists:roles,id',
        'wages'      => 'nullable|numeric|min:0',
        'password'   => 'nullable|confirmed|min:4',
    ]);

    $staff->first_name = $validated['first_name'];
    $staff->last_name = $validated['last_name'];
    $staff->phone = $validated['phone'];
    $staff->email = $validated['email'];
    $staff->shop_id = $validated['shop_id'];
    $staff->role_id = $validated['role_id'];
    $staff->wages = $validated['wages'] ?? 0;

    if (!empty($validated['password'])) {
        $staff->password = Hash::make($validated['password']);
    }

    $staff->save();

  
return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');

    
}

public function destroy(Staff $staff)
{
    $staff->delete();
    return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
}

}
