<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Redirect path after successful login
     */
    protected $redirectTo = '/dashboard';

    /**
     * Only guests can access login, except logout
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Allow login via email or username
     */
    protected function credentials(Request $request)
    {
        $login = $request->input('email'); // from login form
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $request->password,
        ];
    }

    /**
     * Custom login logic to allow only admin users
     */
    public function login(Request $request)
    {
        // Validate form input
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt authentication
        if (!Auth::attempt($this->credentials($request))) {
            return back()->withErrors([
                'email' => 'Invalid login credentials.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        // BLOCK login if user is NOT admin
        if ($user->role_id !== 2) { // 2 = admin role ID
            Auth::logout();
            return back()->withErrors([
                'email' => 'Access denied. Only admin users can login.',
            ])->withInput($request->only('email'));
        }

        // Admin login successful
        return redirect()->intended($this->redirectTo);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
