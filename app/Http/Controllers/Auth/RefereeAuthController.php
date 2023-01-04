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
            'password' => 'required|min:6'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // save user to database
        $admin_user = Referee::create([
            'fullname' => $request->name,
            'contact' => $request->contact,
            'email' => $request->email,
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
            'passport' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'visapage' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'emiratesIdFront' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'emiratesIdBack' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check if previous steps are completed
        $user = $request->user();

        $passport_path = null;
        $visapage_path = null;
        $emiratesIdFront = null;
        $emiratesIdBack = null;

        if($user->fullname == null || $user->email == null || $user->password == null){
            return response()->json([
                'error' => 'incompleted previous step'
            ] , 400);
        }

        if($user->ppcopy != null && $user->visapage != null && $user->emiratesIdFront != null && $user->emiratesIdBack != null){
            return response()->json([
                'error' => 'already uploaded images'
            ] , 400);
        }

        // upload files
        if ($request->hasFile('passport')) {
            $passport_path = $this->UploadFile($request->file('passport'), 'Referee/Passport');
        }

        if ($request->hasFile('visapage')) {
            $$passport_path = $this->UploadFile($request->file('visapage'), 'Referee/VisaPage');
        }

        if ($request->hasFile('emiratesIdFront')) {
            $emiratesIdFront = $this->UploadFile($request->file('emiratesIdFront'), 'Referee/EmiratesIDFront');
        }

        if ($request->hasFile('emiratesIdBack')) {
            $emiratesIdBack = $this->UploadFile($request->file('emiratesIdBack'), 'Referee/EmiratesIDBack');
        }

        $referee_object = Referee::where('email' , $user->email)->first();
        $referee_object->ppcopy = $passport_path;
        $referee_object->visapage = $visapage_path;
        $referee_object->emiratesIdFront = $emiratesIdFront;
        $referee_object->emiratesIdBack = $emiratesIdBack;
        $referee_object->update();

        return response()->json([
            'message' => 'Files uploaded successfully',
            'passport' => $passport_path,
            'visapage' => $visapage_path,
            'emiratesIdFront' => $emiratesIdFront,
            'emiratesIdBack' => $emiratesIdBack
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
        if($user->ppcopy == null || $user->visapage == null || $user->emiratesIdFront == null || $user->emiratesIdBack == null){
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

        if(checkOTPExpired($expire_date)){
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
        $otp_check = RefereeOtp::where('userId' , $user->id);

        if($otp_check){
            if( !checkOTPExpired($otp_check->expireTime) ){
                return response()->json([
                    'message' => 'Previous otp is not expired yet!'
                ] , 400);
            }
        }

        // delete previous Otp
        RefereeOtp::where('userId' , $user->id)->delete();

        // generate new otp
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


    private function checkOTPExpired($expire_time){

        $now_date = new DateTime();
        $expire_date = new DateTime($expire_time);
        $expired = false;

        if($now_date > $expire_date){
            $expired = true;
        }

        return $expired;
    }


}
