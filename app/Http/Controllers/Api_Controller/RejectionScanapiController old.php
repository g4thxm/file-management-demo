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
Use Auth;
use App\Models\Branch;
Use DB;

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

    foreach ($json['data'] as $bar) {
        try {
            // Retrieve necessary data from the database
            $grnno = Grn::where('grn_no', $request->grnno)->first();
            $tag = Barcode::where('serial_no', $bar['tag'])->first();
            $bin = Bin::where('Bin_Name', $bar['bin'])->first();

            // Check if necessary data is retrieved from the database
          
                 if (!$bin) 
                {

                   return response()->json(['code' => 401, 'status' => 'Invalid Bin']);
                }

                if(!$tag)
                {
                    
                    return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
            
                }
            // Check barcode status
            if ($tag->status == 2) {
                return response()->json(['code' => 404, 'status' => 'Barcode Missing']);
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
          
                ->where('batch_no',$tag->batch_number)
                ->where('item_id',$tag->item_id)->first();

            $status = "6";
            Barcode::where('serial_no', $bar['tag'])
                ->update([
                    'status' => $status,
                    'bin_id' => $bin->id,
                    'qc_approval_status' => "2",
                ]);

            // Update scanned_qty and rejected_qty in Grn_sub table
         if($tag->status==1)
            {
               
                Grn_sub::where('grn_id', $grnno->id)
               
                ->where('batch_no',$tag->batch_number)
                ->where('item_id',$tag->item_id)
                ->update([
    
                   'scanned_qty' => $grn_sub->scanned_qty - $tag->net_weight,
                   
                ]);


                StorageScan::where('grn_id', $grnno->id)
               
                ->where('barcode',$tag->serial_no)
                ->where('item_id',$tag->item_id)
                ->delete();  
    
            }


            Grn_sub::where('grn_id', $grnno->id)
           
            ->where('batch_no',$tag->batch_number)
            ->where('item_id',$tag->item_id)
            ->update([

               'rejected_qty' => $grn_sub->rejected_qty + $tag->net_weight,
               
            ]);



            
            $grn_sub1 = Grn_sub::where('grn_id', $grnno->id)
          
            ->where('batch_no',$tag->batch_number)
            ->where('item_id',$tag->item_id)->first();


            $qc_scanned = DB::table('qcs')->where('grn_id', $grnno->id)
            ->where('item_id',$tag->item_id)->first();


            // dd( $grn_id);
            // dd($grn_sub->rejected_qty);

            if($grn_sub->rejected_qty >= $qc_scanned->rejected_qty)
            {
                return response()->json(['code' => 404, 'status' => 'Rejection Qunatity Exceeding']);
            }


            
            $total_scan_qty = $grn_sub1->scanned_qty + $grn_sub1->rejected_qty;

              if($grn_sub1->qty==$total_scan_qty)
            {

                Grn_sub::where('grn_id', $grnno->id)
               
                ->where('batch_no',$tag->batch_number)
                ->where('item_id',$tag->item_id)
                ->update([
    
                   'grn_status' => 1,
                   
                ]);

            }
            

            $grn_sub_status = Grn_sub::select('grn_id')->where('grn_id', $grnno->id)
            ->where('grn_status',0)->first();

            // Commit transaction
            DB::commit();
        } catch (\Exception $e) {
            // Handle database insertion errors
            DB::rollBack();
            Log::error('Database insertion error: ' . $e->getMessage());
            // Return an error response
            return response()->json(['code' => 500, 'status' => 'Error: ' . $e->getMessage()]);
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
