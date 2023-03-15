<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referee;
use App\Models\RefereeOtp;
use App\Models\CustomerSubmission;
use App\Models\Admin;
use Carbon\Carbon;


class StatController extends Controller
{

    public function getAllReferees(Request $request){


        $user = $request->user();

        $referees = [];

        if($user->role == 'admin'){

            if($request->introducer){
                $referees = Referee::where('introducerId' , $request->introducer)->get();
            }else{
                $referees = Referee::all();
                  foreach($referees as $referee){
                    $introducer =  Admin::where( 'id' ,  $referee->introducerId)->first();
                    $referee->introducer = $introducer;
                }
            }

        }else{
            $referees = Referee::where('introducerId' , $user->id)->get();
        }

        return response()->json([
            'referees' => $referees
        ]);

    }


    public function getStatistics(Request $request){


        $user = $request->user();

        $stats = [];
        $refereesCount = [];
        $introducerCount = 0;
        $todayReferees = 0;
        $lastTenDayReferees = 0;
        $lastThirtyDaysReferees = 0;
        $registeredRefereesDays = [];
        $introducerRefereeCount = [];
        $totalSubmissionCount = 0;
        $refereeSubmissionCount = [];
        $introducerSubmissionCount = [];
        $submissionStatusCount = [];


        if($user->role == 'admin'){

            $introducer = Admin::select('id', 'fullname', 'email')
            ->where('role', 'introducer')
            ->get();
            $referee = Referee::select('id', 'fullname','email','propic')->get();
            $submission = CustomerSubmission::select('id', 'status','remarks','statusRemarks','created_at','updated_at',)->get();

            $totalSubmissionCount = CustomerSubmission::all()->count();
            $refereesCount = Referee::all()->count();
            $introducerCount = Admin::where('role', 'introducer')->count();

            foreach($introducer as $admin) {
                $referee_count = Referee::where('introducerId', $admin->id)->count();
            
                $introducerRefereeCount[] = [
                    "introducer_id" => $admin->id,
                    "introducer_name" => $admin->fullname,
                    "introducer_email" => $admin->email,
                    "refereeCount" => $referee_count
                ];
            }
            
            usort($introducerRefereeCount, function($a, $b) {
                return $b['refereeCount'] - $a['refereeCount'];
            });

            $refereeSubmissionCount = [];
foreach($referee as $ref) {
    $submission_count = CustomerSubmission::where('refereeId', $ref->id)->count();
    if($submission_count > 0) {
        $refereeSubmissionCount[] = [
            "ref_id" => $ref->id,
            "ref_name" => $ref->fullname,
            "ref_email" => $ref->email,
            "ref_img" =>$ref->propic,
            "submission_count" => $submission_count
        ];
    }
}

usort($refereeSubmissionCount, function($a, $b) {
    return $b['submission_count'] - $a['submission_count'];
});




            $registeredRefereesDays = Referee::selectRaw('DATE(created_at) as date, COUNT(*) as count, (COUNT(*) * 100 / sum(COUNT(*)) over ()) as percentage')
            ->groupBy('date')
            ->get();

            

          //  $submissionStatusCount = CustomerSubmission::selectRaw('status, count(*) as count')->groupBy('status')->get();
            $submissionStatusCount = CustomerSubmission::selectRaw('status, count(*) as count, (count(*) * 100 / sum(count(*)) over ()) as percentage')
                                ->groupBy('status')
                                ->get();

            $todayReferees = Referee::where( 'created_at', '>', Carbon::now()->subDays(1)->toDateTimeString())->count();
            $lastTenDayReferees = Referee::where( 'created_at', '>', Carbon::now()->subDays(10)->toDateTimeString() )->count();
            $lastThirtyDaysReferees = Referee::where( 'created_at', '>', Carbon::now()->subDays(30)->toDateTimeString() )->count();


        }else{
            $stats = Referee::where('introducerId' , $user->id)->get();
        }

        return response()->json([
            'refereesCount' => $refereesCount,
            'introducerCount' =>$introducerCount,
            'todayReferees' => $todayReferees,
            'totalSubmissionCount'=> $totalSubmissionCount,
            'lastTenDayReferees' => $lastTenDayReferees,
            'lastThirtyDaysReferees'=>$lastThirtyDaysReferees,
            'registeredRefereesDays' => $registeredRefereesDays,
            'submissionStatusCount'=> $submissionStatusCount,
            'refereeSubmissionCount'=>$refereeSubmissionCount,
            'introducerSubmissionCount'=>$introducerSubmissionCount,
            'introducerRefereeCount'=> $introducerRefereeCount,
        ]);

    }

}
