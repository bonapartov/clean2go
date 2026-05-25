<?php
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
Route::group([], function () {
    Route::any('yookassa/webhook', 'YooKassaController@webhook')->name('yookassa.webhook')->withoutMiddleware([VerifyCsrfToken::class]);
});
