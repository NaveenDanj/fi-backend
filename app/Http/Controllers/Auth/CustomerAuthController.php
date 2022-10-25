<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
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

        if(Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])){

            $user = Auth::guard('web')->user();

            $token = $user->createToken('MyApp' , ['user'])->plainTextToken;
            // return the token
            return response()->json([
                'token' => $token,
                'user' => $user
            ] , 200);

        }else{
            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);
        }



        return response()->json([
            'user' => $user
        ] , 200);

    }

    public function customerLogin(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email|unique:users',
            'password' =>  'required',
            'deviceType' =>  'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if(Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])){

            $user = Auth::guard('web')->user();

            $token = $user->createToken('MyApp' , ['user'])->plainTextToken;
            // return the token
            return response()->json([
                'token' => $token,
                'user' => $user
            ] , 200);

        }else{
            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);
        }

    }

    public function currentUser(Request $request){
        $admin_user = Auth::user();
        return $admin_user;
    }

}
