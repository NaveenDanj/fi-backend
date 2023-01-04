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
            'name' => 'required|string|max:100',
            'company' =>  'string|max:20',
            'contact' =>  'required|string|unique:customer_submissions',
            'email' =>  'string|email|unique:customer_submissions',
            'salary' =>  'required|number',
            'lat' => 'string',
            'long' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // save the step 1 details
        $submission = CustomerSubmission::create([
            'name' => $request->name,
            'company' => $request->company,
            'contact' => $request->contact,
            'email' => $request->email,
            'salary' => $request->salary,
            'lat' => $request->lat,
            'long' => $request->lat,
            'refereeId' => $request->user()->id,
            'status' => 'incompleted'
        ]);

        // returned the saved user object

        return response()->json([
            'message' => 'Submission added successfully!',
            'submission' => $submission
        ]);


    }

    // public function customerSubmission2(){

    //     $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
    //         'passport' => 'required|image|mimes:jpg,png,jpeg|max:2048',
    //         'visa' => 'required|image|mimes:jpg,png,jpeg|max:2048',
    //         'submissionId' => 'required|number'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     // check previous submissionid is exists
    //     if( !checkSubmission($request->submissionId) ){

    //         return response()->json([
    //             'message' => 'submission doesnt exists!',
    //         ],400);

    //     }

    //     if(!checkSubmissionRefereeAccess($request->submissionId , $request->user()->id)){
    //         return response()->json([
    //             'message' => 'referee id missmatch',
    //         ],403);
    //     }

    //     // upload files
    //     if ($request->hasFile('passport')) {
    //         $passport_path = $this->UploadFile($request->file('passport'), 'Submission/Passport');
    //     }

    //     if ($request->hasFile('visa')) {
    //         $visa_path = $this->UploadFile($request->file('visa'), 'Submission/Visa');
    //     }

    //     $submission = CustomerSubmission::where('id' , $request->submissionId)->first();
    //     $submission->passportPath = $passport_path;
    //     $submission->visaPath = $visa_path;
    //     $submission->update();

    //     return response()->json([
    //         'message' => 'Step 2 completed!',
    //         'submission' => $submission
    //     ]);

    // }

    // public function customerSubmission3(){

    //     $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
    //         'idFront' => 'required|image|mimes:jpg,png,jpeg|max:2048',
    //         'idBack' => 'required|image|mimes:jpg,png,jpeg|max:2048',
    //         'submissionId' => 'required|number'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }
    // }


    private function checkSubmission($id){

        $submission_exists = CustomerSubmission::where('id' , $id)->first();
        if(!$submission_exists){
            return false;
        }

    }

    private function checkSubmissionRefereeAccess($id , $userId){
        $submission_exists = CustomerSubmission::where('id' , $id)->first();

        if(!$submission_exists){
            return false;
        }

        if($submission_exists->refereeId != $userId ){
            return false;
        }

        return true;

    }

}
