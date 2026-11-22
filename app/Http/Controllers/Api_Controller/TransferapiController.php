<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
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
use DB;

class TransferapiController extends Controller
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

        // $transfer = DB::table('transfer_entries')
        //     ->leftJoin('transfer_scans', 'transfer_entries.id', '=', 'transfer_scans.transfer_id')
        //     ->where('transfer_entries.status', 0)
        //     // ->whereNull('transfer_scans.transfer_id')
        //     ->distinct()
        //     ->select('transfer_entries.transfer_no AS transfer')

        //     ->get();
        $transfer = DB::table('transfer_entries')
            ->leftJoin('transfer_scans', 'transfer_entries.id', '=', 'transfer_scans.transfer_id')
            ->where('transfer_entries.status', 0)
            ->distinct()
            ->select('transfer_entries.transfer_no AS transfer')
            ->orderBy('transfer_entries.transfer_no', 'asc') // Ascending order
            ->get();



        if ($transfer->isNotEmpty()) {
            // If $dispatch_numbers is not empty, i.e., if dispatch numbers were found
            return response()->json(['code' => 200, 'status' => 'Success', 'data' =>  $transfer]);
        } else {
            // If $dispatch_numbers is empty, i.e., if no dispatch numbers were found
            return response()->json(['code' => 404, 'status' => 'Failed']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        dd(1);
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
