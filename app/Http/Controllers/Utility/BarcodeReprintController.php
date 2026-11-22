<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\ProductionEntry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Alert;

class BarcodeReprintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('utility.barcodereprint');
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
        if ($request->doc_type == '1') {
            $grn = Grn::select('grns.id', 'grns.grn_no as doc_no', 'barcodes.serial_no', 'items.item_name', 'barcodes.spq_qty')
                ->leftjoin('barcodes', 'barcodes.transaction_id', '=', 'grns.id')
                ->leftjoin('items', 'barcodes.item_id', '=', 'items.id')
                ->where('barcodes.transaction_type', '1')
                ->where('grns.id', $request->doc_no);
        } else {
            $grn = ProductionEntry::select('production_entries.id', 'production_entries.production_no as doc_no', 'barcodes.serial_no', 'items.item_name', 'barcodes.spq_qty')
                ->leftjoin('barcodes', 'barcodes.transaction_id', '=', 'production_entries.id')
                ->leftjoin('items', 'barcodes.item_id', '=', 'items.id')
                ->where('barcodes.transaction_type', '2')
                ->where('production_entries.id', $request->doc_no);
        }

        if ($request->from_barcode != null) {
            $grn = $grn->where('barcodes.serial_no', '>=', $request->from_barcode);
        }
        if ($request->to_barcode != null) {
            $grn = $grn->where('barcodes.serial_no', '<=', $request->to_barcode);
        }
        $grn = $grn->get();


        if ($grn->count() != 0) {
            if ($request->prn) {
                $content = [];
                $i = 0;
                foreach ($grn as $gr) {
                    $content[$i]['doc_no'] = $gr->doc_no;
                    $content[$i]['barcode'] = $gr->serial_no;
                    $content[$i]['Item_name'] = $gr->item_name;
                    $content[$i]['Quantity'] = $gr->spq_qty;
                    $i++;
                }

                $doctypecontent['doc_type'] = $request->doc_type;


                $printable_content = $this->generateDPL($content, $doctypecontent);

                return back()->with('printable_content1', $printable_content);
            } else {
                $content = $request->doc_no . "|" . $request->from_barcode . "|" . $request->to_barcode;
                return back()->with('printable_content', $content);
            }

            // return back()->with('printable_content',$grn);
            // dd($grn);
            // return view('utility.grnprintview',['print'=>$grn]);

        } else {
            Alert::toast('Nothing to print', 'error')->autoClose(3000);
            return $this->index();
        }
    }
    public function printdata($id)
    {
        // dd($id);
        $data = explode('|', $id);

        $grn_id = $data['0'];
        $f_bar = $data['1'];
        $t_bar = $data['2'];
        $grn = Grn::select('grns.id', 'grns.grn_no', 'barcodes.serial_no', 'items.item_name')
            ->leftjoin('barcodes', 'barcodes.transaction_id', '=', 'grns.id')
            ->leftjoin('items', 'barcodes.item_id', '=', 'items.id')
            ->where('grns.id', $grn_id);

        if ($f_bar != null) {
            $grn = $grn->where('barcodes.serial_no', '>=', $f_bar);
        }
        if ($t_bar != null) {
            $grn = $grn->where('barcodes.serial_no', '<=', $t_bar);
        }
        $grn = $grn->get();

        // dd($grn);
        return view('utility.grnprintview', ['print' => $grn]);
    }


    protected function generateDPL($contents = [], $doctypecontent = [])
    {
        $readContent = $doctypecontent['doc_type'];

        $dpl_data = $this->readDPL($readContent);


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

        return $printable_data1;
    }

    protected function readDPL($readContent)
    {
        // dd($readContent);
        $setting = Setting::select('label', 'type')->where('type', $readContent)->first();
        // dd($setting);
        if ($setting->label) {
            $file = 'prn_files/' . $setting->label;
            $file = public_path() . '/prn_files/' . $setting->label;
            $contents = fopen($file, 'r');
            $contents1 = fread($contents, filesize($file));
        } else {
            // $file = '_barcode_label_29x19.txt';
            $file = public_path() . '/_barcode_label_29x19.txt';
            $contents = fopen($file, 'r');
            $contents1 = fread($contents, filesize($file));
        }
        //     dd(Storage::exists($file));
        // // if(Storage::exists($file)){
        //     $contents = Storage::get($file);
        //     dd($contents);
        //     $contents .= "\n";
        // }
        // else{
        //     $contents = '';
        // }
        return $contents1;
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
