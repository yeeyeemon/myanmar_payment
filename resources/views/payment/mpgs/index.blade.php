@extends('layouts.app')

@section('title')
Pay with Master/Visa
@stop

@section('style')
<style>
  .mpu-logo{
    width: 100px;
  }
  .forignpayment-logo-wrapper {
    background-color: transparent;height: auto;padding: 3px;text-align: center;
  }
  .forignpayment-logo-wrapper img {
     height: 60px;
     width: 90px;
  }
</style>
@stop

@section('content')

<div class="container" style="margin:5% auto;">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="login-container" id="container" style="width: auto;">
                <div class="panel-body">

                   <div class="forignpayment-logo-wrapper">
                    <img src="{{asset('images/payment/payment_images/master_card.png')}}" class="mpu-logo">
                    <img src="{{asset('images/payment/payment_images/visa.png')}}" class="mpu-logo">
                    <img src="{{asset('images/payment/payment_images/jcb.jpg')}}" class="mpu-logo">
                    <img src="{{asset('images/payment/payment_images/unionpay.png')}}" class="mpu-logo">
                   </div>

            	    <h3 class="text-center">Terms & Conditions</h3>
                    <ul>
                        <li>Provided as-is, no tutorials are to be considered professional advice, no liability whatsoever offered for damages resulting from use of any content on this site.</li>
                        <li>All content, images and code copyright</li>
                        <li style="list-style: none;"><input type="checkbox" name="terms" id="terms" onchange="activateButton(this)">  I Agree Terms & Coditions</li>
                    </ul>
                
                    <form action="{{route('payment.hostedcheckout')}}" style="margin-top: 200px;">
                        @csrf
                        <input type="submit" value="Continue" name="submit" id="submit" class="btn">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('script')

<script>

    $(function(){
        disableSubmit();
    });

 function disableSubmit() {
  document.getElementById("submit").disabled = true;
 }

  function activateButton(element) {

      if(element.checked) {
        document.getElementById("submit").disabled = false;
       }
       else  {
        document.getElementById("submit").disabled = true;
      }

  }
</script>

@stop