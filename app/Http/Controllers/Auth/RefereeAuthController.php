<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\Upload;
use App\Models\Referee;
use App\Models\RefereeOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use DateTime;
use Auth;

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

        // save user to database
        $referee_user = Referee::create([
            'fullname' => $request->name,
            'contact' => $request->contact,
            'email' => $request->email,
            'introducerId' => $request->introducerId,
            'password' => Hash::make($request->password),
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
            'visapage' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'image1' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'image2' => 'required|image|mimes:jpg,png,jpeg|max:2048',
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

        if($user->ppcopy != null && $user->visapage != null && $user->verification_image_1 != null && $user->verification_image_2 != null){
            return response()->json([
                'error' => 'already uploaded images'
            ] , 400);
        }

        // upload files

        if ($request->hasFile('visapage')) {
            $visapage_path = $this->UploadFile($request->file('visapage'), 'Referee/VisaPage');
        }

        if ($request->hasFile('image1')) {
            $image1_path = $this->UploadFile($request->file('image1'), 'Referee/verification1');
        }

        if ($request->hasFile('image2')) {
            $image2_path = $this->UploadFile($request->file('image2'), 'Referee/verification1');
        }


        $referee_object = Referee::where('email' , $user->email)->first();
        $referee_object->visapage = $visapage_path;
        $referee_object->verification_image_1 = $image1_path;
        $referee_object->verification_image_2 = $image2_path;
        $referee_object->update();

        return response()->json([
            'message' => 'Files uploaded successfully',
            'visapage' => $visapage_path,
            'verification_image_1' => $image1_path,
            'verification_image_2' => $image2_path
        ]);

    }

    public function refereeRegisterStep3(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'accountNo' => 'required|string',
            'accountName' => 'required|string',
            'bank' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = $request->user();

        // check previous step
        if($user->visapage == null || $user->verification_image_1 == null || $user->verification_image_2 == null){
            return response()->json([
                'error' => 'incompleted previous step'
            ] , 400);
        }

        $user->bank = $request->bank;
        $user->bankAccountNumber = $request->accountNo;
        $user->bankAccountName = $request->accountName;
        $user->update();

        // genereate otp and send to user
        $otp = mt_rand(1000 , 9999);
        $timestamp = microtime(true) * 1000;


        $expire_timestamp = $timestamp + 1000 * 60 * 1;
        $expire_date = Carbon::createFromTimestampMs($expire_timestamp)->format('Y-m-d H:i:s.u');


        $otp_obj = RefereeOtp::create([
            'userId' => $user->id,
            'otp' => $otp,
            'expireTime' => $expire_date,
            'blocked' => false
        ]);

        return response()->json([
            'message' => 'Bank details added successfully!',
            'referee' => $user,
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

        // check user has previous otp's generated
        $otp_check = RefereeOtp::where('userId' , $request->user()->id)->first();

        if($otp_check){
            if( !$this->checkOTPExpired($otp_check->expireTime) ){
                return response()->json([
                    'message' => 'Previous otp is not expired yet!'
                ] , 400);
            }
        }

        // delete previous Otp
        RefereeOtp::where('userId' , $request->user()->id)->delete();

        // generate new otp
        $otp = mt_rand(1000 , 9999);
        $timestamp = microtime(true) * 1000;


        $expire_timestamp = $timestamp + 1000 * 60 * 1;
        $expire_date = Carbon::createFromTimestampMs($expire_timestamp)->format('Y-m-d H:i:s.u');


        $otp_obj = RefereeOtp::create([
            'userId' => $request->user()->id,
            'otp' => $otp,
            'expireTime' => $expire_date,
            'blocked' => false
        ]);

        // send

        return response()->json([
            'message' => 'OTP sent'
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

    public function refereeMe(Request $request){
        $user = $request->user();
        return response()->json([
            'referee' => $user
        ]);
    }

}
