<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Location;
use App\Models\ReceiptScan;
use App\Models\TransferEntry;
use App\Models\TransferReceipt;
use App\Models\TransferReceiptSub;
use App\Models\Barcode;


class ReceiptScanapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //dd(1);
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
    $locids = TransferEntry::pluck('from_location')->unique()->values()->all();
    $transfer = TransferReceipt::where('receipt_no', $request->transfer)->first();
    // dd($transfer);

    // if (!$transfer) {
    //     return response()->json(['code' => 404, 'message' => 'Transfer not found'], 404);
    // }

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
                        
                        
        $newbarcode = $bar['tag'];
        $tag = Barcode::where('serial_no', $newbarcode)->first();

        if (!$tag) {
            return response()->json(['code' => 401, 'message' => 'Invalid Tag'], 401);
        }

        $receipt = DB::table('transfer_receipts')
            ->join('transfer_receipt_subs', 'transfer_receipt_subs.receipt_id', '=', 'transfer_receipts.id')
            ->join('items', 'items.id', '=', 'transfer_receipt_subs.item_id')
            ->where('transfer_receipts.status', '0')
            ->where('transfer_receipts.receipt_no', $request->transfer)
            ->select('transfer_receipts.*', 'transfer_receipt_subs.*', 'items.*')
            ->first();

        $bin = Bin::where('Bin_Name', $bar['bin'])->first();

        if (!$bin) {
            return response()->json(['code' => 401, 'status' => 'Invalid Bin']);
        }

        $receipt_bar = ReceiptScan::where('serial_no', $newbarcode)->first();

        if ($receipt_bar) {
            return response()->json(['code' => 401, 'status' => 'Barcode Already Scanned'], 401);
        }

        $transfers = TransferReceipt::where('receipt_no', $request->transfer)->value('transfer_no');
        // dd($transfers);
        $from_to_location = TransferEntry::where('transfer_no', $transfers)->value('from_location');
        // dd($from_to_location);
        $to_loc_bar = $tag->location_id;
        // dd(     $to_loc_bar);

        if (!in_array($from_to_location, $locids) || $from_to_location != $to_loc_bar) {
            return response()->json(['code' => 401, 'status' => 'From Location is not matched'], 401);
        }
             $transfer = TransferReceipt::where('receipt_no', $request->transfer)->first();
            //  dd($transfer);
            if (!$transfer) {
                return response()->json(['code' => 401, 'status' => 'Invalid Transfer Receipt'], 401);
            }

            // Get the TransferEntry based on transfer_no from TransferReceipt
            $transfer_entry = TransferEntry::where('transfer_no', $transfer->transfer_no)->first();
            //  dd($transfer_entry);

            if (!$transfer_entry) {
                return response()->json(['code' => 401, 'status' => 'Invalid Transfer Entry'], 401);
            }

            // Check if the Barcode's transfer_entry_id matches the TransferEntry ID
            if ($tag->transfer_entry_id != $transfer_entry->id) {
                return response()->json(['code' => 401, 'status' => 'Invalid Barcode for Transfer'], 401);
            }

        $net_qty = $tag->net_weight;

        $receipt_scan = new ReceiptScan([
            'receipt_id' => $transfer->id,
            'transfer_id' => $transfer->id,
            'item_id' => $tag->item_id,
            'serial_no' => $tag->serial_no,
            'scanned_qty' => $tag->spq_qty,
            'bin_id' => $bin->id,
            'scan_time' => $currentTime,
        ]);

        $receipt_scan->save();

        Barcode::where('serial_no', $tag->serial_no)
            ->update([
                'status' => "1",
                'location_id' => $from_to_location,
                'bin_id' => $bin->id,
            ]);

        TransferEntry::where('from_location', $from_to_location)
            ->update([
                'to_location' => $to_loc_bar,
            ]);

        $trans_receipt_sub = TransferReceiptSub::where('receipt_id', $transfer->id)
            ->where('item_id', $tag->item_id)
            ->first();

        if ($trans_receipt_sub) {
            $scannedQty = ($trans_receipt_sub->scanned_qty ?? 0) + ($tag->spq_qty ?? 0);

            $trans_receipt_sub->update([
                'scanned_qty' => $scannedQty,
            ]);
        }

        TransferReceiptSub::where('receipt_id', $transfer->id)
            ->where('item_id', $tag->item_id)
            ->update([
                'scanned_qty' => $trans_receipt_sub->scanned_qty + $tag->spq_qty,
            ]);

        $trans_receipt_sub = TransferReceiptSub::where('receipt_id', $transfer->id)
            ->where('item_id', $tag->item_id)
            ->first();

        if ($trans_receipt_sub->transfer_qty == $trans_receipt_sub->scanned_qty) {
            TransferReceiptSub::where('receipt_id', $transfer->id)
                ->where('item_id', $tag->item_id)
                ->update([
                    'status' => 1,
                ]);
        }

        $trans_receipt_sub_status = TransferReceiptSub::where('receipt_id', $transfer->id)
            ->where('status', 0)
            ->first();
    }

    if (!$trans_receipt_sub_status) {
        TransferReceipt::where('id', $transfer->id)
            ->update([
                'receipt_time' => $currentTime,
                'status' => 1,
            ]);

        DB::commit();
        return response()->json(['code' => 200, 'status' => 'Success'], 200);
    } else {
        DB::commit();
        return response()->json(['code' => 200, 'status' => 'Scanning Success'], 200);
    }

    return response()->json(['code' => 400, 'status' => 'Bad Request'], 400);
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
