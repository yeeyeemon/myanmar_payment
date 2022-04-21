<?php

namespace App\Service;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;




class MPUService
{
    public $secret_key;
    public $pgw_test_url; 
    public $pgw_live_url;
    public $toHashData;

    public function __construct() {
        $this->pgw_test_url = "http://122.248.120.252:60145/UAT/Payment/Payment/pay";
        $this->pgw_live_url = "https://www.mpuecomuat.com/UAT/Payment/Action/api";
        $this->secret_key = "TL0XOZWML4EOYBQQFEF4SM0GGZ11H2GC";
        $this->toHashData = [];
    }
    public function sendPaymentRequest($request) {
        
        $member_fee = 500;
        $service_fee = 100;
        $price = $member_fee + $service_fee . "00";
        
        $secret_key = env('MPU_SECRET_KEY');
        $orderLetterJob = $request->orderLetterJob;
        $pgw_url = env('MPU_PAYMENT_GATEWAY_URL');
        $merchant_id = env('MPU_MERCHANT_ID');
        $invoice_no = $this->generateTransReferenceId();
        $product_desc = "Exam Fee";
        $amount = str_pad($price, 12, '0', STR_PAD_LEFT);;
        $currency_code = env('MPU_CURRENCY_CODE');
        $user_defined_1 = '570';
        $user_defined_2 = $service_fee;
        $user_defined_3 = 307;//unique id from ur app

        $value = [];
        array_push($value, $merchant_id, $invoice_no, $product_desc, $amount, $currency_code, $user_defined_1, $user_defined_2, $user_defined_3);
        
        sort($value, SORT_STRING);
        $string_value = implode("", $value);

        //HASH to pair MPU Response
        $hash_value = strtoupper(hash_hmac('sha1', $string_value, $secret_key, false));
        
        return [
            'pgw_url' => $pgw_url, 
            'merchant_id' => $merchant_id, 
            'invoice_no' => $invoice_no, 
            'product_desc' => $product_desc, 
            'amount' => $amount, 
            'currency_code' => $currency_code, 
            'user_defined_1' => $user_defined_1, 
            'user_defined_2' => $user_defined_2, 
            'user_defined_3' => $user_defined_3, 
            'hash_value' => $hash_value
        ];

        // return view('payment.processing', compact('pgw_url', 'merchant_id', 'invoice_no', 'product_desc','amount', 'currency_code', 'user_defined_1', 'user_defined_2', 'user_defined_3', 'hash_value'));
        
        // echo <<< PAYMENT
        //     <br>
        //     <form id="hidden_form" name="hidden_form" method="post"  action="{$pgw_url}">
        //     <input type="hidden" id="merchantID" name="merchantID" value="{$merchant_id}"> <br>
        //     <input type="hidden" id="invoiceNo" name="invoiceNo" value="{$invoice_no}"> <br>
        //     <input type="hidden" id="productDesc" name="productDesc" value="{$product_desc}"> <br>
        //     <input type="hidden" id="amount" name="amount" value="{$amount}"> <br>
        //     <input type="hidden" id="currencyCode" name="currencyCode" value="{$currency_code}"> <br>
        //     <input type="hidden" id="userDefined1" name="userDefined1" value="{$user_defined_1}"> <br>
        //     <input type="hidden" id="userDefined2" name="userDefined2" value="{$user_defined_2}"> <br>
        //     <input type="hidden" id="userDefined3" name="userDefined3" value="{$user_defined_3}"> <br>
        //     <input type="hidden" id="hashValue" name="hashValue" value="{$hash_value}"> <br>
        //     <input type="submit" id="btnSubmit" name="btnSubmit" value="Submit" style="display: none;">
        //     </form>
        //     <script language="JavaScript">
        //     document.forms["hidden_form"].submit();
        //     </script>
        // PAYMENT;

    }

    public function hashData($values) {
        //HASH to pair MPU Response
        return strtoupper(hash_hmac('sha1', $values, env('MPU_SECRET_KEY'), false));

    }
    protected function generateTransReferenceId() 
    {        
        do {
            $transId = time() . rand(100000, 999999);
        } 
        while ( Transaction::where('trans_reference_id', $transId)->first() );
          
        return $transId;
    }
}