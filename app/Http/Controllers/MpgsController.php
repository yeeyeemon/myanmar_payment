<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderLetterJob;
use App\Transaction;
use App\Service\MPGSService;
use Carbon\Carbon;
use Log;
class MpgsController extends Controller
{
    // protected $MPGSService;

    // public function __construct(MPGSService $MPGSService) {
    //     $this->MPGSService = $MPGSService;
    // }
    public function mpgs($order_letter_job_id)
    {
        $OrderLetterJob =OrderLetterJob::findOrFail($order_letter_job_id);
        
        $amount = 500;
        $orderId = (new MPGSService())->generateTransReferenceId();     

        $result =  (new MPGSService())->init($amount, $orderId, [
            'completeUrl' => route('payment.mpgs-success', ['orderLetterJob' => $OrderLetterJob, 'order_id' => $orderId]),
            'notifyUrl' => route('payment.online', ['orderLetterJob' => $OrderLetterJob, 'status' => 'notify']),
            'errorUrl' => route('payment.online', ['orderLetterJob' => $OrderLetterJob, 'status' => 'An Error Occur']),
            'cancelUrl' => route('payment.online', ['orderLetterJob' => $OrderLetterJob, 'status' => 'Payment has been cancelled.']),
            'timeoutUrl' => route('payment.online', ['orderLetterJob' => $OrderLetterJob, 'status' => 'Connection Timeout'])
        ]);

        if (!$result['initiable']) {
            return redirect()->back()->with('error', 'Sorry. This service is unavailable right now. Please try different service.');
        }
        return view('frontend.mpgs_pay.start', [
            'customerNote' => auth()->user()->name . '-' . $order_letter_job_id->id,
            'description' => 'UCSB Online Application Form',
            'customerName' => auth()->user()->name,
            'hostedcheckoutUrl' => $result['hostedcheckoutUrl'],
            'completeUrl' => $result['completeUrl'],
            'notifyUrl' => $result['notifyUrl'],
            'errorUrl' => $result['errorUrl'],
            'cancelUrl' => $result['cancelUrl'],
            'timeoutUrl' => $result['timeoutUrl'],
            'sessionId' => $result['sessionId'],
            'merchantId' => $result['merchantId']
        ]);
    }

    public function mpgsSuccess($order_letter_job_id, $orderId)
    {
        $OrderLetterJob =OrderLetterJob::findOrFail($order_letter_job_id);
        $mpgsInstance = new MPGSService();
        if (request('resultIndicator', null) !== $mpgsInstance->getSuccessIndicatorCode()) {
            return redirect()->route('payment.online', ['orderLetterJob' => $OrderLetterJob])->with('error', 'Invalid Response');
        }

        $orderDetail = $mpgsInstance->orderApi($orderId);

        if ( isset($orderDetail['result']) && $orderDetail['result'] === 'ERROR' ) {
            $msg = '['.date('Y-m-d H:i:s').']OrderId['.$orderId.'],Reason'.$sessionDetail['error']['explanation'].'['.$sessionDetail['error']['cause'].'].';
            \Log::info($msg);
             return redirect()->route('payment.online')->with('error', $msg);
        } 
        $paymentCardType  = $orderDetail['sourceOfFunds']['provided']['card']['brand'];
        // $template = new Receipt($orderId, $paymentCardType, $orderDetail['amount'], 'New', $cos_application->id);
        // $receipt_id = $template->save();
        $payment_type ="mpgs";
        $transaction_id = (new Transaction)->paymentTransaction($orderId, $paymentCardType, $orderDetail,$order_letter_job_id,$payment_type);
        // $this->paymentSuccess($transaction_id, $order_letter_job_id->id);

        file_put_contents(storage_path("logs") . "/mpgs-" . date("d-m-Y") .$order_letter_job_id . ".log",  "\n Order Letter Job ID (" . $order_letter_job_id->id . ") USER_ID(" . auth()->user()->id . ") with Order ID: " . $orderId . " \n", FILE_APPEND);

        return redirect()->route('payment.receipt')->with('transaction_id', $transaction_id);
    }

   

   

  

  
}
