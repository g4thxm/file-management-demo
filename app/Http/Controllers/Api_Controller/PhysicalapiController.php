<?php

namespace App\Http\Controllers\Api_Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class PhysicalapiController extends Controller
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
        $json=json_decode($json_content,true);
        // dd(1);

          $phyno = DB::table('physical_verifications')
            ->select('pv_no AS phyno')
             ->where('status', 0) // Filter for status 0
            ->get();

                if ($phyno ->isNotEmpty()) {
    // If $dispatch_numbers is not empty, i.e., if dispatch numbers were found
    return response()->json(['code' => 200, 'status' => 'Success', 'data' =>$phyno ]);
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
