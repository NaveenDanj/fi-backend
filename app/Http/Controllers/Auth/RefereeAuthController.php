<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\Upload;
use App\Models\Referee;
use Illuminate\Support\Facades\Hash;
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

        $request->validate([
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
            ]);
        }

        // upload files
        if ($request->hasFile('passport')) {
            $path = $this->UploadFile($request->file('passport'), 'Referee/Passport');
            Files::create([
                'path' => $path
            ]);
            $passport_path = $path;
        }

        if ($request->hasFile('visapage')) {
            $path = $this->UploadFile($request->file('visapage'), 'Referee/VisaPage');
            Files::create([
                'path' => $path
            ]);
            $visapage_path = $path;
        }

        if ($request->hasFile('emiratesIdFront')) {
            $path = $this->UploadFile($request->file('emiratesIdFront'), 'Referee/EmiratesIDFront');
            Files::create([
                'path' => $path
            ]);
            $emiratesIdFront = $path;
        }

        if ($request->hasFile('emiratesIdBack')) {
            $path = $this->UploadFile($request->file('emiratesIdBack'), 'Referee/EmiratesIDBack');
            Files::create([
                'path' => $path
            ]);
            $emiratesIdBack = $path;
        }

        return response()->json([
            'message' => 'Files uploaded successfully',
            'passport' => $passport_path,
            'visapage' => $visapage_path,
            'emiratesIdFront' => $emiratesIdFront,
            'emiratesIdBack' => $emiratesIdBack
        ]);

    }

}
