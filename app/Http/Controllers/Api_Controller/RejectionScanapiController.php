<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Barcode;
use App\Models\RejectionScan;
use App\Models\StorageScan;
use App\Models\Bin;
use App\Models\Grn_sub;
use App\Models\Grn;
use Illuminate\Support\Facades\Log;
use Auth;
use App\Models\Branch;
use App\Models\Qc;
use DB;

class RejectionScanapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // dd(1);
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
        $json_content = file_get_contents('php://input');
        $json = json_decode($json_content, true);

        $currentTime = now()->toDateTimeString();
        $array = sizeof($json['data']);
        $spq_count = 0;
        // $bins = $bin->where('Bin_Name', "$re_bin")->first();
        $grnno = Grn::where('grn_no', $json['grnno'])->first();
        foreach ($json['data'] as $bar) {

                    $bar_tag = trim($bar['tag']);
                        if(strlen($bar_tag)==24)  //If RFID, convert from hex to decimal
                        {
                            $newbarcode1 = "";
                            for($i=0;$i<12;$i++)
                            {
                                $hexstr = substr($bar_tag,$i*2,2);
                                if($hexstr != "00") $newbarcode1 = chr(hexdec($hexstr)) .$newbarcode1;
                            }
                        }
                        else $newbarcode1 = $bar_tag;
                        
                        $bar['tag'] = $newbarcode1;
                        
                        
            $tag_count = Barcode::where('serial_no', $bar['tag'])->first();

            if ($tag_count) {
                $spq_count = $spq_count + $tag_count->spq_qty;
            }
        }
        $grnId = $grnno->id;
        $sum_qcqty = Qc::where('grn_id', $grnId)->sum('rejected_qty');
        $rejected_qty = RejectionScan::where('grn_id', $grnId)->sum('rejected_qty');
        $blanceScan = $sum_qcqty - $rejected_qty;
        if ($spq_count > $blanceScan) {
            return response()->json(['code' => 404, 'status' => 'RejectionScan Quantity Exceeded']);
        }

        foreach ($json['data'] as $bar) {
            try {
                
                $bar_tag = trim($bar['tag']);
                        if(strlen($bar_tag)==24)  //If RFID, convert from hex to decimal
                        {
                            $newbarcode1 = "";
                            for($i=0;$i<12;$i++)
                            {
                                $hexstr = substr($bar_tag,$i*2,2);
                                if($hexstr != "00") $newbarcode1 = chr(hexdec($hexstr)) .$newbarcode1;
                            }
                        }
                        else $newbarcode1 = $bar_tag;
                        
                        $bar['tag'] = $newbarcode1;
                        
                        
                // Retrieve necessary data from the database
                $grnno = Grn::where('grn_no', $json['grnno'])->first();
                // dd($grnno);
                $tag = Barcode::where('serial_no', $bar['tag'])->first();
                $bin = Bin::where('Bin_Name', $bar['bin'])->first();

                // Check if necessary data is retrieved from the database

                if ($grnno->status == '1') {

                    return response()->json(['code' => 401, 'status' => 'Storage scan already completed']);
                }


                if (!$bin) {

                    return response()->json(['code' => 401, 'status' => 'Invalid Bin']);
                }

                if (!$tag) {

                    return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
                }
                // Check barcode status
                if ($tag->status == 2) {
                    return response()->json(['code' => 404, 'status' => 'Barcode Missing']);
                }
                $qc_scanned = DB::table('qcs')->where('grn_id', $grnno->id)
                    ->where('item_id', $tag->item_id)->first();

                if ($array > $qc_scanned->rejected_qty) {
                    return response()->json(['code' => 404, 'status' => 'Rejection Qunatity Exceeding']);
                }

                // Begin transaction
                DB::beginTransaction();

                // Create a new RejectionScan record
                $storage_scan = new RejectionScan([
                    'barcode' => $bar['tag'],
                    'grn_id' => $grnno->id,
                    'bin_code' => $bin->id,
                    'rejected_qty' => $tag->net_weight,
                    'user_id' => 1, // You may want to change this to the actual user ID
                    'batch_no' => $tag->batch_number,
                    'item_id' => $tag->item_id,
                    'netweight' => $tag->net_weight,
                    'scan_time' => $currentTime,
                ]);

                $storage_scan->save();

                $grn_sub = Grn_sub::where('grn_id', $grnno->id)

                    ->where('batch_no', $tag->batch_number)
                    ->where('item_id', $tag->item_id)->first();

                $status = "6";
                Barcode::where('serial_no', $bar['tag'])
                    ->update([
                        'status' => $status,
                        'bin_id' => $bin->id,
                        'qc_approval_status' => "2",
                    ]);

                // Update scanned_qty and rejected_qty in Grn_sub table
                if ($tag->status == 1) {

                    Grn_sub::where('grn_id', $grnno->id)

                        ->where('batch_no', $tag->batch_number)
                        ->where('item_id', $tag->item_id)
                        ->update([

                            'scanned_qty' => $grn_sub->scanned_qty - $tag->net_weight,

                        ]);


                    StorageScan::where('grn_id', $grnno->id)

                        ->where('barcode', $tag->serial_no)
                        ->where('item_id', $tag->item_id)
                        ->delete();
                }


                Grn_sub::where('grn_id', $grnno->id)

                    ->where('batch_no', $tag->batch_number)
                    ->where('item_id', $tag->item_id)
                    ->update([

                        'rejected_qty' => $grn_sub->rejected_qty + $tag->net_weight,

                    ]);




                $grn_sub1 = Grn_sub::where('grn_id', $grnno->id)

                    ->where('batch_no', $tag->batch_number)
                    ->where('item_id', $tag->item_id)->first();


                $qc_scanned = DB::table('qcs')->where('grn_id', $grnno->id)
                    ->where('item_id', $tag->item_id)->first();


                // dd( $grn_id);
                //  dd($qc_scanned->rejected_qty);





                $total_scan_qty = $grn_sub1->scanned_qty + $grn_sub1->rejected_qty;

                if ($grn_sub1->qty == $total_scan_qty) {

                    Grn_sub::where('grn_id', $grnno->id)

                        ->where('batch_no', $tag->batch_number)
                        ->where('item_id', $tag->item_id)
                        ->update([

                            'grn_status' => 1,

                        ]);
                }


                $grn_sub_status = Grn_sub::select('grn_id')->where('grn_id', $grnno->id)
                    ->where('grn_status', 0)->first();


                if (!$grn_sub_status) {
                    // dd('dd');
                    Grn::where('id', $grnno->id)
                        ->update([

                            'status' => 1,

                        ]);
                }
                // Commit transaction
                DB::commit();
            } catch (\Exception $e) {
                // Handle database insertion errors
                DB::rollBack();
                Log::error('Database insertion error: ' . $e->getMessage());
                // Return an error response
                return response()->json(['code' => 500, 'status' => 'Error: ' . $e]);
            }
        }

        // Return success response after the loop completes
        return response()->json(['code' => 200, 'status' => 'Success']);
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
