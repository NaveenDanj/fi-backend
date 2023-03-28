<?php

namespace App\Http\Controllers;
use App\Traits\Upload;
use Illuminate\Http\Request;
use App\Models\CustomerSubmission;
use App\Models\Referee;
use App\Models\CommisionRate;
use App\Models\RefereeWallet;
use App\Models\WalletTransaction;
use App\Models\Admin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RefereeSubmissionStateChange;

class CustomerSubmissionController extends Controller
{

    use Upload;

    public function customerSubmission1(Request $request){
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|string|max:100',
            'company' =>  'string|max:512',
            'contact' =>  'required|string|unique:customer_submissions',
            'email' =>  'string|email|unique:customer_submissions',
            'salary' =>  'required|numeric',
            'lat' => 'string',
            'long' => 'string',
            'consent_of_lead' => 'boolean|required',
            'contacted_by_FCB' => 'boolean|required',
            'remarks' => 'string|max:512'
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
            'contacted_by_FCB' => $request->contacted_by_FCB,
            'remarks' => $request->remarks
        ]);

        $response = response()->json([
            'message' => 'Submission added successfully!',
            'submission' => $submission
        ]);

        if ($response->getStatusCode() === 200) {
          //   $introducer = getRefreeIntroducer(user()->id);
            sendPushMessageToWeb('New Submission updated!','New Submission ','');
        }

        return $response;
    }

    public function getMySubmissions(Request $request){

        $my_submissions = CustomerSubmission::where('refereeId' , $request->user()->id)->orderBy('id', 'desc')->get();

        return response()->json([
            'submissions' => [
                'data' => $my_submissions
            ]
        ]);

    }

    public function getAllSubmissions(Request $request){

        $my_submissions = CustomerSubmission::all();

        foreach($my_submissions as $submission){
            $referee =  Referee::where( 'id' ,  $submission->refereeId)->first();
            $submission->referee = $referee;
            $submission->introducer = Admin::where('id' , $referee->introducerId)->first();
        }

        return response()->json([
            'submissions' => $my_submissions
        ]);

    }

    public function getAllSubmissionsFilter(Request $request){

        $my_submissions = null;

        if($request->type == 'Referee'){
            $my_submissions = CustomerSubmission::where('refereeId' , $request->id)->get();
        }else{

            // all referees belongs to introducer
            $_referees = Referee::where('introducerId' , $request->id)->get();

            $referee = [];

            foreach($_referees as $ref){
                $referee[] = $ref->id;
            }

            $my_submissions = CustomerSubmission::whereIn('refereeId' , $referee)->get();

        }

        // $my_submissions = CustomerSubmission::paginate(15);

        foreach($my_submissions as $submission){
            $referee =  Referee::where( 'id' ,  $submission->refereeId)->first();
            $submission->referee = $referee;
            $submission->introducer = Admin::where('id' , $referee->introducerId)->first();
        }

        return response()->json([
            'submissions' => $my_submissions
        ]);

    }


    public function getAllSubmissionsCalendar(Request $request){

        $my_submissions = CustomerSubmission::all();

        foreach($my_submissions as $submission){
            $referee =  Referee::where( 'id' ,  $submission->refereeId)->first();
            $submission->referee = $referee;
            $submission->introducer = Admin::where('id' , $referee->introducerId)->first();
        }

        return response()->json([
            'submissions' => $my_submissions
        ]);
    }

    

    public function getSubmissionForIntroducer(Request $request){

        $submissions = [];

        $introducer_user = $request->user();

        if($introducer_user->role != 'introducer'){
            return response()->json([
                'message' => 'Invalid user role'
            ] , 403);
        }

        $submissions = $this->getSubmissionsBelongsToIntroducer($introducer_user->id);

        foreach($submissions as $submission){
            $referee =  Referee::where( 'id' ,  $submission->refereeId)->first();
            $submission->referee = $referee;
        }


        return response()->json([
            'submissions' => $submissions
        ]);

    }


    // Get Referee introducer detials
    public function getRefreeIntroducer($id){
        $referee = Referee::where('id' , $id)->get();
        $introducer = Admin::where('id',$referee->introducerId)->get();
        return $introducer;
    }


    public function updateSubmissionState(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'submission_id' => 'required|numeric',
            'status' => 'required|in:Submitted,Contacted,AECB Checked,Documents under process,Approved,Delivered,Activated,Unreachable,Not Interested,Ineligible'
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
            // notify the referee
            $referee = Referee::where('id' , $submission->refereeId)->first();

            $this->handleRefereeCommision($submission,$referee);

            $notification_data = "Submission status has been changed!";
            Notification::send($referee, new RefereeSubmissionStateChange($referee , $submission));
            $res = $this->handlePushNotificationSend($referee,$submission);
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

                // notify the referee
                $referee = Referee::where('id' , $submission->refereeId)->first();

                $this->handleRefereeCommision($submission,$referee);

                $notification_data = "Submission status has been changed!";
                Notification::send($referee, new RefereeSubmissionStateChange($referee , $submission));
                $res = $this->handlePushNotificationSend($referee,$submission);

                return response()->json([
                    'message' => 'submission status updated successfully',
                    'submission' => $submission
                ]);

            }

        }

    }

    public function handlePushNotificationSend($referee,$submission){
        try {
            if($submission->status == 'Activated'){
                $title = 'Congrats! You Earned AED 500';
                $description = 'Wohoo you just got AED 500 richer! You can redeem your earnings today. Your submission `'.$submission->name.'` '.$submission->status;
                $response = $this->sendPushMessage($title,$description,$referee->fcm);
                return $response;
            }else{
                $title = 'Your submission `'.$submission->name.'` '.$submission->status;
                $description = 'Submission `'.$submission->name.'` status has been changed. '.$submission->statusRemarks;
                $response = $this->sendPushMessage($title,$description,$referee->fcm);
                 $msg = $this->sendPushMessageToWeb('Submission status updated!','Submission '.$submission->name.'status changed.',''); 
                return $response;
            }
    

        } catch (\Exception $e) {
            return false;
        }
    }


