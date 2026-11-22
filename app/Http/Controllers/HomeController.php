<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Call;
use App\Models\User;
use App\Models\Service;
use App\Models\Barcode;

use DB;
use Auth;

use App\Models\Callsub;
use App\Models\GroupMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
	public function index()
    {
        return view('home', [
            'branches' => Branch::orderBy('name')->get()
        ]);
    }
	
	// public function call()
    // {
	// 	flash('Call details page flash' )->success();
    //     return view('home');
    // }
//    public function service(){
//        return view('service');
//    }
}
