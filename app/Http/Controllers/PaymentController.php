<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\Referee;
use App\Models\Admin;
use App\Models\RefereeWallet;
use App\Models\Payment;
use PDF;

class PaymentController extends Controller
{

    public function paymentRequest(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'type' => 'required|in:Bank,Office',
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // check balance is suffifient for the payment
        $referee = $request->user();

        $wallet = RefereeWallet::where('userId' , $referee->id)->first();

        if(!$wallet){
            return response()->json([
                'message' => 'Wallet not found'
            ] , 404);
        }

        if($request->amount > $wallet->balance){
            return response()->json([
                'message' => 'Insufficient balance'
            ] , 400);
        }

        // create payment record and update balance

        if($request->type == 'Bank'){

            $p = Payment::create([
                'referee_id' => $referee->id,
                'type' => 'Bank',
                'amount' => $request->amount,
                'status' => 'Pending'
            ]);

            // update user wallet
            $wallet->balance = $wallet->balance - $request->amount;
            $wallet->update();

            return response()->json([
                'message' => 'Pending request added successfully!',
                'payment' => $p
            ]);

        }else{

            $code = Str::uuid()->toString();

            // pdf data
            $data = [
                'title' => 'Your title',
                'date' => date('m/d/Y'),
                'users' => $users
            ];

            $p = Payment::create([
                'code' => $code,
                'referee_id' => $referee->id,
                'type' => 'Bank',
                'amount' => $request->amount,
                'status' => 'Pending'
            ]);

            // update user wallet
            $wallet->balance = $wallet->balance - $request->amount;
            $wallet->update();

            return response()->json([
                'message' => 'Not implemented yet!',
                'payment' => ''
            ]);


        }


    }

}
