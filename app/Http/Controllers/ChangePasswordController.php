<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showChangeForm()
    {
        return view('utility.passwordchange');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'oldpassword'     => 'required',
            'newpassword'     => 'required|min:8|same:confirmpassword',
            'confirmpassword' => 'required',
        ]);

        $user = User::findOrFail(Auth::id());

        // Old password check
        if (!Hash::check($request->oldpassword, $user->password)) {
            Alert::error('Error', 'Old password is incorrect');
            return back();
        }

        // Prevent reuse (optional)
        if (Hash::check($request->newpassword, $user->password)) {
            Alert::warning('Warning', 'New password must be different from old password');
            return back();
        }

        // Update password
        $user->password = Hash::make($request->newpassword);
        $user->save();

        Alert::success('Success', 'Password updated successfully');

        return redirect()->route('changepassword.edit');
    }
}
