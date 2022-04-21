<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class Transaction extends Model
{  
    //this is for mpu payment
    //u can separate this mpu from transaction
    protected $fillable = [
        'user_id',
        'order_letter_job_id',
        'payment_type',
        'invoice_no',
        'trans_reference_id',
        'pan',
        'service_fee',
        'exam_fee',
        'amount',
        'payment_date',
        'fail_reason',
        'approval_code',
        'response_code',
        'status'
    ];

    public function mpgsPay() {
        return $this->hasOne(Transaction::class);
    }

    public function paymentTransaction($orderId, $paymentCardType, $serviceResults,$order_letter_job_id,$payment_type)
    {
        DB::transaction(function () use(&$orderId, &$paymentCardType, &$serviceResults,&$order_letter_job_id, &$payment_type ){

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'order_letter_job_id' =>$order_letter_job_id,
                'payment_type' => $payment_type,
                'invoice_no' =>'-',
                'pan'=>'-',
                'trans_reference_id'=>'-',
                'fail_reason'=>'-',
                'service_fee'=>600,
                'exam_fee'=>5000,
                'amount'=>'-',
                'payment_date'=>now()->format('Y-m-d'),
                'approval_code'=>'-',
                'response_code'=>'-',
                'status'=>$serviceResults['status'],
            ]);
             
        //    OrderLetterJobUser::create([
        //     "user_id" => auth()->user()->id,
        //     "order_letter_job_id" => $order_letter_job_id,
        //     "bank_check_no" => $orderId,
        //     "bank_check_date" => date('Y-m-d'),
        //     "bank_check" => "-",
        //     "bank" => $payment_type,
        //     "status" => "pending"
        //     ]);

            // Notification::create([
            //     'user_id' => auth()->user()->id,
            //     'title' => 'လျှောက်လွှာလက်ခံရရှိခြင်း',
            //     'message' => 'လူကြီးမင်း ပေးပို့သော လျှောက်လွှာအားဌာနမှ လက်ခံရရှိပါသည်။'
            // ]);
            $transaction->mpgsPay()->create([
                'funding_method' => $serviceResults['sourceOfFunds']['provided']['card']['fundingMethod'],
                'customer_note' => $serviceResults['customerNote'],
                'description' => $serviceResults['description'],
                'name_on_card' => $serviceResults['customer']['firstName']?? 'No Name On Cart',
                'pan' => $serviceResults['sourceOfFunds']['provided']['card']['number'],
                'card_type' => $paymentCardType,
                'browser' => $serviceResults['device']['browser'],
                'ip_address' => $serviceResults['device']['ipAddress'],
                'total_amount' => $serviceResults['amount'],
                'currency' => $serviceResults['currency'],
                'status' => $serviceResults['status'],
                'creation_time' => Carbon::parse($serviceResults['creationTime'])->format('Y-m-d H:i:s')
            ]);

            $transaction->cbPay()->create([
                'msg' => $serviceResults['msg'],
                'transStatus' => $serviceResults['transStatus'],
                'bankTransId' => $serviceResults['bankTransId'],
                'transAmount' => $serviceResults['transAmount'],
                'transCurrency' => $serviceResults['transCurrency']
            ]);

        
    });
}
}
