<?php

namespace App\Http\Controllers;
use App\Models\Engineer;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch = (new Branch)->getTable();
        $engineer = (new Engineer)->getTable();
        $users = User::join($branch,'branches.id',"=","users.branch_id")->join($engineer,'eng.id',"=","users.engineer")->select('users.*','branches.name as branch','eng.name as engname')->get();
  
        return view('user_master',['users'=>$users, 'i'=>0]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        //
        $branch_all = Branch::all();
        $engineer_all = Engineer::all();

        return view('user_add',['branch_all'=>$branch_all,'engineer_all'=>$engineer_all]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'username'=>'required',
            'email'=>'required',
            'password'=>'required',
            'branch'=>'required',
            'eng'=>'required'
        ],['name.required'=>'Name is Required',
        'username.required'=>'Username Name is Required',
        'email.required'=>'Email is Required',
        'password.required'=>'Password Person is Required',
        'branch.required'=>'Branch is Required',
        'eng.required'=>'Engineer is Required',
       ]);
      

        date_default_timezone_set("Asia/Calcutta");

        $user = new User;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->branch_id = $request->branch;
        $user->engineer = $request->eng;
        $user->darkmode = 0;
        $user->save();
        flash('User Added Successfully')->success();
        return redirect('usermaster');
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
        $user_id = $id;
        
        $user = User::where('id',$user_id)->get();
        $branch_all = Branch::all();
        $engineer_all = Engineer::all();
        foreach($user as $users)
        {
            $user_name = $users->name;
            $username = $users->username;
            $user_email = $users->email;
            $user_password = $users->password;

            $branch_id = $users->branch_id;
      
            $eng_id = $users->engineer;
            $branch = Branch::select('name')->where('id',$branch_id)->get();
            
            $engineer = Engineer::select('name')->where('id',$eng_id)->get();
            foreach($branch as $cat)
            {
                $branch_name = $cat->name;
            }
            foreach($engineer as $engg)
            {
                $engineer_name = $engg->name;
            }
        }
        return view('user_add',['user_id'=>$user_id,'user_name'=>$user_name,'username'=>$username,'user_email'=>$user_email,'user_password'=>$user_password,'branch_id'=>$branch_id,'branch_name'=>$branch_name,'eng_id'=>$eng_id,'eng_name'=>$engineer_name,'branch_all'=>$branch_all,'engineer_all'=>$engineer_all]);
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
        $request->validate([
            'name'=>'required',
            'username'=>'required',
            'email'=>'required',
            'password'=>'required',
            'branch'=>'required',
            'eng'=>'required'
        ],['name.required'=>'Name is Required',
        'username.required'=>'Username Name is Required',
        'email.required'=>'Email is Required',
        'password.required'=>'Password Person is Required',
        'branch.required'=>'Branch is Required',
        'eng.required'=>'Engineer is Required',
       ]);
        date_default_timezone_set("Asia/Calcutta");

        $password = Hash::make($request->password);
        User::where('id', $id)
                     ->update([
                      'branch_id' =>$request->branch,'name' => $request->name,'username' => $request->username,'password' => $password,'email' => $request->email,'engineer' => $request->eng
                     ]);
 
                     flash('User Updated Successfully')->success();
                     return redirect('usermaster');
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
