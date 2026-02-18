<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __construct()
    {
        // Only guests can access login, except logout
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Blade login view
    }

    /**
     * Handle login request for Admin or Staff
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email'    => 'required|string', // email, username, or phone
            'password' => 'required|string|min:4',
        ]);

        $login = $request->email;
        $password = $request->password;

        Log::info("Login attempt", ['login' => $login, 'password_length' => strlen($password)]);

        // ----------------------------
        // 1️⃣ Attempt Admin / Sub-admin login (users table)
        // ----------------------------
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('web')->attempt([$field => $login, 'password' => $password])) {
            $user = Auth::guard('web')->user();
            Log::info("Admin login success", ['user_id' => $user->id, 'role_id' => $user->role_id]);

            // Only allow admin/sub-admin roles
            if (in_array($user->role_id, [1, 2])) {
                return redirect()->route('dashboard.admin');
            }

            // Wrong role: logout
            Auth::guard('web')->logout();
            Log::warning("Admin login failed due to role mismatch", ['role_id' => $user->role_id]);
        } else {
            Log::info("Admin login attempt failed for $login");
        }

        // ----------------------------
        // 2️⃣ Attempt Staff login (staff table)
        // ----------------------------
        $staff = Staff::where('phone', $login)->first();

        if ($staff && Hash::check($password, $staff->password)) {
            Auth::guard('staff')->login($staff);
            $request->session()->regenerate();

            Log::info("Staff login success", ['staff_id' => $staff->id, 'shop_id' => $staff->shop_id]);

            // Redirect to staff dashboard
            return redirect()->route('staff.dashboard');
        }

        Log::info("Staff login attempt failed for $login");

        // ----------------------------
        // 3️⃣ Login failed
        // ----------------------------
        return back()->withErrors([
            'email' => 'Invalid login credentials. Check logs for details.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout both guards (Admin & Staff)
     */
    public function logout(Request $request)
    {
        // Logout Admin
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        // Logout Staff
        if (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
