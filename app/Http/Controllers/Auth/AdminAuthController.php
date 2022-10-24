<?php

namespace App\Http\Controllers\Auth;
use App\Models\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    
    public function adminRegister(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|string|email|unique:admins',
            'password' =>  'required|min:6',
            'deviceType' =>  'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $admin_user = Admin::create([
            'fullname' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin_user->createToken($request->deviceType)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ] , 200);

    }

    public function adminLogin(Request $request){

    }

}
