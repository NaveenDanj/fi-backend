<?php

namespace App\Http\Controllers;
use App\Traits\Upload;
use Illuminate\Http\Request;
use App\Models\CustomerSubmission;


class CustomerSubmissionController extends Controller
{

    use Upload;

    public function customerSubmission1(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|string|max:100',
            'company' =>  'string|max:20',
            'contact' =>  'required|string|unique:customer_submissions',
            'email' =>  'string|email|unique:customer_submissions',
            'salary' =>  'required|numeric',
            'lat' => 'string',
            'long' => 'string',
            'consent_of_lead' => 'boolean|required',
            'contacted_by_FCB' => 'boolean|required'
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
            'status' => 'Submitted',
            'consent_of_lead' => $request->consent_of_lead,
            'contacted_by_FCB' => $request->contacted_by_FCB
        ]);

        // returned the saved user object

        return response()->json([
            'message' => 'Submission added successfully!',
            'submission' => $submission
        ]);


    }

    public function getMySubmissions(Request $request){

        $my_submissions = CustomerSubmission::where('refereeId' , $request->user()->id)->paginate(15);

        return response()->json([
            'submissions' => $my_submissions
        ]);

    }

    public function getAllSubmissions(Request $request){

        $my_submissions = CustomerSubmission::paginate(15);

        return response()->json([
            'submissions' => $my_submissions
        ]);

    }

    public function getSubmissionForIntroducer(Request $request){

    }


    public function updateSubmissionState(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'submission_id' => 'required|numeric',
            'status' => 'required|in:Submitted,Contacted,AECB_Checked,Pending,Approved,Delivered,Activated'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $submission = CustomerSubmission::where('id' , $request->submission_id)->first();

        if(!$submission){
            return response()->json(['message' => 'Submission not found'], 404);
        }

        $submission->status = $request->status;
        $submission->update();

        return response()->json([
            'message' => 'submission status updated successfully',
            'submission' => $submission
        ]);

    }


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
