<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Cbpay as CBPayModel;
use App\Transaction;
use App\Service\CBPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\templates\Cosmetic\Receipt;
use App\Models\OrderLetterJobUser;
use App\Models\Notification;
use Log;
class CBPayController extends Controller
{

    protected $CBPayService;

    public function __construct(CBPayService $CBPayService) {
        $this->CBPayService = $CBPayService;
    }
  

    public function checkTransaction(Request $request){
        $check = $this->CBPayService->checkTransaction($request->transRef);
        CBPayModel::where('transRef',$request->transRef)->update([
            'resBody'=> json_encode($check),
            'tranStatus'=>$check['transStatus'],

        ]);
        // if(env('REAL_PAYMENT') == false)
        // {
        //     $check['transStatus'] = 'S';
        //     $check['bankTransId'] = 1;
        //     $check['transAmount'] = 1;
        //     $check['transCurrency'] = 'MMK';
        // }

        if($check['transStatus'] == 'S'){ //if transaction success

            DB::transaction(function () use(&$check, &$request){

                // $template = new Receipt($guid, 'CBPay', $check['transAmount'], $request->formType, $request->cos_application_id);
                // $receipt_id = $template->save();

                $transaction = Transaction::create([
                    'user_id' => auth()->user()->id,
                    'order_letter_job_id' =>$request->orderLetterJob,
                    'payment_type' => "CBPay",
                    'invoice_no' =>$check['bankTransId'],
                    'pan'=>'-',
                    'trans_reference_id'=>$request->transRef,
                    'fail_reason'=>'-',
                    'service_fee'=>600,
                    'exam_fee'=>5000,
                    'amount'=>ltrim(substr($check['transAmount'], 0, -2), '0'),
                    'payment_date'=>now()->format('Y-m-d'),
                    'approval_code'=>$check['transStatus'],
                    'response_code'=>$check['transStatus'],
                    'status'=>$check['transStatus']
                ]);
                CBPayModel::where('transRef',$request->transRef)->update([
                    'resBody'=> json_encode($check),
                    'tranStatus'=>$check['transStatus'],
        
                ]);
                 


                $check['transaction_id'] = $transaction->id;
            });

            return json_encode($check);
        }

        return json_encode($check);
    }

    public function receipt(Request $request){
        $transactions = Transaction::find($request->transaction_id);
        return view('payment.receipt',compact('transactions'));

    }
}
