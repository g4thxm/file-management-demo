<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DateTime;
use Alert;
use App\Http\Requests\ConfigurationRequest;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $darkmode = Auth()->user()->darkmode;
        $conf = Configuration::orderBy('branch_id', 'ASC')->get();
        return view('utility.configurationlist', ['darkmode' => $darkmode, 'master' => $conf]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $darkmode = Auth()->user()->darkmode;
        return view('utility.configuration', ['darkmode' => $darkmode]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConfigurationRequest $request)
    {
        // dd($request->all());

        $branchid = Auth::user()->branch_id;




        if ($request->hasFile('company_logo')) {
            $ldate = new DateTime('now');
            $strdate = $ldate->format('Y-m-d');
            $strtime = $ldate->format('H-i-s');
            $fileName = $strdate . '_' . $strtime . '_' . $request->company_logo->getClientOriginalName();
            $request->company_logo->move(public_path('dist/img/'), $fileName);
        } else {
            $fileName = null;
        }
        if ($request->hasFile('login_logo')) {
            $ldate = new DateTime('now');
            $strdate = $ldate->format('Y-m-d');
            $strtime = $ldate->format('H-i-s');
            $fileName1 = $strdate . '_' . $strtime . '_' . $request->login_logo->getClientOriginalName();
            $request->login_logo->move(public_path('dist/img/'), $fileName1);
        } else {
            $fileName1 = null;
        }
        if ($request->hasFile('login_background')) {
            $ldate = new DateTime('now');
            $strdate = $ldate->format('Y-m-d');
            $strtime = $ldate->format('H-i-s');
            $fileName2 = $strdate . '_' . $strtime . '_' . $request->login_background->getClientOriginalName();
            $request->login_background->move(public_path('dist/img/'), $fileName2);
        } else {
            $fileName2 = null;
        }
        if ($request->hasFile('favicon')) {
            $ldate = new DateTime('now');
            $strdate = $ldate->format('Y-m-d');
            $strtime = $ldate->format('H-i-s');
            $fileName3 = $strdate . '_' . $strtime . '_' . $request->favicon->getClientOriginalName();
            $request->favicon->move(public_path('dist/img/'), $fileName3);
        } else {
            $fileName3 = null;
        }

        $count = Configuration::where('branch_id', $branchid)->count();


        if ($count == 0) {
            $task = new Configuration([
                'branch_id' => $branchid,
                'front_logo' => $fileName,
                'login_logo' => $fileName1,
                'back_img' => $fileName2,
                'favi' => $fileName3,
                'darkmode' => $request->get('darkmode')

            ]);
            $task->save();
        } else {

            $updateData = [
                'front_logo' => $fileName,
                'login_logo' => $fileName1,
                'back_img' => $fileName2,
                'favi' => $fileName3,
                'darkmode' => $request->get('darkmode')
            ];

            $updateData = array_filter($updateData, function ($value) {
                return !empty($value);
            });

            Configuration::where('branch_id', $branchid)
                ->update($updateData);
        }


        Alert::toast('Configuration saved', 'success')->autoClose(3000);
        return redirect()->route('config.index');
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
