<?php

use App\Http\Controllers\Payment\MPUController;
use App\Http\Controllers\Payment\MpgsController;
use App\Http\Controllers\Payment\CBPayController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


Route::name('payment.')->prefix('payment')->group(function() {
    

    Route::get('select-online', [PaymentController::class, 'onlinePayment'])->name('online');

    Route::get('setup-payment', [PaymentController::class, 'setupPayment'])->name('setup');

    Route::post('mpu/backend', [MPUController::class, 'backend']);
    Route::post('mpu/frontend', [MPUController::class, 'frontend']);

    //cb payment
    Route::post('cbpay/checktransaction', [CBPayController::class, 'checkTransaction'])->name('check-transaction');
    Route::get('payment-receipt', [CBPayController::class, 'receipt'])->name('receipt');

     //mpgs
    Route::get('mpgs/{orderLetterJob}', [
        MpgsController::class, 'mpgs'
    ])->name('mpgs');

    Route::get('mpgs-success/{orderLetterJob}/{order_id}', [
        MpgsController::class, 'mpgsSuccess'
    ])->name('mpgs-success');
    


});