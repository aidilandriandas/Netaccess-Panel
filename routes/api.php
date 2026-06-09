<?php

use Illuminate\Support\Facades\Route;



Route::post('/payment/midtrans/notification', [\App\Http\Controllers\PaymentGatewayController::class, 'notification'])
    ->name('payment.midtrans.notification');

