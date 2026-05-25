<?php
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
Route::group([], function () {
    Route::any('cloudpayments/webhook', 'CloudPaymentsController@webhook')->name('cloudpayments.webhook')->withoutMiddleware([VerifyCsrfToken::class]);
});
