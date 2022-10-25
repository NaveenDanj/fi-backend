<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\RefereeAuthController;

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

    // customer
    Route::post('/customer-register' , [CustomerAuthController::class , 'customerRegister']);
    Route::post('/customer-login' , [CustomerAuthController::class , 'customerLogin']);
    Route::middleware(['auth:sanctum' , 'abilities:web'])->get('/customer-me' , [CustomerAuthController::class , 'currentUser']);
    // admin
    Route::post('/admin-register' , [AdminAuthController::class , 'adminRegister']);
    Route::post('/admin-login' , [AdminAuthController::class , 'adminLogin']);
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/admin-me' , [AdminAuthController::class , 'currentAdminUser']);

    // referee registration steps
    Route::post('/referee-register-step1' , [RefereeAuthController::class , 'refereeRegisterStep1']);
    Route::post('/referee-register-step2' , [RefereeAuthController::class , 'refereeRegisterStep2']);

});