public function sendPushMessage($title,$description,$fcmTokens){
   
    $response = Http::post('https://exp.host/--/api/v2/push/send', [
        'to' => $fcmTokens,
        'title' => $title,
        'body' => $description,
    ]);
  //  return response($response, 200); 
    if ($response->ok()) {
        return 'Notification sent successfully';
    } else {
        return 'Failed to send notification';
    }
}


public function sendPushMessageToWeb($title,$description,$fcmTokens){
    $adminsFcm = Admin::where('role', 'admin')->whereNotNull('fcm')->pluck('fcm')->toArray();
    $allFcmTokens = array_merge($adminsFcm, $fcmTokens);

    $response = Http::withHeaders([
        'Content-Type'=>'application/json',
        'Authorization' => "key=AAAANlvvNdQ:APA91bFKY7fPxxoUFH-CS_C65pZdy8oPWjNH0mUOyBxAqmdDiqIrEeiskUFDrixNJ2w7_FHfuu8niOJHqNJJbVCvwzABTO518Sz-y3B3IypMPpU5OfbihwYlNYo7R886U6SiRETWq9Kn",
        'Content-Type' => 'application/json'
   ])->post('https://fcm.googleapis.com/fcm/send', [
        'registration_ids' => $allFcmTokens,
        'notification' => [
            'title' =>$title,
            'body' => $description,
            'mutable_content' => true,
            'sound' => 'Tri-tone'
        ],
        'data' => [
            'url' => '',
            'dl' => '/all-submission'
        ]
    ]);
    
    if ($response->ok()) {
        return 'Notification sent successfully';
    } else {
        return  'Notification sent failed';
    }
}




    public function updateSubmissionStateRemark(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'submission_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $submission = CustomerSubmission::where('id' , $request->submission_id)->first();

        if(!$submission){
            return response()->json(['message' => 'Submission not found'], 404);
        }

        // check if admin is whether a admin or introducer
        $introducer_user = $request->user();


        if($introducer_user->role == 'admin'){

            $submission->statusRemarks = $request->statusRemarks;
            $submission->update();

            // notify the referee
            $referee = Referee::where('id' , $submission->refereeId)->first();

            $this->handleRefereeCommision($submission,$referee);


            $notification_data = "Submission Remark Updated";
            Notification::send($referee, new RefereeSubmissionStateChange($referee , $submission));

            return response()->json([
                'message' => 'Submission Remark Update successfully',
                'submission' => $submission
            ]);

        }else{

            $belongs_to = $this->getSubmissionIntroducer($submission->id);


            if($belongs_to->id != $introducer_user->id){

                return response()->json([
                    'message' => 'Unauthorized!',
                ] , 403);

            }else{


                $submission->statusRemarks = $request->statusRemarks;
                $submission->update();

                $this->handleRefereeCommision($submission,$referee);

                // notify the referee
                $referee = Referee::where('id' , $submission->refereeId)->first();
                $notification_data = "Submission Remark Updated";
                Notification::send($referee, new RefereeSubmissionStateChange($referee , $submission));


                return response()->json([
                    'message' => 'Submission Remark Update successfully',
                    'submission' => $submission
                ]);

            }

        }

    }


    public function updateSubmissionIntroducerRemark(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'submission_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $submission = CustomerSubmission::where('id' , $request->submission_id)->first();

        if(!$submission){
            return response()->json(['message' => 'Submission not found'], 404);
        }

        $introducer_user = $request->user();


        if($introducer_user->role == 'admin'){

            $submission->introducerRemarks = $request->introducerRemarks;
            $submission->update();

            return response()->json([
                'message' => 'Introducer remark update successfully',
            ]);

        }else{

            $belongs_to = $this->getSubmissionIntroducer($submission->id);


            if($belongs_to->id != $introducer_user->id){

                return response()->json([
                    'message' => 'Unauthorized!',
                ] , 403);

            }else{
                
            $submission->introducerRemarks = $request->introducerRemarks;
            $submission->update();

                return response()->json([
                    'message' => 'Introducer remark update successfully',
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
        $submissionsByReferees = CustomerSubmission::whereIn('refereeId', $referees)->get();
        $submissionsByStaff = CustomerSubmission::where('assignStaff', $id)->get();

        $submissions = $submissionsByReferees->merge($submissionsByStaff);

     //   $submissions = CustomerSubmission::whereIn('refereeId' , $referees)->get();
        return $submissions;
    }






    private function getSubmissionIntroducer($id){
        $submissions = CustomerSubmission::where('id' , $id)->first();
        $referee = Referee::where('id' , $submissions->refereeId)->first();

        $introducer_user = Admin::where('id' , $referee->introducerId )->first();
        if($submissions->assignStaff!=null){
            $introducer_user = Admin::where('id', $submissions->assignStaff)->first();
        }
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

    private function handleRefereeCommision($submission){

        // get the commision rate from the database
        $rate = CommisionRate::first();

        // check if status is activated
        if($submission->status != 'Activated'){
            return 0;
        }

        // add the commission to the referee wallet
        $referee_wallet = RefereeWallet::where('userId' , $submission->refereeId)->first();



        $referee_wallet->balance = $referee_wallet->balance + $rate->rate;
        $referee_wallet->update();

        // update the wallet transaction table
        WalletTransaction::create([
            'userId' => $submission->refereeId,
            'walletId' => $referee_wallet->id,
            'amount' => $rate->rate,
            'transactionType' => 'Commision'
        ]);

        return 0;

    }



    public function updateSubmissionAssignStaff(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'submission_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $submission = CustomerSubmission::where('id' , $request->submission_id)->first();

        if(!$submission){
            return response()->json(['message' => 'Submission not found'], 404);
        }

        // check if admin is whether a admin or introducer
        $introducer_user = $request->user();


        if($introducer_user->role == 'admin'){

            $submission->assignStaff = $request->assign_introducer;
            $submission->update();

            return response()->json([
                'message' => 'Submission Assign successfully',
                'submission' => $submission
            ]);

        }else{

            $belongs_to = $this->getSubmissionIntroducer($submission->id);

            if($belongs_to->id != $introducer_user->id){

                return response()->json([
                    'message' => 'Unauthorized!',
                ] , 403);

            }else{

                $submission->assignStaff = $request->assign_introducer;
                $submission->update();

                return response()->json([
                    'message' => 'Submission Assign successfully',
                    'submission' => $submission
                ]);

            }

        }

    }
}
