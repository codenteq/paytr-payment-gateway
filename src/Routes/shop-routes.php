<?php

use Illuminate\Support\Facades\Route;
use Webkul\PayTR\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {

    /**
     * PayTR payment routes
     */
    Route::get('/paytr-redirect', [PaymentController::class, 'redirect'])->name('paytr.redirect');

    Route::get('/paytr-success', [PaymentController::class, 'success'])->name('paytr.success');

    Route::get('/paytr-cancel', [PaymentController::class, 'failure'])->name('paytr.cancel');

    Route::post('/paytr-callback', [PaymentController::class, 'callback'])->name('paytr.callback')
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
});
