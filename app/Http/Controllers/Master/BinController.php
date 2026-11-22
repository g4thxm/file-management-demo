<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\BinExport;
use App\Imports\BinImport;
use App\Models\Bin;
use DB;
use Excel;
use Alert;

class BinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $i = 0;
        $bin = Bin::where('deleted_at', null)->get();
        return view('master.binlist', ['bin' => $bin, 'i' => $i]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("master.bincreate");
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
        $this->validate($request, [
            'Bin_Name' => 'required',
            'bin_code' => 'required',
            'storage_loc_id' => 'required',
            'Bin_Desc' => 'required'
        ]);
        $bin = Bin::where('Bin_Name', $request->Bin_Name)->orWhere('bin_code', $request->bin_code);
        $bin = $bin->first();
        if ($bin) {
            Alert::toast('Bin Already Exist', 'error')->autoClose(3000);
            return redirect()->route('bin.index');
        }
        // dd($vendor);
        $bin = new Bin([

            'Bin_Name' => $request->get('Bin_Name'),
            'bin_code' => $request->get('bin_code'),
            'storage_loc_id' => $request->get('storage_loc_id'),
            'Bin_Desc' => $request->get('Bin_Desc'),
        ]);
        //  dd($vendor);
        $bin->save();
        $content[] = [
            'barcode' => $bin->Bin_Name,
        ];
        if ($request->prn) {
            // dd(123);
            $printable_content = $this->generateDPL($content);
            // dd($printable_content);
            Alert::toast('Bin Added Successfully', 'success')->autoClose(3000);
            return redirect()->route('bin.index')->with('printable_content1', $printable_content);
        } else {
            $printable_content = $bin->id;
            Alert::toast('Bin Added Successfully', 'success')->autoClose(3000);
            return redirect()->route('bin.index')->with('printable_content', $printable_content);
        }
        // Alert::toast('Bin Added Successfully', 'success')->autoClose(3000);
        // return redirect()->route('bin.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bin  $bin
     * @return \Illuminate\Http\Response
     */

    public function exportIntoExcel()
    {
        // dd('hai');
        return Excel::download(new BinExport, 'Bin Master.xlsx');
    }

    public function show(Bin $bin)
    {
        // $id = $bin;

        $bin_data = Bin::where('id', $bin->id)->first();

        // dd($bin_data);

        $content[] = [
            'barcode' => $bin_data->Bin_Name,
        ];

        $printable_content = $this->generateDPL($content);
        Alert::toast('Printing Succesfully ' . 'success')->autoClose(3000);
        return redirect()->route('bin.index')->with('printable_content1', $printable_content);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bin  $bin
     * @return \Illuminate\Http\Response
     */
    public function edit(Bin $bin)
    {
        //
        return view("master.bincreate", compact('bin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bin  $bin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // DB::enableQueryLog();
        //
        $this->validate($request, [
            'Bin_Name' => 'required',
            'bin_code' => 'required',
            'storage_loc_id' => 'required',
            'Bin_Desc' => 'required'
        ]);

        $bin = Bin::find($id);

        $bin2 = Bin::whereNotIn('id', [$id]);

        $bin2 = $bin2->where(DB::raw("(Bin_Name = '$request->Bin_Name' OR bin_code = '$request->bin_code' ) and deleted_at"));
        $bin2 = $bin2->first();
        if ($bin2) {
            Alert::toast('Bin Already Exist', 'error')->autoClose(3000);
            return redirect()->route('bin.index');
        }
        // dd(\DB::getQueryLog());


        $binarray = [
            'Bin_Name' => $request->get('Bin_Name'),
            'bin_code' => $request->get('bin_code'),
            'storage_loc_id' => $request->get('storage_loc_id'),
            'Bin_Desc' => $request->get('Bin_Desc'),
        ];

        $bin->update($binarray);
        Alert::toast('Bin Modified successfully', 'success')->autoClose(3000);
        return redirect()->route('bin.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bin  $bin
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $bin = Bin::find($id);
        $result = $bin->delete();
        if ($result) {
            Alert::toast('Bin deleted successfully', 'success')->autoClose(3000);
            return redirect()->route('bin.index');
        } else {
            Alert::toast('Bin deletion failed', 'error')->autoClose(3000);
            return redirect()->route('bin.index');
        }
    }

    public function uploadForm()
    {
        return view('master.binupload');
    }

    public function fileUpload(Request $request)
    {
        $current = date('Y-m-d_H-i-s');
        $file = $request->file('file')->store('file');
        $data = Excel::import(new BinImport, $file);
        // dd($data);   

        $fileName = $current . '_' . $request->file->getClientOriginalName();
        // dd($fileName);
        $request->file->move(public_path('file'), $fileName);

        Alert::toast('Bin Uploaded Successfully', 'success')->autoClose(3000);
        return redirect()->route('bin.index');
    }

    protected function generateDPL($contents = [])
    {
        // dd($contents);

        $dpl_data = $this->readDPL();

        $printable_data1 = '';

        foreach ($contents as $content) {
            $patterns = array();
            $replacements = array();
            foreach ($content as $k => $v) {
                $patterns[] = "[$k]";
                $replacements[] = (string)$v;
            }
            $assigned = str_replace($patterns, $replacements, $dpl_data);

            // remove unwanted tags
            $assigned = preg_replace("/(\[\w+\])/", '', $assigned);
            $printable_data1 .= $assigned;
        }
        // dd($printable_data1);
        return $printable_data1;
    }

    protected function readDPL()
    {
        // dd(12354);
        $file = public_path() . '/prn_files/' . '/bin.txt';
        $contents = fopen($file, 'r');
        $contents1 = fread($contents, filesize($file));

        return $contents1;
    }
}
