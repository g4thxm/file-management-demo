<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Alert;

class PrnUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('utility.prnupload');
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
        $id = 1;

        if (!$request->has('prn_type') || $request->prn_type == "") {
            Alert::toast('Please select a Document Type', 'error')->autoClose(3000);
            return redirect()->route('prnupload.index');
        }

        if ($request->hasFile("file")) {

            $ext = $request->file('file')->getClientOriginalExtension();
            if ($ext == 'txt' || $ext == 'prn') {
                $uniqueno = date('U');
                $filename = "barcode" . $uniqueno . "." . $ext;
                // $path = $request->file('label_file')->storeAs('public',$filename);
                $request->file('file')->move(public_path('prn_files'), $filename);

                // $settings = Setting::find($id);
                $setting = Setting::where('type', $request->prn_type)->first();
                // dd($setting);
                //     if ($settings == null) {
                //         $setting = new Setting;
                //         $setting->label = $filename;
                //         $setting->type = $request->prn_type;
                //         $setting->save();
                //     } else {
                //         $settings->label = $filename;
                //         $settings->type = $request->prn_type;
                //         $settings->save();
                //     }

                //     Alert::toast('Label file uploaded successfully', 'success')->autoClose(3000);
                //     return $this->index();
                // } 
                if ($setting == null) {
                    $setting = new Setting;
                }

                $setting->label = $filename;
                $setting->type = $request->prn_type;
                $setting->save();

                Alert::toast('Label file uploaded successfully', 'success')->autoClose(3000);
                return $this->index();
            } else {
                Alert::toast('Invalid file format please select .txr or .prn file type', 'error')->autoClose(3000);
                return redirect()->route('prnupload.index');
            }
        } else {
            Alert::toast('Please Choose prn file', 'error')->autoClose(3000);
            return $this->index();
        }
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
