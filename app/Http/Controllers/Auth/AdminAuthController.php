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
            'role' => 'required|in:admin,introducer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $admin_user = Admin::create([
            'fullname' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
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

    public function adminLogin(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' =>  'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

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

    public function deleteIntroducer(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'email' => 'required|string|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // delete introducer user
        $introducer = Admin::where('email' , $request->email)->where('role' , 'introducer')->first();

        if(!$introducer){
            return response()->json([
                'message' => 'Introducer account not found'
            ] , 404);
        }

        // soft delete the admin account
        $introducer->delete();

        return response()->json([
            'message' => 'Introducer account deleted successfully',
            'deleted_account' => $introducer
        ]);

    }

    public function getAllAdmin(Request $request){

        $admins = Admin::where('role' , 'admin')->paginate(15);

        return response()->json([
            'admin_users' => $admins
        ]);

    }

}
