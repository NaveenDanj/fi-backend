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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email|unique:users',
            'password' =>  'required',
            'deviceType' =>  'required'
        ]);

        $admin_user = Admin::where('email' , $request->email)->first();

        if($admin_user == null){
            return response()->json([
                'message' => 'User not found'
            ] , 404);
        }

        if (! Hash::check($request->password, $admin_user->password)) {

            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);

        }

        $token = $admin_user->createToken('default')->plainTextToken;
        // return the token
        return response()->json([
            'token' => $token,
            'user' => $admin_user
        ] , 200);

    }

    public function currentAdminUser(Request $request){
        return $request->user();
    }

}
