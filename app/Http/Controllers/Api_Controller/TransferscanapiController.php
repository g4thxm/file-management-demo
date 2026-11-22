<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Barcode;
use App\Models\TransferEntry;
use App\Models\TransferentrySub;
use App\Models\TransferOrder;
use App\Models\TransferorderSub;
use App\Models\TransferPicklist;
use App\Models\TransferReceipt;
use App\Models\TransferReceiptSub;
use App\Models\TransferScan;
use App\Models\TransferManualClose;
use Carbon\Carbon;
use DB;
use Alert;
use Illuminate\Support\Facades\Auth;

class TransferscanapiController extends Controller

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

        // Find dispatch entry based on dispatch number
        $transfer = TransferEntry::where('transfer_no', $request->transfer)->first();

        if (!$transfer) {
            return response()->json(['code' => 404, 'status' => 'Transfer not found']);
        }
        try {
            $transfer_picklist = TransferPicklist::where('transfer_entry_id', $transfer->id)->first();

            if ($transfer->status == '1') {

                return response()->json(['code' => 401, 'status' => 'TransferScan already completed']);
            }

            DB::beginTransaction();


            foreach ($json['data'] as $bar) {
                $newbarcode = $bar['tag'];

                if ($newbarcode != null) {
                    // Find barcode information
                    $tag = Barcode::where('serial_no', $newbarcode)->where('status', '1')->first();
                  

                    $tag = Barcode::where('serial_no', $bar['tag'])->first();

                    $transferId = $transfer->id;
                    $transfer_qty = TransferScan::where('transfer_id', $transferId)->sum('picked_qty');

                    $transfer_entry_sub = TransferEntrySub::where('transfer_entry_id', $transferId)->sum('planned_qty');
                
                    $barcode = Barcode::where('serial_no', $newbarcode)->sum('spq_qty');
                
                    if ($barcode > $transfer_entry_sub) {
                        return response()->json(['code' => 404, 'status' => 'Transferscan Quantity Exceeded']);
                    }

                    if (!$tag) {
                        return response()->json(['code' => 401, 'status' => 'Invalid Barcode']);
                    } else if ($tag->status == 4) {
                        return response()->json(['code' => 401, 'status' => 'Already Scanned']);
                    } elseif ($tag->status == 8) {
                        return response()->json(['code' => 401, 'status' => 'Does not exist']);
                    }

                    $transfer_item = TransferentrySub::where('transfer_entry_id', $transfer->id)->pluck('item_id')->toArray();
                   
              
                    if (!in_array($tag->item_id, $transfer_item)) {
                        return response()->json(['code' => 401, 'status' => 'Item mismatch']);
                    }

                    // $barcodeTransfer = TransferPicklist::where('serial_no', $newbarcode)->first();
                  
                    // if ($barcodeTransfer && $barcodeTransfer->transfer_entry_id !== $transfer->id) {
                    //     return response()->json(['code' => 401, 'status' => 'Barcode does not belong to this transfer']);
                    // }


                    $old_barcode = Barcode::where('serial_no', $newbarcode)->first();


                    // Access net_weight directly from the $old_barcode object
                    $net_weight = $old_barcode->net_weight;

                    // $tag = $old_barcode->serial_no;




                    $net_weight = $tag->net_weight;

                    $new_net_weight = $tag->net_weight;
                    $spq = $tag->spq_qty;
                    $new_spq = $tag->spq_qty;
                    $updated_qty = $bar['qty'];

                    $dispatch_qty = TransferentrySub::where('transfer_entry_id', $transfer->id)->where('item_id', $old_barcode->item_id)->first();

                    $balance_scan = $dispatch_qty->planned_qty - $dispatch_qty->scanned_qty;
                
                    if ($balance_scan < $bar['qty']) {

                        return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                    }

                    if ($new_spq == $updated_qty) {
                        $updated_net = ($net_weight / $spq) * $updated_qty;

                        if ($balance_scan < $tag->spq_qty) {

                            return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                        }
                    } else {
                        $old_barcode = Barcode::where('serial_no', $newbarcode)->first();

                        // $barcode_data = Barcode::where('serial_no', $old_barcode)->first();
                        // dd($barcode_data);

                        if ($balance_scan < $old_barcode->spq_qty) {

                            return response()->json(['code' => 401, 'status' => 'Quantity Exceeded']);
                        }
                    }

                    Barcode::where('serial_no', $newbarcode)->where('status', "1")
                        ->update([
                            'status' => "4",
                            'spq_qty' => $new_spq,
                            // 'net_weight' => ($net_weight / $spq) * $updated_qty,
                            'net_weight' => $new_net_weight,
                            'grn_sqp_qty' => $new_spq,
                            'grn_net_weight' => $new_net_weight,
                        ]);

                    $data_check = TransferPicklist::where('serial_no', $newbarcode)->where('transfer_entry_id', $transfer->id)->count();

                    if ($data_check > 0) {
                        TransferPicklist::where('serial_no', $newbarcode)->where('transfer_entry_id', $transfer->id)
                            ->update([
                                'picked_qty' =>  $new_spq,
                                'status' => '1',
                                'confirm_status' => '1',
                            ]);
                    } else {
                        $data_picklist13 = new TransferPicklist([
                            'transfer_entry_id' => $transfer->id,
                            'order_id' => $transfer->order_id,
                            'item_id' => $old_barcode->item_id,
                            'lot_no' => $old_barcode->batch_number,
                            'serial_no' => $newbarcode,
                            'sub_qty' =>  $new_spq,
                            'picked_qty'  =>   $new_spq,
                            'status' => "1",
                            'confirm_status' => "1"
                        ]);
                        $data_picklist13->save();
                    }

                    // Generate receipt number
                    $yr = 'RCPT' . date("y");
                    $len = strlen($yr);
                    $receiptNumber = DB::select("SELECT CONCAT('$yr',LPAD(max_receipt_no+1,GREATEST(5,LENGTH(max_receipt_no+1)),'0')) AS receipt_number FROM 
                        (SELECT IFNULL(MAX(SUBSTRING(`receipt_no`, $len+1)),'0') AS max_receipt_no FROM transfer_receipts WHERE receipt_no LIKE '$yr%') p2");
                    $receiptNumber = $receiptNumber[0]->receipt_number;

                    $receipt = TransferReceipt::where('from_location', $transfer->from_location)
                        ->where('to_location', $transfer->to_location)
                        ->where('transfer_no', $transfer->transfer_no)->first();

                    if (!$receipt) {
                        $transferReceipt = new TransferReceipt([
                            'receipt_no' => $receiptNumber,
                            'transfer_no' => $transfer->transfer_no,
                            'transfer_order_id' => $transfer->order_id,
                            'from_location' => $transfer->from_location,
                            'to_location' => $transfer->to_location,
                            'entry_time' => $currentTime,
                            'user_id' => 1,
                            'status' => 0,
                        ]);
                        $transferReceipt->save();
                        Log::info('New receipt created', ['receipt' => $transferReceipt]);
                        $receiptId = $transferReceipt->id;
                    } else {
                        $receiptId = $receipt->id;
                    }

                    $receiptSub = TransferReceiptSub::where('item_id', $old_barcode->item_id)->where('receipt_id', $receiptId)->first();

                    if ($receiptSub) {
                        TransferReceiptSub::where('receipt_id', $receiptId)->where('item_id', $old_barcode->item_id)
                            ->update(['transfer_qty' => $receiptSub->transfer_qty + $updated_qty]);
                    } else {
                        $transferReceiptSub = new TransferReceiptSub([
                            'receipt_id' => $receiptId,
                            'transfer_id' => $transfer->id,
                            'item_id' => $old_barcode->item_id,
                            'batch_no' => $old_barcode->batch_number,
                            'transfer_qty' => $updated_qty,
                            'scanned_qty' => 0,
                            'status' => 0,
                        ]);
                        $transferReceiptSub->save();
                        Log::info('New receipt sub created', ['receipt_sub' => $transferReceiptSub]);
                    }

                    $transferScan = new TransferScan([
                        'serial_no' => $newbarcode,
                        'transfer_id' => $transfer->id,
                        'bin_id' => $old_barcode->bin_id,
                        'item_id' => $old_barcode->item_id,
                        'picked_qty' => $new_spq,
                        'user_id' => 1,
                        'scan_time' => $currentTime,
                    ]);
                    $transferScan->save();

                    Log::info('New transfer scan created', ['scan' => $transferScan]);


                    $Dispatch_entry_subqty11 = TransferentrySub::where('transfer_entry_id', $transfer->id)->where('item_id', $old_barcode->item_id)->first();


                    $scanned_dispatch = $Dispatch_entry_subqty11->scanned_qty;


                    TransferentrySub::where('transfer_entry_id', $transfer->id)->where('item_id', $old_barcode->item_id)
                        ->update([

                            'scanned_qty' => $scanned_dispatch + $new_spq,

                        ]);

                    $Dispatch_order_sub11 = TransferorderSub::where('order_id', $transfer->order_id)->where('item_id', $old_barcode->item_id)
                        ->first();

                    $scanned_order = $Dispatch_order_sub11->transfer_qty;


                    TransferorderSub::where('order_id', $transfer->order_id)->where('item_id', $old_barcode->item_id)
                        ->update([

                            'transfer_qty' => $scanned_order + $new_spq,

                        ]);

                    $Dispatch_entry_subqty = TransferentrySub::where('transfer_entry_id', $transfer->id)->where('item_id', $old_barcode->item_id)->first();

                    $planned_qty = $Dispatch_entry_subqty->planned_qty;

                    $scanned_qty = $Dispatch_entry_subqty->scanned_qty;


                    if ($planned_qty == $scanned_qty) {
                        TransferentrySub::where('transfer_entry_id', $transfer->id)->where('item_id', $old_barcode->item_id)
                            ->update([

                                'status' => '1',
                            ]);
                    }

                    $Dispatch_order_sub = TransferorderSub::where('order_id', $transfer->order_id)->where('item_id', $old_barcode->item_id)->first();

                    $order_qty = $Dispatch_order_sub->order_qty;

                    $transfer_qty = $Dispatch_order_sub->transfer_qty;

                    if ($order_qty == $transfer_qty) {
                        TransferorderSub::where('order_id', $transfer->order_id)->where('item_id', $old_barcode->item_id)
                            ->update([

                                'status' => '1',

                            ]);
                    }

                    $dispatch_entry_status = TransferentrySub::select('transfer_entry_id')->where('transfer_entry_id', $transfer->id)
                        ->where('status', 0)->first();

                    if (!$dispatch_entry_status) {

                        TransferEntry::where('id', $transfer->id)
                            ->update([

                                'status' => '1',
                                'transfer_confirm_status' => '1',
                            ]);
                    }


                    $dispatchorder_status = TransferorderSub::where('order_id', $transfer->order_id)->where('status', 0)->first();

                    if (!$dispatchorder_status) {

                        TransferOrder::where('id', $transfer->order_id)
                            ->update([
                                'status' => '1',
                            ]);
                    }
                }
            }
            DB::commit();
            return response()->json(['code' => 200, 'status' => 'Success']);
        } catch (\Throwable $th) {
            // Rollback transaction in case of errorF
            DB::rollBack();

            // Log the entire error for debugging
            \Log::error($th);

            // Return the exception message in the JSON response
            return response()->json([
                'code' => 500,
                'status' => 'An error occurred ' . $th,
                'error' => $th->getMessage() // Show the exception message
            ]);
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
