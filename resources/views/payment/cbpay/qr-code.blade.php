@extends('layouts.app')
@section('content')

<div class="card">
    <div class="card-body">
        <div class="text-center">
            {!! QrCode::size(200)->generate($data['merDqrCode']) !!}
            <br><br>
            <h3>QR Code ကို Scan ဖတ်ပြီး ငွေပေးချေပါ။ </h3>
        </div>
        <input type="hidden" name="order_letter_job_id" id="order_letter_job_id" value="{{ $order_letter_job_id }}">
    </div>

    
</div>

<form method="put" id="submit-form" action="{{ route('payment.receipt')}}">
    <div class="row">
        <input type="hidden" name="type" value="{{request('form_type')}}">
        <input type="hidden" name="transaction_id" value="">
    </div>
</from>

@endsection

@section('js')
    @parent
    <script>
        function check(){
            $.ajax({
                type: "POST",
                url: "{{route('payment.check-transaction')}}",
                data: {
                    formType: "{{request('form_type')}}",
                    transRef: "{{$data['transRef']}}",
                    _token: "{{ csrf_token() }}",
                    orderLetterJob : $('#order_letter_job_id').val()
                },
                success: function(result) {
                    console.log(result)
                    let data = JSON.parse(result);

                    if(data.transStatus == 'S'){
                        $("input[name='transaction_id']").val(data.transaction_id);
                        $("#submit-form").submit();
                    }

                    //call agian till transStatus change
                    if(data.transStatus == 'P'){
                        setTimeout(function() {

                            check();

                        }, 2000);
                    }
                },
                error: function(result) {
                    console.log(result)
                }
            });
        }

        //first time call
        check();

        setInterval(function() {
            window.location.reload();
        }, 270000);
        
    </script>
@endsection
