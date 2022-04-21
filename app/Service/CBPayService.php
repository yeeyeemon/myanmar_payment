<?php

namespace App\Service;

use Exception;
use App\Transaction;
use App\Cbpay as CBPayModel;
class CBPayService
{
    protected $url;
    protected $authToken;
    protected $contentType;
    protected $reqId;
    protected $merId;
    protected $subMerId;
    protected $terminalId;
    protected $transAmount;
    protected $transCurrency;
    protected $serviceFee;

    protected $ref1;
    protected $ref2;
    protected $verify;
    protected $client;


    public function __construct(){
        $cbpay_sevices =config('services.cbpay');

        $this->contentType = 'application/json';
        $this->authToken =$cbpay_sevices['token'];
        $this->reqId = $this->generateTransReferenceId();
        $this->merId = $cbpay_sevices['mer_id'];
        $this->subMerId = $cbpay_sevices['submer_id'];
        $this->terminalId = $cbpay_sevices['terminal_id'];
        $this->serviceFee = $cbpay_sevices['service_fee'];
        $this->verify = env('APP_ENV') == 'production';
        $this->serviceUrl = $cbpay_sevices['service_url'];
        $this->client = new \GuzzleHttp\Client();
    }

    // public function

    public function generateTransaction($transAmount, $ref1, $ref2, $transCurrency = 'MMK'){
        $this->transAmount = $transAmount;// + $this->serviceFee;

        $this->transCurrency = $transCurrency;

        // $this->ref1 = "Tnhi is what we vfallwg w wrgwhw4 wtqet";

        $this->ref1 = substr(str_replace("_","",$ref1), 0, 10);

        $this->ref2 = 'cbp';
        
        $body = json_encode([
            'reqId' => $this->reqId,
            'merId' => $this->merId,
            'subMerId' => $this->subMerId,
            'terminalId' => $this->terminalId,
            'transAmount' => $this->transAmount,
            'transCurrency' => $this->transCurrency,
            'ref1' => $this->ref1,
            'ref2' => $this->ref2
        ]);
       

        $response = $this->client->request('POST', $this->serviceUrl . '/generate-transaction.service',
        [
            // 'verify' => $this->verify,
            'headers' => [
                'Content-Type' => $this->contentType,
                'Authen-Token' => $this->authToken 
            ],
            'body' => $body
        ]);
	
        $log = $response->getBody();
        $responseData = json_decode($response->getBody(), true);
        file_put_contents(storage_path('logs/payment-logs').'/global-cbpay-log-'.date("d-m-Y").'.log', now() ."- $log\n", FILE_APPEND);
        // CBPayModel::create([
        //     'user_id' => auth()->user()->id, 
        //     'reqId' => $this->reqId,
        //     'transRef' => $responseData['transRef'],
        //     'reqBody' => $log,
        // ]);

        
    //    dd($responseData, isset($responseData['code']) && $responseData['code'] === '0000');
        if ( isset($responseData['code']) && $responseData['code'] === '0000') {
            return $responseData;
        }

        highlight_string("Someting went wrong!\nPlease contact!");
        exit();
    }

    public function checkTransaction($transRef){

        $body = json_encode([
            'merId' => $this->merId, 'transRef' => $transRef
        ]);

        $response = $this->client->request('POST', $this->serviceUrl . '/check-transaction.service',
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => $this->contentType,
                    'Authen-Token' => $this->authToken
                ],
                'body' => $body
            ]
        );
 
        return json_decode($response->getBody(), true);

    }

    public function generateTransReferenceId() 
    {        
        do {
            $transId = time() . rand(100000, 999999);
        } 
        while ( Transaction::where('trans_reference_id', $transId)->first() );
          
        return $transId;
    }
}
