<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
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
        // 1️⃣ Attempt Admin / Sub-admin login (email or username)
        // ----------------------------
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $notStaff = fn ($query) => $query->where('user_type', '!=', Users::TYPE_STAFF);

        if (Auth::attempt([$field => $login, 'password' => $password, $notStaff])) {
            $user = Auth::user();
            Log::info("Admin login success", ['user_id' => $user->id, 'role_id' => $user->role_id]);

            // Only allow admin/sub-admin roles, or a Super Admin regardless of role_id
            if (in_array($user->role_id, [1, 2]) || $user->super_user) {
                $request->session()->regenerate();
                return redirect()->route('dashboard.admin');
            }

            // Wrong role: logout
            Auth::logout();
            Log::warning("Admin login failed due to role mismatch", ['role_id' => $user->role_id]);
        } else {
            Log::info("Admin login attempt failed for $login");
        }

        // ----------------------------
        //  Attempt Staff login (phone)
        // ----------------------------
        if (Auth::attempt(['phone' => $login, 'password' => $password, 'user_type' => Users::TYPE_STAFF])) {
            $staff = Auth::user();
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
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
