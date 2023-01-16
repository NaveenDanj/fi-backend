<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\RefereeAuthController;
use App\Http\Controllers\CustomerSubmissionController;
use App\Http\Controllers\IntroducerController;
use App\Http\Controllers\MetaDataController;
use App\Http\Controllers\PaymentController;

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
    Route::middleware(['auth:sanctum' , 'abilities:user'])->get('/customer-me' , [CustomerAuthController::class , 'currentUser']);

    // admin
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/admin-register' , [AdminAuthController::class , 'adminRegister']);
    Route::post('/admin-login' , [AdminAuthController::class , 'adminLogin']);
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/admin-me' , [AdminAuthController::class , 'currentAdminUser']);
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/admin-get-all-admins' , [AdminAuthController::class , 'getAllAdmin']);


    // referee registration steps
    Route::post('/referee-register-step1' , [RefereeAuthController::class , 'refereeRegisterStep1']);
    Route::middleware(['auth:sanctum' , 'abilities:referee'])->post('/referee-register-step2' , [RefereeAuthController::class , 'refereeRegisterStep2']);
    Route::middleware(['auth:sanctum' , 'abilities:referee'])->post('/referee-register-step3' , [RefereeAuthController::class , 'refereeRegisterStep3']);
    Route::middleware(['auth:sanctum' , 'abilities:referee'])->post('/referee-register-verify-otp' , [RefereeAuthController::class , 'refereeVerifyOTP']);

    Route::post('/referee-resend-otp' , [RefereeAuthController::class , 'resendOtp']);
    Route::post('/referee-login' , [RefereeAuthController::class , 'refereeLogin']);
    Route::post('/referee-login-verify-otp' , [RefereeAuthController::class , 'verifyOTPLogin']);

    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-upload-verification-image' , [RefereeAuthController::class , 'updateRefereeVerficationImages']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-update-bank-details' , [RefereeAuthController::class , 'updateRefereeBankDetails']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-upload-profile-pic' , [RefereeAuthController::class , 'uploadRefereeProfilePic']);

    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/referee-me' , [RefereeAuthController::class , 'refereeMe']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-edit-profile' , [RefereeAuthController::class , 'editRefereeProfile']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/referee-get-unread-notifications' , [RefereeAuthController::class , 'getUnreadNotifications']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/referee-reset-password' , [RefereeAuthController::class , 'resetPassword']);


    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/referee-get-all' , [AdminAuthController::class , 'getAllReferees']);


});

Route::prefix('submission')->group(function (){
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-customer-submission' , [CustomerSubmissionController::class , 'customerSubmission1'] );
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/view-my-submissions' , [CustomerSubmissionController::class , 'getMySubmissions'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/view-all-submissions' , [CustomerSubmissionController::class , 'getAllSubmissions'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/update-submission-status' , [CustomerSubmissionController::class , 'updateSubmissionState'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/view-introducer-submissions' , [CustomerSubmissionController::class , 'getSubmissionForIntroducer'] );
});


Route::prefix('introducer')->group(function (){
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/add-introducer' , [IntroducerController::class , 'addIntroducer'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/delete-introducer' , [AdminAuthController::class , 'deleteIntroducer'] );
});

Route::prefix('meta')->group(function (){
    Route::get('/load-meta-data' , [MetaDataController::class , 'loadMetaData'] );
});

Route::prefix('payment')->group(function(){
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/payment-request' , [PaymentController::class , 'paymentRequest'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/payment-request-change-status' , [PaymentController::class , 'paymentStateChange'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/payment-get-all-requests' , [PaymentController::class , 'getAllPayments'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/payment-get-request-by-code' , [PaymentController::class , 'getPaymentByCode'] );
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/payment-get-referee-payments' , [PaymentController::class , 'getRefereePaymentList'] );
});
