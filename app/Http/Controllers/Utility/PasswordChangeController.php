<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordChangeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('utility.passwordchange');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        // dd($id);
        $this->validate($request,[
            'oldpassword' =>  'required',
            'newpassword' => 'required',
            'confirmpassword' => 'required|same:newpassword'
        ]);
        // $user = Auth::user();
    $userInfo = User::find(Auth::id());
    $a=['password' => bcrypt($request->oldpassword)];

        // if(Hash::check($request->oldpassword, $userInfo->password))
        // if($a!=$userInfo->password)
        // if(!Hash::check($request->old_password, auth()->$userInfo->password))
        if (!Hash::check($request->oldpassword, $userInfo->password))
        { 
            return redirect()->route('passwordchange.index')->with('error','Old password not matching');
        }
        else{

        $user=User::find($id);

        $userarray=['password' => bcrypt($request->get('newpassword'))];
        // $userarray->password = Hash::make($request->newpassword);
        // $user->save();
        $user->update($userarray);

    // User::find(auth()->user()->id)->update(['password'=> Hash::make($request->newpassword)]);
        return redirect()->route('passwordchange.index')->with('success','Password changed successfully');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
