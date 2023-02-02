<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referee;
use App\Models\RefereeOtp;
use App\Models\Admin;


class StatController extends Controller
{

    public function getAllReferees(Request $request){


        $user = $request->user();

        $referees = [];

        if($user->role == 'admin'){

            if($request->introducer){
                $referees = Referee::where('introducerId' , $request->introducer)->paginate(15);
            }else{
                $referees = Referee::paginate(15);
            }

        }else{
            $referees = Referee::where('introducerId' , $user->id)->paginate(15);
        }

        return response()->json([
            'referees' => $referees
        ]);

    }

}