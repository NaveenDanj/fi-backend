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
              $mytime = Carbon::now();
              echo $mytime->toDateTimeString();
              $introducer = Admin::all();

              $refereesCount = count(Referee::all());
              $introducerCount= count(Admin::all());

             $introducerRefereeCount = Referee::all()->groupBy('introducerId');
             $introducerRefereeCount= $introducerRefereeCount->count();



              $todayReferees = Referee::where( 'created_at', '>', '2023-03-10 15:53:21')->get();
              $lastTenDayReferees = count(Referee::where( 'created_at', '>', Carbon::now()->subDays(10))->get());
             // $registeredRefereesDays = Carbon::parse(Referee::select('created_at'))->format('d/m/Y')->get();

        }else{
            $stats = Referee::where('introducerId' , $user->id)->get();
        }

        return response()->json([
            'refereesCount' => $refereesCount,
            'introducerCount' =>$introducerCount,
            'todayReferees' => $todayReferees,
            'lastTenDayReferees' => $lastTenDayReferees,
            'registeredRefereesDays' => $registeredRefereesDays,
            'introducerRefereeCount'=> $introducerRefereeCount
        ]);

    }

}
