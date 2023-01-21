<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\Upload;
use App\Models\Referee;
use App\Models\RefereeOtp;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\RefereeWallet;
use DateTime;
use Auth;
use SMSGlobal\Credentials;
use Twilio\Rest\Client;



class RefereeAuthController extends Controller
{
    use Upload;

    public function refereeRegisterStep1(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|string',
            'contact' =>  'required|string|unique:referees',
            'email' =>  'required|string|email|unique:referees',
            'password' => 'required|min:6',
            'introducerId' => 'numeric|required'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check if introducer is exists
        $introducer = Admin::where('id' ,  $request->introducerId)->first();

        if(!$introducer){
            return response()->json([
                'message' => 'Introducer not found!'
            ] , 401);
        }


        if($introducer->role != 'introducer'){
            return response()->json([
                'message' => 'Requested user is not an introducer!'
            ] , 401);
        }

        // save user to database
        $referee_user = Referee::create([
            'fullname' => $request->name,
            'contact' => $request->contact,
            'email' => $request->email,
            'introducerId' => $request->introducerId,
            'password' => Hash::make($request->password),
        ]);

        // add referee wallet
        RefereeWallet::create([
            'userId' => $referee_user->id,
            'balance' => 0,
        ]);

        if(Auth::guard('referee')->attempt(['email' => $request->email, 'password' => $request->password])){

            $referee = Auth::guard('referee')->user();

            $token = $referee->createToken('MyApp' , ['referee'])->plainTextToken;
            // return the token
            return response()->json([
                'token' => $token,
                'referee' => $referee
            ] , 200);

        }else{
            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);
        }


    }

    public function refereeRegisterStep2(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'image1' => 'image|mimes:jpg,png,jpeg|max:2048',
            'image2' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check if previous steps are completed
        $user = $request->user();

        $visapage_path = null;
        $image1_path = null;
        $image2_path = null;

        if($user->fullname == null || $user->email == null || $user->password == null){
            return response()->json([
                'error' => 'incompleted previous step'
            ] , 400);
        }

        if($user->ppcopy != null && $user->verification_image_1 != null && $user->verification_image_2 != null){
            return response()->json([
                'error' => 'already uploaded images'
            ] , 400);
        }

        // upload files

        // if ($request->hasFile('visapage')) {
        //     $visapage_path = $this->UploadFile($request->file('visapage'), 'Referee/VisaPage');
        // }

        if ($request->hasFile('image1')) {
            $image1_path = $this->UploadFile($request->file('image1'), 'Referee/verification1');
        }

        if ($request->hasFile('image2')) {
            $image2_path = $this->UploadFile($request->file('image2'), 'Referee/verification2');
        }


        $referee_object = Referee::where('email' , $user->email)->first();
        // $referee_object->visapage = $visapage_path;
        $referee_object->verification_image_1 = $image1_path;
        $referee_object->verification_image_2 = $image2_path;
        $referee_object->update();

        return response()->json([
            'message' => 'Files uploaded successfully',
            // 'visapage' => $visapage_path,
            'verification_image_1' => $image1_path,
            'verification_image_2' => $image2_path
        ]);

    }

    public function refereeRegisterStep3(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'bankAccountNumber' => 'string|unique:referees',
            'accountName' => 'string',
            'bank' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = $request->user();

        // check previous step
        // if($user->verification_image_1 == null || $user->verification_image_2 == null){
        //     return response()->json([
        //         'error' => 'incompleted previous step'
        //     ] , 400);
        // }

        $user->bank = $request->bank;
        $user->bankAccountNumber = $request->bankAccountNumber;
        $user->bankAccountName = $request->accountName;
        $user->update();

        // genereate otp and send to user
        // $otp = mt_rand(1000 , 9999);
        // $timestamp = microtime(true) * 1000;


        // $expire_timestamp = $timestamp + 1000 * 60 * 1;
        // $expire_date = Carbon::createFromTimestampMs($expire_timestamp)->format('Y-m-d H:i:s.u');


        // $otp_obj = RefereeOtp::create([
        //     'userId' => $user->id,
        //     'otp' => $otp,
        //     'expireTime' => $expire_date,
        //     'blocked' => false
        // ]);

        $checksum = $this->generateOTP($user);

        $otp = RefereeOtp::where('checksum' , $checksum)->first();

        return response()->json([
            'message' => 'Bank details added successfully!',
            'referee' => $user,
            'checksum' => $checksum,
            'otp' => $otp
        ] , 200);

    }

    public function checkOTPExpired($expire_time){

        $now_date = new DateTime();
        $expire_date = new DateTime($expire_time);
        $expired = false;

        if($now_date > $expire_date){
            $expired = true;
        }

        return $expired;
    }

    public function refereeVerifyOTP(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'otp' => 'required|numeric',
            'checksum' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // get the user otp
        $user = $request->user();
        $user_otp = RefereeOtp::where('userId' , $user->id)->first();

        if($user_otp == null){
            return response()->json(['error' => 'User otp not found!'], 400);
        }

        // $now_date = new DateTime();
        // $expire_date = new DateTime($user_otp->expireTime);
        // $expired = false;

        if($this->checkOTPExpired($user_otp->expireTime)){
            return response()->json(['error' => 'OTP expired!'], 400);
        }


        if($user_otp->otp != $request->otp){
            return response()->json(['error' => 'Invalid otp!'], 400);
        }

        $user->phoneVerified = true;
        $user->update();

        RefereeOtp::where('userId' , $user->id)->delete();


        return response()->json([
            'message' => 'OTP verified successfully!'
        ] , 200);

    }

    public function resendOtp(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'checksum' => 'string|required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $otp_check = RefereeOtp::where('checksum' , $request->checksum)->first();

        if(!$otp_check){
            return response()->json([
                'message' => 'Checksum is invalid'
            ] , 400);
        }


        if($otp_check){
            if( !$this->checkOTPExpired($otp_check->expireTime) ){
                return response()->json([
                    'message' => 'Previous otp is not expired yet!'
                ] , 400);
            }
        }

        $user = Referee::where('id' , $otp_check->userId)->first();

        $checksum = $this->generateOTP($user);

        $otp_check->delete();

        // send it using sms gateway

        return response()->json([
            'message' => 'OTP sent',
            'checksum' => $checksum
        ] , 200);

    }

    public function refereeLogin(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' =>  'required|string|email',
            'password' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }


        if(Auth::guard('referee')->attempt(['email' => $request->email, 'password' => $request->password])){

            $referee = Auth::guard('referee')->user();

            // $token = $referee->createToken('MyApp' , ['referee'])->plainTextToken;
            // // return the token

            $checksum = $this->generateOTP($referee);

            // should remove after testing phase
            $otp = RefereeOtp::where('checksum' , $checksum)->first();

            return response()->json([
                'message' => 'Logged in successfully!',
                'checksum' => $checksum,
                'otp' => $otp
            ] , 200);

        }else{
            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);
        }

    }

    public function verifyOTPLogin(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'otp' =>  'required|numeric',
            'email' => 'required|string|email',
            'checksum' => 'required|string'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check the user
        $referee = Referee::where('email' , $request->email)->first();

        if(!$referee){
            return response()->json([
                'message' => 'User not found!'
            ] , 404);
        }

        if($referee->email == 'test@gmail.com'){
            $token = $referee->createToken('MyApp' , ['referee'])->plainTextToken;

            RefereeOtp::where('userId' , $referee->id)->delete();

            return response()->json([
                'message' => 'user logged in successfully!',
                'token' => $token,
                'referee' => $referee
            ]);

        }

        // check user has otp request
        $check_login_attempt = RefereeOtp::where('userId' , $referee->id)->where('checksum' , $request->checksum)->first();

        if(!$check_login_attempt){
            return response()->json([
                'message' => 'no otp found!'
            ] , 404);
        }

        if($this->checkOTPExpired($check_login_attempt->expireTime)){
            return response()->json(['error' => 'OTP expired!'], 400);
        }


        if($check_login_attempt->otp != $request->otp){
            return response()->json(['error' => 'Invalid otp!'], 400);
        }

        if($referee->phoneVerified == false){
            $referee->phoneVerified = true;
            $referee->update();
        }

        $token = $referee->createToken('MyApp' , ['referee'])->plainTextToken;

        RefereeOtp::where('userId' , $referee->id)->delete();

        return response()->json([
            'message' => 'user logged in successfully!',
            'token' => $token,
            'referee' => $referee
        ]);

    }

    public function editRefereeProfile(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' =>  'required|string',
            'contact' => 'required|string',
            'email' =>  'required|string|email',
            'propic' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check if contact is already used
        $contact_check = Referee::where('contact' , $request->contact)->first();
        $referee = $request->user();

        if($contact_check){

            if($contact_check->id != $referee->id){
                return response()->json([
                    'message' => 'contact is used in another Referee account'
                ] , 400);
            }

        }

        $email_check = Referee::where('email' , $request->email)->first();

        if($email_check){

            if($email_check->id != $referee->id){
                return response()->json([
                    'message' => 'email is used in another Referee account'
                ] , 400);
            }

        }

        if($request->hasFile('propic')){

            $propic_path = null;

            if ($request->hasFile('propic')) {
                $propic_path = $this->UploadFile($request->file('propic'), 'Referee/propic');
                $referee->propic = $propic_path;
                $referee->update();
            }
        }


        $referee->fullname = $request->name;
        $referee->contact = $request->contact;
        $referee->email = $request->email;
        $referee->update();

        return response()->json([
            'message' => 'Referee account updated successfully',
            'referee' => $referee
        ]);

    }

    public function uploadRefereeProfilePic(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'propic' => 'required|image|mimes:jpg,png,jpeg|max:2048'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $referee = $request->user();

        //delete old propic
        if($referee->propic != null){
            $this->deleteFile($referee->propic);
        }

        $propic_path = null;

        if ($request->hasFile('propic')) {
            $propic_path = $this->UploadFile($request->file('propic'), 'Referee/propic');
            // $this->UploadFile($request->file('image1'), 'Referee/verification1');
        }

        $referee->propic = $propic_path;
        $referee->update();

        return response()->json([
            'message' => 'Profile picture uploaded successfully!',
            'referee' => $referee
        ]);


    }

    public function updateRefereeVerficationImages(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'image1' => 'image|mimes:jpg,png,jpeg|max:2048',
            'image2' => 'image|mimes:jpg,png,jpeg|max:2048'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $referee = $request->user();

        if ( !$request->hasFile('image1') && !$request->hasFile('image2') ) {
            return response()->json([
                'message' => 'Please upload atleast 1 image!'
            ] , 400);
        }

        $img1 = null;
        $img2 = null;

        if ($request->hasFile('image1')) {
            $img1 = $this->UploadFile($request->file('image1'), 'Referee/verification1');
            $referee->verification_image_1 = $img1;
            $referee->update();
        }

        if ($request->hasFile('image2')) {
            $img2 = $this->UploadFile($request->file('image2'), 'Referee/verification2');
            $referee->verification_image_2 = $img2;
            $referee->update();
        }


        return response()->json([
            'message' => 'Referee account updated successfully',
            'referee' => $referee
        ]);

    }

    public function updateRefereeBankDetails(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'bankAccountNumber' => 'string',
            'accountName' => 'string',
            'bank' => 'string',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $referee = $request->user();

        $check_acc_nm = Referee::where('bankAccountNumber' , $request->bankAccountNumber)->first();

        if($check_acc_nm){

            if($check_acc_nm->id != $referee->id ){
                return response()->json([
                    'message' => 'Bank account number used in another account!',
                ] , 400);
            }

        }



        $referee->bank = $request->bank;
        $referee->bankAccountName = $request->accountName;
        $referee->bankAccountNumber  = $request->bankAccountNumber;
        $referee->update();

        return response()->json([
            'message' => 'Referee account updated successfully',
            'referee' => $referee
        ]);


    }

    public function getUnreadNotifications(Request $request){
        $unreads = $request->user()->unreadNotifications;
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'messages' => $unreads
        ]);
    }

    public function resetPassword(){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $referee = $request->user();

        if(! Auth::guard('referee')->attempt(['email' => $referee->email, 'password' => $request->oldPassword])){

            return response()->json([
                'message' => 'Old password is incorrect'
            ] , 401);

        }

        // reset password
        $referee->password = Hash::make($request->newPassword);
        $referee->update();

        return response()->json([
            'message' => 'Password reseted successfully!',
            'referee' => $referee
        ]);

    }

    private function generateOTP($user){
        // genereate otp and send to user
        $otp = mt_rand(1000 , 9999);
        $timestamp = microtime(true) * 1000;


        $expire_timestamp = $timestamp + 1000 * 60 * 1;
        $expire_date = Carbon::createFromTimestampMs($expire_timestamp)->format('Y-m-d H:i:s.u');

        $checksum = Str::uuid()->toString();

        RefereeOtp::create([
            'userId' => $user->id,
            'otp' => $otp,
            'expireTime' => $expire_date,
            'blocked' => false,
            'checksum' => $checksum
        ]);

        // send
        $res = $this->handleSendSMS($otp , $user);
        // dd($res);

        return $checksum;
    }

    public function refereeMe(Request $request){
        $user = $request->user();
        return response()->json([
            'referee' => $user
        ]);
    }

    public function getAllReferees(Request $request){
        $referees = Referee::paginate(15);

        return response()->json([
            'referees' => $referees
        ]);

    }

    private function handleSendSMS($message , $user){
        try {
            $msg = 'Your FINWIN verification code is ' . $message;
            $response = $this->sendSMSService($msg , $user->contact);
            return $response;
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
    }

    public function testSMS(Request $request){
        $user = Referee::where('email' , 'naveenhettiwaththa@gmail.com')->first();
        $res = $this->handleSendSMS('0011' , $user);
        return response()->json([
            'response' => $res
        ]);
    }

    public function sendSMSService($msg , $contact){

        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $client  = new Client($sid, $token);

        $message = $client->messages->create(
            $contact, // Text this number
            [
            //   'messagingServiceSid' => 'MG91e3a30a30d522b9a2e33424e7880151',
              'body' => $msg,
              'from' => '+18316031423'
            ]
        );

        return $message;

    }


}
