<?php

namespace App\Http\Controllers;
use App\Traits\Upload;
use Illuminate\Http\Request;
use App\Models\CustomerSubmission;
use App\Models\Referee;


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


        $introducer_user = $request->user();

        if($introducer_user->role != 'introducer'){
            return response()->json([
                'message' => 'Invalid user role'
            ] , 403);
        }

        $submissions = $this->getSubmissionsBelongsToIntroducer($introducer_user->id);

        return response()->json([
            'submissions' => $submissions
        ]);

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

        if($submission->status == 'Activated'){
            return response()->json(['message' => 'Status is activated'], 400);

        }

        // check if admin is whether a admin or introducer
        $introducer_user = $request->user();

        if($introducer_user->role == 'admin'){

            $submission->status = $request->status;
            $submission->update();

            return response()->json([
                'message' => 'submission status updated successfully',
                'submission' => $submission
            ]);

        }else{

            $belongs_to = $this->getSubmissionIntroducer($submission->id);

            if($belongs_to->id != $introducer_user->id){

                return response()->json([
                    'message' => 'Unauthorized!',
                ] , 403);

            }else{

                $submission->status = $request->status;
                $submission->update();

                return response()->json([
                    'message' => 'submission status updated successfully',
                    'submission' => $submission
                ]);

            }

        }




    }


    private function checkSubmission($id){

        $submission_exists = CustomerSubmission::where('id' , $id)->first();
        if(!$submission_exists){
            return false;
        }

    }

    private function getSubmissionsBelongsToIntroducer($id){

        $referees_belongs_to_introducer = Referee::where('introducerId' , $id)->get();
        $referees = [];

        foreach ($referees_belongs_to_introducer as $referee){
            $referees[] = $referee->id;
        }

        $submissions = CustomerSubmission::whereIn('refereeId' , $referees)->get();
        return $submissions;
    }

    private function getSubmissionIntroducer($id){
        $submissions = CustomerSubmission::whereIn('id' , $id)->first();
        $referee = Referee::where('id' , $submissions->refereeId)->first();
        $introducer_user = Admin::where('id' , $referee->introducerId )->first();
        return $introducer_user;
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

    private function handleRefereeCommision($submission , $referee){

        // get the commision rate from the database

        // add the commission to the referee wallet

        // update the wallet transaction table


    }

}
