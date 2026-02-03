<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Only guests can access registration
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'phone'       => 'required|string|max:20|unique:users,phone',
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
        ]);

        // Create user as NON-ADMIN
       Users::create([
    'first_name'  => $request->first_name,
    'middle_name' => $request->middle_name,
    'last_name'   => $request->last_name,
    'name'        => trim($request->first_name . ' ' . $request->last_name),

    // username = email
    'username'    => $request->email,

    'phone'       => $request->phone,
    'email'       => $request->email,

    'password'    => Hash::make($request->password),
    'code'        => random_int(100000, 999999),
    'expires_at'  => now()->addMinutes(30),

    'role_id'     => 0,
    'verified'    => 0,
    'verified_at' => null,
    'super_user'  => 0,
    'user_type'   => 'Retailer',

    'login_trials'    => 0,
    'password_reset' => 0,
    'admin_id'       => null,
    'profile_image'  => null,
    'sync_status'    => 0,
]);

        // Redirect to login with message
        return redirect()
            ->route('login')
            ->with('success', 'Registration successful. Your account will be activated once approved by admin.');
    }
}
