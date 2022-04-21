@extends('layouts.app')
@section('style')
<style>
    .payment-wrapper img {
       height: 95px;
       margin-top: 5px;
       width: 130px;
    }
     /* HIDE RADIO */
     .hiddenradio [type=radio] { 
       position: absolute;
       opacity: 0;
       width: 0;
       height: 0;
   }
 
   /* IMAGE STYLES /
   .hiddenradio [type=radio] + img {
       cursor: pointer;
       margin-right: 40px;
     /-webkit-filter: grayscale(100%); /* Safari 6.0 - 9.0 */
     /filter: grayscale(100%);/
   }
 
   /* CHECKED STYLES /
   .hiddenradio [type=radio]:checked + img {
       outline: 10px solid forestgreen;
       border: 1px solid;
       padding: 10px;
       box-shadow: 5px 10px 18px #888888;
      -webkit-filter: none; / Safari 6.0 - 9.0 */
      filter: none;
   }
 
   .payment-type-choose{
     text-align: center;
     font-size: 18px;
   }
 /* .hiddenradio img:hover{
     width: 150px;
     transition: width 2s;
 
     } */
 </style>
@endsection

@section('content')
    <div class="my-4">&nbsp;</div>
    <h3 class="text-black text-center py-2 mt-4"><strong>UCSB Online Payment</strong></h3>
    <div class="container shadow rounded my-4 p-4 border">
        <form action="{{route('payment.setup')}}" method="GET">
            @csrf
            <input type="hidden" name="orderLetterJob" id="orderLetterJob" value="{{$orderLetterJob}}">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="badge badge-secondary"><h6>Username: {{auth()->user()->name ?? 'username'}}</h6></div>
                    <div>Date: {{now()->toDateString()}}</div>
                </div>
                <div class="table-responsive">
                    <table class="table-bordered table">
                        <thead>
                            <tr>
                                <th>Fee</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Exam Fee</td>
                                <td><input type="text" name="exam_fee" id="exam_fee" value="5000" class="border-0 w-10 bg-white" disabled> MMK</td>
                            </tr>
                            <tr>
                                <td>Banking Fee</td>
                                <td><input type="text" name="banking_fee" id="banking_fee" value="1000" class="border-0 w-10 bg-white" disabled> MMK</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <h6>CHOOSE A PAYMENT METHOD</h6>
                    <div class="text-center payment-wrapper">
                        <div class="hiddenradio">
                            <label>
                                <input type="radio" name="paymentMethod" id="master" value="master" />
                                <img src="{{ asset('images/payment/payment_images/master_card.png') }}" alt="" width="100px">
                            </label>
                            <label>
                                <input type="radio" name="paymentMethod" id="visa" value="visa" />
                                <img src="{{ asset('images/payment/payment_images/visa.png') }}" alt="" width="100px">
                            </label>
                            <label>
                                <input type="radio" name="paymentMethod" id="jcb" value="jcb" />
                                <img src="{{ asset('images/payment/payment_images/jcb.jpg') }}" alt="" width="90px">
                            </label>
                            <label>
                                <input type="radio" name="paymentMethod" id="unionpay" value="unionpay" />
                                <img src="{{ asset('images/payment/payment_images/unionpay.png') }}" alt="" width="100px">
                            </label>
                            <label>
                                <input type="radio" name="paymentMethod" id="mpu" value="mpu" />
                                <img src="{{ asset('images/payment/payment_images/mpu.png') }}" alt="" width="150px">
                            </label>
                            <label>
                                <input type="radio" name="paymentMethod" id="cbpay" value="cbpay" />
                                <img src="{{ asset('images/payment/payment_images/cbpay.jpg') }}" alt="" width="90px">
                            </label>
                        </div>
                        <br>
                    </div>
                </div>
                
            </div>
            <button type="submit" class="btn btn-primary">Next</button>
        </form>    
    </div>
@endsection



