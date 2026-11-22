<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use DB;

class GrnapiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $headers = apache_request_headers();
	    header('Content-Type: application/json');
        $json_content = file_get_contents('php://input'); 
        $json=json_decode($json_content,true);

       $grnno = DB::table('grns')
        ->join('qcs','qcs.grn_id','=','grns.id')
        ->select('grn_no AS grnno')
        ->where('grns.status','0')->where('grns.qc_status','1')
        ->where('qcs.scanned_qty','!=','0')->GroupBy('grns.grn_no')
        ->get();
        // dd($grnno);
       if ($grnno) {
            // If $grnno is provided
            return response()->json(['code' => 200, 'status' => 'Success', 'data' => [['grnno' => $grnno]]]);
        } else {
            // If $grnno is not provided
            return response()->json(['code' => 404, 'status' => 'Failed']);
        }
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
        $json=json_decode($json_content,true);

        return response()->json(['message' => 'Hello, world!']);
        //dd(1);
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
