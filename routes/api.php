<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerAuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('auth')->group(function (){
    Route::post('/customer-register' , [CustomerAuthController::class , 'customerRegister']);
    Route::post('/customer-login' , [CustomerAuthController::class , 'customerLogin']);
    Route::middleware('auth:sanctum')->get('/customer-me' , [CustomerAuthController::class , 'currentUser']);
});