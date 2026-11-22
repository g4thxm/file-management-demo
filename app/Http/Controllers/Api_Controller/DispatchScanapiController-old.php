<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Barcode;
use App\Models\DispatchEntry;
use App\Models\DispatchEntrySub;
use App\Models\DispatchOrder;
use App\Models\DispatchOrderSub;
use App\Models\DispatchScan;
use App\Models\DispatchPicklist;
use Carbon\Carbon;
use Auth;
use Alert;
use Throwable;

class DispatchScanapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $headers = apache_request_headers();
        header('Content-Type: application/json');
        $json_content = file_get_contents('php://input');
        $json = json_decode($json_content, true);
        //  dd(1);
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
     *    public function store(Request $request)
     */
    public function store(Request $request)
    {
        // dd($request->dispatch);
        $headers = apache_request_headers();
        header('Content-Type: application/json');
        $json_content = file_get_contents('php://input');
        $json = json_decode($json_content, true);
        
        // Get current time
          $currentTime = now();
         try
         {
     
           $dispatch = DispatchEntry::where('dispatch_no', $request->dispatch)->first();
       

           $dispatch_picklist = DispatchPicklist::where('dispatch_entry_id', $dispatch->id)->first();
         
    
            if($dispatch->status == '1')
            {

                return response()->json(['code' => 401, 'status' => 'DispatchScan Already Completed']);
            }
          
            foreach ($json['data'] as $bar)
            {
            
             $newbarcode = $bar['tag'];

             if ($newbarcode != null) 
             {
                // Find barcode information
                $tag = Barcode::where('serial_no', $newbarcode)->where('status', '1')->first();
                // dd($tag);
               
                $tag = Barcode::where('serial_no', $bar['tag'])->first();
                
                $dispatchId = $dispatch->id;
                $dispatch_qty = DispatchScan::where('dispatch_id', $dispatchId)->sum('scanned_qty');
                
                $dispatch_entry_sub = DispatchEntrySub::where('dispatch_entry_id', $dispatchId)->sum('planned_qty');
                
                $barcode=Barcode::where('serial_no', $newbarcode)->sum('spq_qty');
               
                if  ($barcode > $dispatch_entry_sub) {
                    return response()->json(['code' => 404, 'status' => 'Dispatchscan Quantity Exceeded']);
                }

                if (!$tag)
                {

                    return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
                }

                if (!$bar['qty']) 
                {
                return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                } 
                else if ($tag->status == 2) {
                    return response()->json(['code' => 401, 'status' => 'Already scanned']);
                }
                 elseif ($tag->status == 8)
                  {
                    return response()->json(['code' => 401, 'status' => 'Does not Exist']);
                  }
                
                 $disp_d = DispatchEntry::leftjoin('dispatch_entry_subs', 'dispatch_entry_subs.dispatch_entry_id', '=', 'dispatch_entries.id')->where('dispatch_entries.dispatch_no', $request->dispatch)->where('dispatch_entry_subs.item_id', $tag->item_id)->first();


                 if ($disp_d != null)
                 {
                    $balance_scan = $disp_d->planned_qty -  $disp_d->scanned_qty;
                 } else 
                 {
                    return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
                 }
             
                if (!$bar['qty'])
                 {
                    if ("$balance_scan" < $tag->spq_qty) {
                        return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                    }
                }
                
                 else {
                       
                    if ("$balance_scan" < $bar['qty'] )
                     {
                        return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                    }
                }

                 $dispatch_item = DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)->pluck('item_id')->toArray();


                if (!in_array($tag->item_id, $dispatch_item)) 
                {
                    return response()->json(['code' => 401, 'status' => 'Item Mismatch']);
                }

                // Find old barcode
                $old_barcode = Barcode::where('serial_no', $newbarcode)
                ->where('status', "1")->first();
                    // dd( $old_barcode);
                DB::beginTransaction();

                $net_weight = $tag->net_weight;
                $new_net_weight = $tag->net_weight;
                $spq = $tag->spq_qty;
                $new_spq = $tag->spq_qty;
                $updated_qtys = $tag->spq_qty;
                $updated_qty =  $bar['qty'];

                $tag = $tag->serial_no;
            
                $dispatch_qty = DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)->where('item_id', $old_barcode->item_id)->first();

                //  dd($old_barcode->spq_qty - $bar['qty']);

                if ($bar['qty'] == '' || $new_spq == $bar['qty']) 
                {

                    $updated_qty = 0;
               
                    if ($bar['qty'] && $balance_scan < $bar['qty'])
                    {
                        return response()->json(['code' => 401, 'status' => 'Quantity exceeded']);
                    }
                    
                    if ($bar['qty'] > $old_barcode->spq_qty) {
                        return response()->json(['code' => 401, 'status' => 'Tag quantity exceeded']);
                    }


                    $updated_qty =  $bar['qty'];
                    $tag = $old_barcode->serial_no;


                    Barcode::where('serial_no', $newbarcode)->where('status', "1")
                        ->update([
                            'status' => "2",
                            'spq_qty' => 0,
                            'net_weight' => 0,
                            'grn_sqp_qty' => $new_spq,
                            'grn_net_weight' => $new_net_weight,
                        ]);

                    $data_check = DispatchPicklist::where('serial_no', $newbarcode)->where('dispatch_entry_id', $dispatch->id)->count();

                    if ($data_check > 0) 
                    {
                        DispatchPicklist::where('serial_no', $newbarcode)->where('dispatch_entry_id', $dispatch->id)
                            ->update([
                                'picked_qty' => $updated_qty,
                                'status' => '1',
                                'confirm_status' => '1',
                            ]);
                    } 
                    else
                     {
                        $data_picklist13 = new DispatchPicklist([
                            'dispatch_entry_id' => $dispatch->id,
                            'order_id' => $dispatch->order_id,
                            'item_id' => $old_barcode->item_id,
                            'lot_no' => $old_barcode->batch_number,
                            'serial_no' => $newbarcode,
                            'sub_qty' => $updated_qty,
                            'picked_qty' => $updated_qty,
                            'status' => "1",
                            'confirm_status' => "1"
                        ]);
                        $data_picklist13->save();
                    }
                } 

                else {

                    if ($balance_scan < $barcode_data->spq_qty) 
                    {
                        return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                    }

                    Barcode::where('serial_no', $newbarcode)->where('status', "1")
                        ->update([
                            'status' => "2",
                            'spq_qty' => 0,
                            'net_weight' => 0,
                            'grn_sqp_qty' => $new_spq,
                            'grn_net_weight' => $new_net_weight,

                        ]);

                    $data_check = DispatchPicklist::where('serial_no', $newbarcode)
                        ->where('dispatch_entry_id', $dispatch->id)
                        ->count();

                    if ($data_check > 0) {

                        DispatchPicklist::where('serial_no', $newbarcode)
                            ->where('dispatch_entry_id', $dispatch->id)
                            ->update([
                                'picked_qty' => $updated_qty,
                                'status' => '1',
                                'confirm_status' => '1',
                            ]);
                    }
                    
                     else {
                        $data_picklist13 = new DispatchPicklist([
                            'dispatch_entry_id' => $dispatch->id,
                            'order_id' => $dispatch->order_id,
                            'item_id' => $old_barcode->item_id,
                            'lot_no' => $old_barcode->batch_number,
                            'serial_no' => $newbarcode,
                            'sub_qty' => $updated_qty,
                            'picked_qty' => $updated_qty,
                            'status' => "1",
                            'confirm_status' => "1"
                        ]);
                        $data_picklist13->save();
                    }
                }
             
                $dispatch_scan = new DispatchScan([
                    'barcode' => $newbarcode,
                    'dispatch_id' => $dispatch->id,
                    'bin_code' => $old_barcode->bin_id,
                    'item_id' => $old_barcode->item_id,
                    'scanned_qty' => $updated_qty,
                    'user_id' => 1,
                    'scan_time' => $currentTime,
                ]);
               $dispatch_scan->save();
               
               
                $Dispatch_entry_subqty11 = DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)
                    ->where('item_id', $old_barcode->item_id)
                    ->first();

                $scanned_dispatch = $Dispatch_entry_subqty11->scanned_qty;

                if ($updated_qty == "") {
                    $new_updated_qty = 0;
                } else {
                    $new_updated_qty = $updated_qty;
                }
                
                DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)
                    ->where('item_id', $old_barcode->item_id)
                    ->update([
                        'scanned_qty' => $scanned_dispatch + $new_updated_qty,
                    ]);
                 
                $Dispatch_order_sub11 = DispatchOrderSub::where('order_id', $dispatch->order_id)
                    ->where('item_id', $old_barcode->item_id)
                    ->first();

               
                $scanned_order = $Dispatch_order_sub11->despatch_qty;
                
                DispatchOrderSub::where('order_id', $dispatch->order_id)
                    ->where('item_id', $old_barcode->item_id)
                    ->update([
                        'despatch_qty' => $scanned_order + $new_updated_qty,
                    ]);

                $Dispatch_entry_subqty = DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)
                    ->where('item_id', $old_barcode->item_id)->first();
                
                $planned_qty = $Dispatch_entry_subqty->planned_qty;
                $scanned_qty = $Dispatch_entry_subqty->scanned_qty;

                if ($planned_qty == $scanned_qty) 
                {
                    DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)
                        ->where('item_id', $old_barcode->item_id)
                        ->update([
                            'status' => '1',
                        ]);
                }
                

                $Dispatch_order_sub = DispatchOrderSub::where('order_id', $dispatch->order_id)
                    ->where('item_id', $old_barcode->item_id)
                    ->first();
                $order_qty = $Dispatch_order_sub->order_qty;
                $despatch_qty = $Dispatch_order_sub->despatch_qty;

                if ($order_qty == $despatch_qty) 
                {
                    DispatchOrderSub::where('order_id', $dispatch->order_id)
                        ->where('item_id', $old_barcode->item_id)
                        ->update([
                            'status' => '1',
                        ]);
                }

                $dispatch_entry_status = DispatchEntrySub::where('dispatch_entry_id', $dispatch->id)
                    ->where('status', 0)
                    ->first();


                if (!$dispatch_entry_status) {
                    DispatchEntry::where('id', $dispatch->id)
                        ->update([
                            'status' => '1',
                            'dispatch_confirm_status' => '1',
                        ]);
                }
                
                $dispatchorder_status = DispatchOrderSub::where('order_id', $dispatch->order_id)
                    ->where('status', 0)
                    ->first();
                
                if (!$dispatchorder_status) {
                    DispatchOrder::where('id', $dispatch->order_id)
                        ->update([
                            'status' => '1',
                        ]);
                }

                DB::commit();
            }
        }

        return response()->json(['code' => 200, 'status' => 'Success']);
    }
    catch(\Throwable $th)
    {
            return response()->json(['code' => 200, 'status' => '$th']);
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
