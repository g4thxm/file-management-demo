<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use DB;

class ReceiptapiController extends Controller
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


       $transfer = DB::table('transfer_receipts')
    ->select('receipt_no AS transfer')
    ->where('status', 0) // Filter for status 0
    ->get();

// dd($transfer); // Check what $transfer contains

if ($transfer->isNotEmpty()) {
    // If $transfer is not empty, i.e., if transfer receipts were found
    return response()->json(['code' => 200, 'status' => 'Success', 'data' =>  $transfer]);
} else {
    // If $transfer is empty, i.e., if no transfer receipts were found
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
