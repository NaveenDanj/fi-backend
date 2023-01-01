<?php

namespace App\Http\Controllers;
use App\Traits\Upload;
use Illuminate\Http\Request;
use App\Models\CustomerSubmission;


class CustomerSubmissionController extends Controller
{

    use Upload;

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
        $submission = CustomerSubmission::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'contact' => $request->contact,
            'email' => $request->email,
            'salary' => $request->salary,
            'refereeId' => $request->user()->id
        ]);

        // returned the saved user object

        return response()->json([
            'message' => 'Step 1 completed!',
            'submission' => $submission
        ]);


    }

    public function customerSubmission2(){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'passport' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'visa' => 'required|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check previous step is completed or not!


        // added requrired validations for the image uploads

    }

    public function customerSubmission3(){

    }

    public function customerSubmission4(){

    }

}
