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
        $registeredRefereesDays = [];
        $introducerRefereeCount = [];
        $totalSubmissionCount = 0;
        $refereeSubmissionCount = 0;
        $introducerSubmissionCount = 0;
        $submissionStatusCount = [];

        if($user->role == 'admin'){

            //  $referees = Referee::where('introducerId' , $request->introducer)->get();
            // $mytime = Carbon::now();
            // echo $mytime->toDateTimeString();
            $introducer = Admin::all();

            $refereesCount = count(Referee::all());
            $introducerCount= count(Admin::all());

            $introducerRefereeCount = Referee::all()->groupBy('introducerId');
            $introducerRefereeCount= $introducerRefereeCount->count();


            $introducer_out_list = [];

            foreach($introducer as $admin){

                $referee_count = Referee::where('introducerId' , $admin->id)->count();
                $introducer_out_list[] = [
                    "introducer" => $admin,
                    "refereeCount" => $referee_count
                ];
            }

            // dd( Carbon::now()->subDays(10)->toDateTimeString() );

            $todayReferees = Referee::where( 'created_at', '>', Carbon::now()->subDays(1)->toDateTimeString())->count();
            $lastTenDayReferees = Referee::where( 'created_at', '>', Carbon::now()->subDays(10)->toDateTimeString() )->count();

        }else{
            $stats = Referee::where('introducerId' , $user->id)->get();
        }

        return response()->json([
            'refereesCount' => $refereesCount,
            'introducerCount' =>$introducerCount,
            'todayReferees' => $todayReferees,
            'lastTenDayReferees' => $lastTenDayReferees,
            'registeredRefereesDays' => $registeredRefereesDays,
            'introducerRefereeCount'=> $introducerRefereeCount,
            "introducerOutList" => $introducer_out_list
        ]);

    }

}
