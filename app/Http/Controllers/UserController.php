<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use Session;
use App\Models\Users;
use Validator;
use Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class UserController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
	
	public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }
	
	public function changepassword()
    {
        $user = Auth::user();
        return view('user.changepassword', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function updateprofile(Request $request, $user_id)
    {
        $Usr = Users::findOrFail($user_id);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user_id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);
        $Usr->name = $request->input('name');
        $Usr->phone = $request->input('phone');
        $Usr->email = $request->input('email');
        if ($request->hasFile('profile_image')) {
            if ($Usr->profile_image) {
                Storage::disk('public')->delete($Usr->profile_image);
            }
            $Usr->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }
        $Usr->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }
   
    public function updatePassword(Request $request, $user_id)
    {
        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Users::findOrFail($user_id);

        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->route('changepassword')->with('error', 'Current password is incorrect!');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('changepassword')->with('success', 'Password changed successfully!');
    }

    public function userlogout()
{
    Auth::logout();
    return redirect()->route('login');
}

}
