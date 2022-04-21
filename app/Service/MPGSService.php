<?php

namespace App\Service;

use Exception;
use App\Transaction;
use Illuminate\Support\Facades\Log;

class MPGSService
{
    protected $contentType;

    protected $serviceFee;
    protected $merchantId;
    protected $apiPassword;
    protected $currency;
    protected $baseUrl;
    protected $version;
    protected $sessionUrl;

    public function __construct()
    {
        if ( 'production' === env('APP_ENV') ) {
            $serviceFee  = env('MPGS_PROD_SERVICE_FEE');
            $apiPassword = env('MPGS_PROD_API_PASSWORD');
            $currency    = env('MPGS_PROD_CURRENCY');
            $baseUrl     = env('MPGS_PROD_BASE_URL');
            $version     = env('MPGS_PROD_VERSION');
            $merchantId  = env('MPGS_PROD_MERCHANT_ID');
        } else {
            $serviceFee  = env('MPGS_LOCAL_SERVICE_FEE');
            $apiPassword = env('MPGS_LOCAL_API_PASSWORD');
            $currency    = env('MPGS_LOCAL_CURRENCY');
            $baseUrl     = env('MPGS_LOCAL_BASE_URL');
            $version     = env('MPGS_LOCAL_VERSION');
            $merchantId  = env('MPGS_LOCAL_MERCHANT_ID');
        }

        $this->contentType = 'application/json';
        $this->serviceFee  = (int) $serviceFee;
        $this->merchantId  = $merchantId;
        $this->apiPassword = $apiPassword;
        $this->baseUrl     = $baseUrl;
        $this->version     = $version;
        $this->currency    = $currency;
        $this->apiUrl      = $baseUrl . "/api/rest/version/" . $version . "/merchant/" . $merchantId;
        $this->sessionUrl = $this->apiUrl . "/session";
    }

    public function init($amount, $orderId, $urls = array() )
    {
        // $amount += $this->serviceFee;
        $result = $this->createCheckoutSession($amount, $orderId, $urls['completeUrl'], $urls['notifyUrl']);
       
        if( isset($result['result']) && 'SUCCESS' === $result['result'] ) {

            $this->setSession('successIndicator', $result['successIndicator']);

            return array(
                'initiable' => true,
                'hostedcheckoutUrl' => $this->baseUrl . "/checkout/version/" . $this->version . "/checkout.js",
                'errorUrl' => $urls['errorUrl'],
                'cancelUrl' => $urls['cancelUrl'],
                'completeUrl' => $urls['completeUrl'],
                'timeoutUrl' => $urls['timeoutUrl'],
                'notifyUrl' => $urls['notifyUrl'],
                'sessionId' => $result['session']['id'],
                'merchantId' => $result['merchant']
            );

        }
        if ( isset($result['result']) && 'ERROR' === $result['result'] ) {
            if(env('APP_ENV') == 'local'){
                dd($result);
            }
            Log::error('mpgs error -'.json_encode($result));
            return ['initiable' => false ];
        }
    }

    /*public function sessionApi($sessionId)
    {
        return $this->initCurl('GET', $this->apiUrl . '/session/' . $sessionId);
    }*/

    public function orderApi( $orderId )
    {
        return $this->initCurl('GET', $this->apiUrl . '/order/' . $orderId);
    }

    public function getSuccessIndicatorCode()
    {
        return session('successIndicator');
    }

    // (new \App\Helpers\Mpgs()->refundPayment($orderId, $transactionId, $amount) );
    public function refundPayment($orderId, $transactionId, $amount)
    {
        // https://ap-gateway.mastercard.com/api/rest/version/59/merchant/{merchantId}/order/{orderid}/transaction/{transactionid}
        $apiUrl = $this->apiUrl . "/order/" . $orderId . "/transaction/" . $transactionId . "";

        return $this->initCurl('PUT', $this->apiUrl, array(
            "apiOperation" => "REFUND",
            "transaction" => array(
                "amount" => $amount,
                "currency" => $this->currency
            )
        ));
    }

    protected function setSession($key, $val)
    {
        session([$key => $val]);
    }

    protected function createCheckoutSession($amount, $orderId, $completeUrl, $notifyUrl)
    {
       
        return $this->initCurl('POST', $this->sessionUrl, array(
            "apiOperation" => "CREATE_CHECKOUT_SESSION",
            "interaction" => array(
                "operation" => "PURCHASE",
                "returnUrl" => $completeUrl
            ),
            "order" => array(
                "amount" => $amount,
                "currency" => $this->currency,
                "id" => $orderId,
                "notificationUrl" => $notifyUrl
            )
        ));
    }

    /*protected function paymentSession( $sessionId )
    {
        $url = $this->apiUrl . '/session/' . $sessionId;
        $result = $this->curlInit('GET', $url);
        return $result;
    }*/

    protected function initCurl($method, $url, $data = array())
    {
        /*$credential = base64_encode("merchant." . $this->merchantId . ":" . $this->apiPassword);
        $header = array('Content-Type: ' . $this->contentType, 'Authorization: Basic ' . $credential);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ( count( $data) > 0 ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        dd($result);
        return $result;
*/
        // $credential = base64_encode("merchant." . $this->merchantId . ":" . $this->apiPassword);
        // $header = array('Content-Type: ' . $this->contentType, 'Authorization: Basic ' . $credential);


        $response = (new \GuzzleHttp\Client())->request($method, $url,
        [
            'verify' => false,
            'allow_redirects'=>true,
            'http_errors'=>false,
            'headers' => array(
                'Content-Type' => $this->contentType,
                'Authorization' => 'Basic ' . base64_encode("merchant." . $this->merchantId . ":" . $this->apiPassword)
            ),
            'body' => count( $data) > 0? json_encode($data):null
        ]);

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
