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
use App\Http\Controllers\StatController;

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


    // forgot password
    Route::post('/referee-forgot-password-send-otp' , [RefereeAuthController::class , 'forgotPasswordSendOTP']);
    Route::post('/referee-forgot-password-verify-otp' , [RefereeAuthController::class , 'verifyForgotPasswordOTP']);
    Route::post('/referee-forgot-password-update-password' , [RefereeAuthController::class , 'forgotPasswordAddPassword']);

    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-upload-verification-image' , [RefereeAuthController::class , 'updateRefereeVerficationImages']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-update-bank-details' , [RefereeAuthController::class , 'updateRefereeBankDetails']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-upload-profile-pic' , [RefereeAuthController::class , 'uploadRefereeProfilePic']);

    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/referee-me' , [RefereeAuthController::class , 'refereeMe']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-fcm-update' , [RefereeAuthController::class , 'updateRefereeFcmToken']);
    
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-edit-profile' , [RefereeAuthController::class , 'editRefereeProfile']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/referee-get-unread-notifications' , [RefereeAuthController::class , 'getUnreadNotifications']);
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-reset-password' , [RefereeAuthController::class , 'resetPassword']);

});

Route::prefix('stat')->group(function (){
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/referee-get-all' , [StatController::class , 'getAllReferees']);
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/statistics' , [StatController::class , 'getStatistics']);
});



Route::prefix('submission')->group(function (){
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/referee-customer-submission' , [CustomerSubmissionController::class , 'customerSubmission1'] );
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/view-my-submissions' , [CustomerSubmissionController::class , 'getMySubmissions'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/view-all-submissions' , [CustomerSubmissionController::class , 'getAllSubmissions'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/view-all-submissions-filter' , [CustomerSubmissionController::class , 'getAllSubmissionsFilter'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/view-all-submissions-calendar' , [CustomerSubmissionController::class , 'getAllSubmissionsCalendar'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/update-submission-status' , [CustomerSubmissionController::class , 'updateSubmissionState'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/update-submission-status-remark' , [CustomerSubmissionController::class , 'updateSubmissionStateRemark'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/update-submission-introducer-remark' , [CustomerSubmissionController::class , 'updateSubmissionIntroducerRemark'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/view-introducer-submissions' , [CustomerSubmissionController::class , 'getSubmissionForIntroducer'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/update-submission-assign-introducer' , [CustomerSubmissionController::class , 'updateSubmissionAssignStaff'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->get('/get-submission-pending' , [CustomerSubmissionController::class , 'checkSubmissionStatusIsSubmitted'] );
});


Route::prefix('introducer')->group(function (){
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/add-introducer' , [IntroducerController::class , 'addIntroducer'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/delete-introducer' , [AdminAuthController::class , 'deleteIntroducer'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin'])->post('/introducer-fcm-update' , [AdminAuthController::class , 'updateAdminFcmToken'] );
});

Route::prefix('meta')->group(function (){
    Route::get('/load-meta-data' , [MetaDataController::class , 'loadMetaData'] );
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/create-bug-report' , [MetaDataController::class , 'createBugReport'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/get-bug-reports' , [MetaDataController::class , 'getBugReports'] );
});

Route::prefix('payment')->group(function(){
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->post('/payment-request' , [PaymentController::class , 'paymentRequest'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/payment-request-change-status' , [PaymentController::class , 'paymentStateChange'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->get('/payment-get-all-requests' , [PaymentController::class , 'getAllPayments'] );
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/payment-get-request-by-code' , [PaymentController::class , 'getPaymentByCode'] );
    Route::middleware(['auth:sanctum' , 'abilities:referee' , 'refereeVerified'])->get('/payment-get-referee-payments' , [PaymentController::class , 'getRefereePaymentList'] );
});

Route::prefix('refree')->group(function(){
    Route::middleware(['auth:sanctum' , 'abilities:admin' , 'roleAdminRequired'])->post('/update-introducer' , [RefereeAuthController::class , 'updateRefereeIntroducer']);
});


Route::prefix('test')->group(function(){
    Route::get('/send-sms' , [RefereeAuthController::class , 'testSMS']);
    Route::post('/send-push' , [CustomerSubmissionController::class , 'sendPushMessageToWeb']);
});
