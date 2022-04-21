@extends('layouts.app')

@section('content')
{{-- @dd($data['pgw_url']) --}}
<img src="{{asset('images/spinner-loading-payment.svg')}}" alt="" class="mt-5 d-block text-center mx-auto ">
<h4 class="text-center">Processing Payment Page</h4>
<form id="hidden_form" name="hidden_form" method="post"  action="{{$data['pgw_url']}}">
    <input type="hidden" id="merchantID" name="merchantID" value="{{$data['merchant_id']}}"> <br>
    <input type="hidden" id="invoiceNo" name="invoiceNo" value="{{$data['invoice_no']}}"> <br>
    <input type="hidden" id="productDesc" name="productDesc" value="{{$data['product_desc']}}"> <br>
    <input type="hidden" id="amount" name="amount" value="{{$data['amount']}}"> <br>
    <input type="hidden" id="currencyCode" name="currencyCode" value="{{$data['currency_code']}}"> <br>
    <input type="hidden" id="userDefined1" name="userDefined1" value="{{$data['user_defined_1']}}"> <br>
    <input type="hidden" id="userDefined2" name="userDefined2" value="{{$data['user_defined_2']}}"> <br>
    <input type="hidden" id="userDefined3" name="userDefined3" value="{{$data['user_defined_3']}}"> <br>
    <input type="hidden" id="hashValue" name="hashValue" value="{{$data['hash_value']}}"> <br>
    <input type="submit" id="btnSubmit" name="btnSubmit" value="Submit" style="display: none;">
</form>
@endsection

@section('js')

<script type="text/javascript">
    
    function submitForm()
    {
        document.forms["hidden_form"].submit();
    }
    
    if(window.attachEvent)
    {
        window.attachEvent("onload", submitForm);
    }
    else
    {
        window.addEventListener("load", submitForm, false);
    }
</script>

@endsection