<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bin;
use App\Models\Item;
use App\Models\PhysicalVerification;
use App\Models\PhysicalVerificationSub;
Use App\Models\Barcode;
use DB;
use Auth;
use Carbon\Carbon;
class PhysicalScanapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    $headers = apache_request_headers();
    header('Content-Type: application/json');
    $json_content = file_get_contents('php://input');
    $json = json_decode($json_content, true);

    $currentTime = Carbon::now()->toDateTimeString();
    $today = date('Y-m-d');
    $phyno = PhysicalVerification::where('pv_no', $request->phyno)->first();

    if (!$phyno) {
        return response()->json(['code' => 404, 'status' => 'Physical verification entry not found']);
    }

    if ($request->quantity) {
        $partial = 1;
    } else {
        $partial = 0;
    }

    DB::beginTransaction();

    foreach ($json['data'] as $bar)
     {
         
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
         
        $newbarcode = $bar['tag'];

        $bin = Bin::where('Bin_Name', $bar['bin'])->first();
        if (!$bin) {
            DB::rollBack();
            return response()->json(['code' => 404, 'status' => 'Bin not found']);
        }

        $storage_loc_id = $bin->storage_loc_id;

        if ($phyno->location != $storage_loc_id) {
            DB::rollBack();
            return response()->json(['code' => 401, 'status' => 'Bin location does not belong to physical entry location']);
        }

        if ($newbarcode != null) {
            $physical_entry_sub = PhysicalVerificationSub::where('barcode', $newbarcode)->first();
            if ($physical_entry_sub) {
                DB::rollBack();
                return response()->json(['code' => 404, 'status' => 'Already Scanned']);
            }

            $tag = Barcode::where('serial_no', $newbarcode)->where('status', "1")->first();
            if (!$tag) {
                DB::rollBack();
                return response()->json(['code' => 404, 'status' => 'Invalid Barcode']);
            }

            if ($today > $tag->expiry_date) {
                DB::rollBack();
                return response()->json(['code' => 404, 'status' => 'Item Expired']);
            }

            $item_id = $tag->item_id;
            $item = Item::where('id', $item_id)->first();
            $type =  '';

        if($item->item_type=='FG' )
                {
			
                    $type='FG';

                        
                if($request->quantity){
                    
                    $quantity=$request->quantity;
                }
                else{
                    
                    $quantity=$tag->spq_qty;	
                 }

                 $single_weight=$item->net_weight/$item->spq_quantity;
		          $updated_quantity=$single_weight*$quantity;
                //   dd( $updated_quantity);

                }
                else{
		
                    if($request->quantity){
                        
                    $quantity=$request->quantity;
                    
                    }
                    else{
                    $quantity=$tag->net_weight;	
                    }
                        
                 }

            $old_location_id = $tag->location_id;
        
            $old_bin_id = $tag->bin_id;
            $old_spq = $tag->spq_qty;
            // dd($old_spq);
            $old_net_weight = $tag->net_weight;
            // dd( $old_net_weight);
            $barcode_status = $tag->status;

            // dd( $new_net_weight);
            if ($type == 'FG') {
                $new_spq = $quantity;
                // dd($new_spq);
                $new_net_weight = $updated_quantity;
                // dd($updated_quantity);
                $varience_spq = $new_spq - $old_spq;
                $varience_net_weight = $new_net_weight - $old_net_weight;
            } else {
                $new_spq = '';
                $new_net_weight = $quantity;
                $varience_spq = '';
                $varience_net_weight = $new_net_weight - $old_net_weight;
            }

            //   dd($new_net_weight - $old_net_weight);
            $status = 0;
            //    dd($varience_net_weight);

            if ($storage_loc_id == $old_location_id)
             {
                //  dd('111');
                if ($varience_net_weight == 0) 
                
                {
                    $status = 0; // synchronized
                } elseif ($varience_net_weight < 0)
                 {
                    $status = 1; // deficiency
                }else{
                    $status = 2;
                }
            } else {
                // dd('222');
                // surplus
                $status = 2;
            }
                //  dd($phyno->pv_no);

            // $physicalId = $request->phyno;
            // // Check if $physicalId is empty or null, then set it to 0
            // $physicalId = empty($physicalId) ? 0 : $physicalId;
            $barcodeinc = new PhysicalVerificationSub([
                'physical_id' =>  $phyno->id,
                // dd($phyno),
                'barcode' => $newbarcode,
                'item_id' => $item_id,
                'old_location_id' => $tag->location_id,
                'new_location_id' => $storage_loc_id,
                'old_bin_id' => $tag->bin_id,
                'new_bin_id' => $bin->id,
                'old_spq' => $old_spq,
                'new_spq' => $new_spq,
                'old_net_weight' => $old_net_weight,
                'new_net_weight' => $new_net_weight,
                'varience_spq' => $varience_spq,
                'varience_net_weight' => $varience_net_weight,
                'barcode_status' => $barcode_status,
                'scan_type' => $partial,
                'scan_time' => $currentTime,
                'status' => $status,
            ]);

            // dd($barcodeinc);

            $barcodeinc->save();
        }
    }
    // dd($barcodeinc);

    DB::commit();
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
