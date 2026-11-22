<?php

namespace App\Http\Controllers\Api_Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Barcode;
use App\Models\StorageScan;
use App\Models\RejectionScan;
use App\Models\Bin;
use App\Models\Grn_sub;
use App\Models\Grn;
Use Auth;
use App\Models\Branch;
Use DB;

class StorageScanapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
       
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
       
        //
        // dd($request->data);

        $headers = apache_request_headers();
        header('Content-Type: application/json');
        $json_content = file_get_contents('php://input'); 
        $json=json_decode($json_content,true);



       

        
        //  $bin=DB::table('bins')->get();
        // $barcodetble=DB::table('barcodes')->get();
        $grn_id=DB::table('grns')->get();
        $currentTime = Carbon::now();
        $currentTime=$currentTime->toDateTimeString();
        // $branchid=Auth::user()->branch_id;
        // $locid=Auth::user()->location_id;
        // $userid=Auth::user()->id;
        // $re_grn_no = $request->grn_no;
        // $re_bin = $request->bin;

        // $this->validate($request, [
        //     'bin' => 'required',
        // ]);
        // $barcode_arr = substr($request->final_barcode,0,-1);
        // $barcode_arr = explode('|', $barcode_arr);
        // $e_message = "";
        // $s_message = "";
        // $barc = "";
         $cm_msg = "";
        // // dd($barcode_arr);
        // $flag = 0;

        // $bins = $bin->where('Bin_Name', "$re_bin")->first();
      

    //  dd($grnno);
       
     foreach ($json['data'] as $bar) {
    try {
        // Retrieve or instantiate necessary models for database insertion
        $grnno = Grn::where('grn_no', $request->grnno)->first();
        $tag = Barcode::where('serial_no', $bar['tag'])->first();
        $bin = Bin::where('Bin_Name', $bar['bin'])->first();
        
        // Check if necessary data is retrieved from the database
        if (!$grnno) {
            return response()->json(['code' => 401, 'status' => 'Invalid GRN']);
        }
        
        if (!$tag) {
            return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
        }
          else if($tag->status==1)
         {
                        return response()->json(['code' => 401, 'status' => 'Already Scanned']);
         }

        if (!$bin) {
            return response()->json(['code' => 401, 'status' => 'Invalid Bin']);
        }

        if ($tag->status == 2) {
            return response()->json(['code' => 404, 'status' => 'Barcode Missing']);
        }

        // Proceed with database insertion if all necessary data is available
        $storageScan = new StorageScan;
        $storageScan->barcode = $bar['tag'];
        $storageScan->grn_id = $grnno->id;
        $storageScan->bin_code = $bin->id;
        $storageScan->scanned_qty = $tag->net_weight;
        $storageScan->batch_no = $tag->batch_number;
        $storageScan->item_id = $tag->item_id;
        $storageScan->netweight = $tag->net_weight;
        $storageScan->scan_time = now(); // Assuming you're using Laravel's Carbon for timestamps
        // dd( $storageScan);
        // Update the status of the barcode entry
        $status = "1";
        Barcode::where('serial_no', $bar['tag'])
            ->update([
                'status'             => $status,
                'bin_id'             => $bin->id,
                'qc_approval_status' => "1",
            ]);

        // Save the StorageScan model
        $storageScan->save();
        // dd( $storageScan);
        
        // Update the scanned quantity in the Grn_sub table
        $grn_sub = Grn_sub::where('grn_id', $grnno->id)
                            ->where('batch_no', $tag->batch_number)
                            ->where('item_id', $tag->item_id)
                            ->first();

        // Check if the scanned quantity exceeds the expected quantity
        $qc_scanned = DB::table('qcs')->where('grn_id', $grnno->id)
                                       ->where('item_id', $tag->item_id)
                                       ->first();

        if ($grn_sub->scanned_qty >= $qc_scanned->scanned_qty) {
            return response()->json(['code' => 404, 'status' => 'StorageScan Quantity Exceeded']);
        }

        // Update the scanned quantity
        Grn_sub::where('grn_id', $grnno->id)
                ->where('batch_no', $tag->batch_number)
                ->where('item_id', $tag->item_id)
                ->update([
                    'scanned_qty' => $grn_sub->scanned_qty + $tag->net_weight,
                ]);

        // Optionally, change the status in the Grn_sub table if necessary
        // For example, if the scanned quantity reaches the expected quantity
        if ($grn_sub->qty == ($grn_sub->scanned_qty + $tag->net_weight)) {
            Grn_sub::where('grn_id', $grnno->id)
                   ->where('batch_no', $tag->batch_number)
                   ->where('item_id', $tag->item_id)
                   ->update(['grn_status' => 1]);
        }
        
    } catch (\Exception $e) {
        // Handle any exceptions or errors that occur during database insertion
        // Log the error for debugging purposes
        Log::error('Database insertion error: ' . $e->getMessage());
        // Continue with the loop execution even if an error occurs
        continue;
    }
}

// If the loop completes without errors, return a success response
return response()->json(['code' => 200, 'status' => 'Success']);
    }
        // $data='';
        // if($e_message != null){
        //     $msg = explode('+',$e_message);
        //     $i = 0;
        //     foreach($msg as $ms){
        //         if($ms != null){
        //             $trim = explode('&',$ms);
        //             $data.= "<tr><td>$trim[0]</td><td><font color='red'>$trim[1]</font></td></tr>";
        //         }
        //     }
        //     // dd($msg);
        // }
        // dd($s_message);

        // if($s_message != null){
        //     $msg = explode('+',$s_message);
        // // dd($msg);

        //     $i = 0;
        //     foreach($msg as $ms){
        //         if($ms != null){
        //             $trim = explode('&',$ms);
        //             if(isset($trim[1])){
        //         $err = $trim[1];
        //         $data.= "<tr><td>$trim[0]</td><td><font color='green'>$err</font></td></tr>";

        //             }

        //         }
        //     }
        // }
        // $final = [];
        // $final["msg"] = $data;
        // $final["bar"] = $barc;
        // $final["cmb"] = $cm_msg;
        // // dd($final);
        // // dd($flag);

        // return json_encode($final);
     
         
// dd($grnno);
        
       
    
        
        
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
