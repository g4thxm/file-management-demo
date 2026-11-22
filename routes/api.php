<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::resource('grnapi', 'App\Http\Controllers\Api_Controller\GrnapiController');

Route::resource('dispatch', 'App\Http\Controllers\Api_Controller\DispatchapiController');

Route::resource('dispatchscan', 'App\Http\Controllers\Api_Controller\DispatchScanapiController');

Route::resource('transfer', 'App\Http\Controllers\Api_Controller\TransferapiController');

Route::resource('transferscan', 'App\Http\Controllers\Api_Controller\TransferscanapiController');

Route::resource('receipt', 'App\Http\Controllers\Api_Controller\ReceiptapiController');

Route::resource('receiptscan', 'App\Http\Controllers\Api_Controller\ReceiptScanapiController');

Route::resource('physical', 'App\Http\Controllers\Api_Controller\PhysicalapiController');

Route::resource('physicalscan', 'App\Http\Controllers\Api_Controller\PhysicalScanapiController');

Route::resource('rejection', 'App\Http\Controllers\Api_Controller\RejectionScanapiController');

Route::resource('storage', 'App\Http\Controllers\Api_Controller\StorageScanapiController');
//  Route::get('/grn', function () {
//      return response()->json(['message' => 'Hello, world!']);
//  });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
