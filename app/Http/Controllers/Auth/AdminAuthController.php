<?php

namespace App\Http\Controllers\Auth;
use App\Models\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;

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
            'user' => $admin_user
        ] , 200);

    }

    public function adminLogin(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email|unique:users',
            'password' =>  'required',
        ]);

        if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])){

            $admin_user = Auth::guard('admin')->user();

            $token = $admin_user->createToken('MyApp' , ['admin'])->plainTextToken;
            // return the token
            return response()->json([
                'token' => $token,
                'user' => $admin_user
            ] , 200);

        }else{
            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);
        }

    }

    public function currentAdminUser(Request $request){
        $admin_user = Auth::user();
        return $admin_user;
    }

}
