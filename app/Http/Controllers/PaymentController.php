<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use App\Service\MPUService;
use App\Service\CBPayService;
use App\Service\MPGSService;
use App\Service\Payment;
use App\Service\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    protected $MPUService;
    protected $CBPayService;
    protected $MPGSService;

    public function __construct(MPUService $MPUService,CBPayService $CBPayService,MPGSService $MPGSService) {
        $this->MPUService = $MPUService;
        $this->CBPayService = $CBPayService;
        $this->MPGSService = $MPGSService;
    }
    
    public function onlinePayment( ) {
        return view('payment.online.index');
    }

    public function setupPayment(Request $request) {
        $invoice_no = $this->CBPayService->generateTransReferenceId();
        $order_letter_job_id = $request->orderLetterJob;
       
        switch($request->paymentMethod){
            case "mpu":
            $data = $this->MPUService->sendPaymentRequest($request);
            return view('payment.processing', compact('data'));
            break;
            case "cbpay":
            $data = $this->CBPayService->generateTransaction(10,'ref 1' , 'ref test 2');
           
            return view('payment.cbpay.qr_code', compact(['data','order_letter_job_id']));
            break;
            case "master":
            return redirect()->route('payment.mpgs',$order_letter_job_id);//unique id from ur application
            break;

            case "jcb":
            return redirect()->route('payment.mpgs',$order_letter_job_id);
            break;

            case "visa":
            return redirect()->route('payment.mpgs',$order_letter_job_id);
            break;

            case "unionpay":
            return redirect()->route('payment.mpgs',$order_letter_job_id);
            break;

        }
    }
}
