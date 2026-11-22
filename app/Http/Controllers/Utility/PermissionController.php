<?php

namespace App\Http\Controllers\Utility;

use App\Models\Permission;
use App\Models\Submenu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('utility.permission');
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
        // dd($request->all());

        $i = Submenu::count();
        $per = Submenu::select('id')->where('title', 'User Permission')->get();
        $permission = Permission::where('user_id', $request->user_id)
            ->whereNotIn('submenu_id', $per);
        $permission->forceDelete();
        $pr = Permission::select('id')->orderBy('id', 'desc')->first();
        $pr = $pr->id;
        // for($j=1;$j<=$i;$j++) {
        $data = $request->all();
        $k = 0;

        foreach ($data as $key => $value) {
            if ($k >= 2 && $key != "cbox" && $key != "2") {

                // dd($value;
                if ($value == "1") {
                    $pr = $pr + 1;
                    Permission::insert([
                        'id' => $pr,
                        'user_id' => $request->user_id,
                        'submenu_id' => $key,
                    ]);
                }
            }
            $k++;
        }
        // }
        // Alert::toast('Permission Modified successfully', 'success')->autoClose(3000);
        // flash('Permission saved successfully')->success();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
