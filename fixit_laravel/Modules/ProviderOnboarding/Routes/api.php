<?php

use Illuminate\Support\Facades\Route;

Route::prefix('onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('status', 'Api\OnboardingController@status')->name('api.onboarding.status');
        Route::post('inn', 'Api\OnboardingController@checkInn')->name('api.onboarding.inn');
        Route::post('specialization', 'Api\OnboardingController@saveSpecialization')->name('api.onboarding.specialization');
        Route::get('complete', 'Api\OnboardingController@complete')->name('api.onboarding.complete');

        // Sprint 2
        // Route::post('ooo-documents', 'Api\OnboardingController@saveOooDocuments');
        // Route::post('contract/generate', 'Api\OnboardingController@generateContract');
        // Route::post('contract/send-sms', 'Api\OnboardingController@sendContractSms');
        // Route::post('contract/sign', 'Api\OnboardingController@signContract');
        // Route::post('npd-lost', 'Api\OnboardingController@npdLost');
        // ^ Sprint 2: только фиксирует потерю НПД и выставляет payments_frozen = true.
        //   Полный флоу (экран выбора ГПХ / восстановить НПД / стать ИП) реализуется в Sprint 4.

        // Sprint 3
        // Route::post('passport', 'Api\OnboardingController@submitPassport');
        // Route::get('passport/status', 'Api\OnboardingController@passportStatus');
    });
