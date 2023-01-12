<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Models\Referee;
use App\Models\Admin;
use App\Models\RefereeWallet;
use App\Models\Payment;
use App\Models\WalletTransaction;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Illuminate\Support\Facades\Notification;
use App\Notifications\RefereePaymentStateChange;
use App\Traits\Upload;

class PaymentController extends Controller
{
    use Upload;
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
                'message' => 'Payment request added successfully!',
                'payment' => $p
            ]);

        }else{

            $code = Str::uuid()->toString();

            $p = Payment::create([
                'code' => $code,
                'referee_id' => $referee->id,
                'type' => 'Bank',
                'amount' => $request->amount,
                'status' => 'Pending'
            ]);

            // update user wallet
            $temp_balance = $wallet->balance;
            $wallet->balance = $wallet->balance - $request->amount;
            $wallet->update();

            $qr  = base64_encode(QrCode::format('svg')->size(200)->errorCorrection('H')->generate($code));

            // pdf data
            $data = [
                'date' => date('m/d/Y'),
                'user' => $referee,
                'payment' => $p,
                'qrcode' => $qr,
                'prev_balance' => $temp_balance,
                'current_balance' => $wallet->balance
            ];

            $filename = 'PaymentRequestSlip_'.$p->id.'.pdf';
            $path = 'Referee/PaymentSlip/'.$filename;
            $pdf = PDF::loadView('/PDF/PaymentRequest', $data)->save('../storage/app/public/Referee/PaymentSlip/'.$filename)->stream($filename);

            $p->pdf_link = $path;
            $p->update();

            return response()->json([
                'message' => 'Payment request added successfully!',
                'pdf' => $path
            ]);

        }


    }

    public function paymentStateChange(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'payment_id' => 'required',
            'state' => 'required|in:Success,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $admin = $request->user();

        // get the paymnet object
        $payment = Payment::where('id' , $request->payment_id)->first();

        if(!$payment){
            return response()->json([
                'message' => 'Payment request not found!'
            ] , 404);
        }

        if($payment->status != 'Pending'){
            return response()->json([
                'message' => 'Payment request already processed!'
            ] , 400);
        }

        // update the status
        $payment->status = $request->state;
        $payment->update();

        $wallet = RefereeWallet::where('userId' , $payment->referee_id)->first();


        if($request->state == 'Rejected'){

            if(!$wallet){
                return response()->json([
                    'message' => 'Referee wallet not found!'
                ] , 404);
            }

            // refund to the wallet
            $wallet->balance = $wallet->balance + $payment->amount;
            $payment->checked_by = $admin->id;
            $wallet->update();

            WalletTransaction::create([
                'userId' => $payment->referee_id,
                'walletId' => $wallet->id,
                'transactionType' => 'Refund',
                'amount' => $payment->amount

            ]);

            // notify the referee
            $referee = Referee::where('id' , $payment->referee_id)->first();
            Notification::send($referee, new RefereePaymentStateChange($referee , $payment->amount , 'Reject'));

            // delete the slip
            $this->deleteFile($payment->pdf_link);

            return response()->json([
                'message' => 'Payment status changed successfully',
                'payment' => $payment,
                'wallet' => $wallet
            ]);

        }

        WalletTransaction::create([
            'userId' => $payment->referee_id,
            'walletId' => $wallet->id,
            'transactionType' => 'Withdraw',
            'amount' => $payment->amount

        ]);

        $referee = Referee::where('id' , $payment->referee_id)->first();
        Notification::send($referee, new RefereePaymentStateChange($referee , $payment->amount , 'Success'));

        // delete the slip
        $this->deleteFile($payment->pdf_link);

        return response()->json([
            'message' => 'Payment status changed successfully',
            'payment' => $payment,
            'wallet' => $wallet
        ]);

    }

    public function getAllPayments(Request $request){
        $payments = Payment::orderBy('id', 'desc')->paginate(15);

        return response()->json([
            'payments' => $payments
        ]);

    }

    public function getPaymentByCode(Request $request){

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $payment = Payment::where('code' , $request->code)->first();

        if(!$payment){
            return response()->json([
                'message' => 'Payment request not found!'
            ] , 404);
        }

        return response()->json([
            'payment' => $payment,
            'referee' => $request->user()
        ]);

    }

    public function getRefereePaymentList(Request $request){

        $referee = $request->user();
        $payments = Payment::where('referee_id' , $referee->id)->paginate(15);
        $wallet = RefereeWallet::where('userId' , $referee->id)->first();

        return response()->json([
            'wallet_balance' => $wallet->balance,
            'payments' => $payments
        ]);


    }


}
