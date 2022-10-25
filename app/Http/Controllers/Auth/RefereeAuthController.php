<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RefereeAuthController extends Controller
{

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

}
