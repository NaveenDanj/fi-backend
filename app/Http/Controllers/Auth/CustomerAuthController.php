<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    
    public function customerRegister(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' =>  'required|min:6',
            'deviceType' =>  'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::create([
            'fullname' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken($request->deviceType)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ] , 200);

    }

    public function customerLogin(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email|unique:users',
            'password' =>  'required',
            'deviceType' =>  'required'
        ]);

        $user =  User::where('email' , $request->email)->first();

        if($user == null){
            return response()->json([
                'message' => 'User not found'
            ] , 404);
        }

        if (! Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);

        }

        $token = $user->createToken('default')->plainTextToken;
        // return the token
        return response()->json([
            'token' => $token,
            'user' => $user
        ] , 200);

    }

    public function currentUser(Request $request){
        return $request->user();
    }

}
