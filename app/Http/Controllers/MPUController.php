<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Service\MPUService;
use Illuminate\Http\Request;
use App\Models\OrderLetterJob;
use App\Models\OrderLetterJobUser;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MPUController extends Controller
{
    public $MPUService;

    public function __construct(MPUService $MPUService) {
        $this->MPUService = $MPUService;
    }

    public function setupPayment(Request $request) {
        try {
            $this->MPUService->sendPaymentRequest($request);            
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function frontend(Request $request) {
        // Log::info($request->all());
        $secret_key = env('MPU_SECRET_KEY');

        $value = [];
        foreach(request()->all() as $key=>$val) {
            if($key != 'hashValue') {
                array_push($value, $val);
            }
        }

        sort($value, SORT_STRING);
        $string_value = implode("", $value);
        $hash_value = strtoupper(hash_hmac('sha1', $string_value, $secret_key, false));

        if ($hash_value == request('hashValue') && request('respCode') == '00' && request('invoiceNo')) {
            
            return redirect()->route('guest.job-list')->with('success','Payment Success');
        }

        return redirect()->route('payment.receipt')->with('transaction_id', $transaction_id); 
    }

    public function backend(Request $request) {

        Log::info($request->all());
        
        DB::transaction(function () use($request) {

            $secret_key = env('MPU_SECRET_KEY');
    
            $value = [];

            foreach ($request->all() as $key => $val) {
              if ($key != 'hashValue') {
                array_push($value, $val);
              }
            }
            
            sort($value, SORT_STRING);
            $string_value = implode("", $value);
            $hash_value = strtoupper(hash_hmac('sha1', $string_value, $secret_key, false));
    
            if($hash_value == request('hashValue') && request('respCode') == '00' && request('invoiceNo')) {

                $user = User::findOrfail(request('userDefined1'));

                Log::info([$request->all(), $user]);
              
                $invoice_no = request('invoiceNo');

                $pan = request('pan', null);
                $amount = ltrim(substr(request('amount'), 0, -2), '0');
                $service_fee = request('userDefined2');
                $exam_fee = $amount - $service_fee;

                $bank_date = $this->format_bank_date($request->dateTime);

                Transaction::create([
                    "user_id" => $request->userDefined1,
                    'order_letter_job_id' => $request->userDefined3,
                    "payment_type" => "MPU",
                    "invoice_no" => $invoice_no,
                    "trans_reference_id" => $request->tranRef, 
                    "pan" => $pan,
                    "exam_fee" => $exam_fee,
                    "service_fee" => $service_fee,
                    "amount" => $amount,
                    "status" => $request->status ?? 0,
                    "payment_date" => $bank_date,
                    "fail_reason" => $request->failReason,
                    "approval_code" => $request->approvalCode,
                    "response_code" => $request->respCode
                ]);

                OrderLetterJobUser::create([
                    "user_id" => $request->userDefined1,
                    "order_letter_job_id" => $request->userDefined3,
                    "bank_check_no" => $invoice_no,
                    "bank_check_date" => date('Y-m-d'),
                    "bank_check" => "-",
                    "bank" => "MPU",
                    "status" => "pending"
                ]);
                 
            }
    
        });
        Notification::create([
            'user_id' => $request->userDefined1,
            'title' => 'လျှောက်လွှာလက်ခံရရှိခြင်း',
            'message' => 'လူကြီးမင်း ပေးပို့သော လျှောက်လွှာအားဌာနမှ လက်ခံရရှိပါသည်။'
        ]);
    }

    public function format_bank_date($integer_date) {
       return substr($integer_date, 0, 4) .'-'. substr($integer_date, 4,2) .'-'. substr($integer_date,6,2) .' '. substr($integer_date,8,2) .':'. substr($integer_date,10,2) .':'. substr($integer_date,12,2);
    }

}

