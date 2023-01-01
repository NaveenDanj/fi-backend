<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerSubmission;


class CustomerSubmissionController extends Controller
{

    public function customerSubmission1(){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'firstName' => 'required|string',
            'lastName' =>  'required|string',
            'contact' =>  'required|string|unique:customer_submissions',
            'email' =>  'required|string|email|unique:customer_submissions',
            'salary' =>  'required|number',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // save the step 1 details
        CustomerSubmission::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'contact' => $request->contact,
            'email' => $request->email,
            'salary' => $request->salary,
            'refereeId' => $request->user()->id
        ]);




    }

    public function customerSubmission2(){

    }

    public function customerSubmission3(){

    }

    public function customerSubmission4(){

    }

}
