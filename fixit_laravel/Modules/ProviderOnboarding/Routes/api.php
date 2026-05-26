<?php

use Illuminate\Support\Facades\Route;

Route::prefix('onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Sprint 1
        Route::get('status', 'Api\OnboardingController@status')->name('api.onboarding.status');
        Route::post('inn', 'Api\OnboardingController@checkInn')
            ->middleware('throttle:onboarding-inn')
            ->name('api.onboarding.inn');
        Route::post('specialization', 'Api\OnboardingController@saveSpecialization')->name('api.onboarding.specialization');
        Route::get('complete', 'Api\OnboardingController@complete')->name('api.onboarding.complete');

        // Sprint 2
        Route::post('contract/generate', 'Api\OnboardingController@generateContract')->name('api.onboarding.contract.generate');
        Route::post('contract/send-sms', 'Api\OnboardingController@sendContractSms')
            ->middleware('throttle:onboarding-sms')
            ->name('api.onboarding.contract.send-sms');
        Route::post('contract/sign', 'Api\OnboardingController@signContract')
            ->middleware('throttle:onboarding-sign')
            ->name('api.onboarding.contract.sign');
        Route::post('npd-lost', 'Api\OnboardingController@npdLost')->name('api.onboarding.npd-lost');
        Route::post('gph-contract/generate', 'Api\OnboardingController@generateGphContract')->name('api.onboarding.gph-contract.generate');
        Route::post('gph-contract/send-sms', 'Api\OnboardingController@sendContractSms')
            ->middleware('throttle:onboarding-sms')
            ->name('api.onboarding.gph-contract.send-sms');
        Route::post('gph-contract/sign', 'Api\OnboardingController@signGphContract')
            ->middleware('throttle:onboarding-sign')
            ->name('api.onboarding.gph-contract.sign');

        // Sprint 3
        Route::post('passport', 'Api\OnboardingController@uploadPassport')->name('api.onboarding.passport.upload');
        Route::get('passport/status', 'Api\OnboardingController@passportStatus')->name('api.onboarding.passport.status');

        // Sprint 4
        Route::post('ooo-documents', 'Api\OnboardingController@uploadOooDocuments')->name('api.onboarding.ooo-documents');
    });

// Signed URL для безопасного просмотра файлов паспорта (TTL 15 мин) — без auth:sanctum, верифицируется подписью
Route::get('onboarding/passport/file/{userId}/{type}', 'Api\OnboardingController@servePassportFile')
    ->middleware('signed')
    ->name('api.onboarding.passport.file');
