<?php

use Illuminate\Support\Facades\Route;

Route::prefix('onboarding')
    ->middleware(['auth:sanctum', 'role:provider,serviceman'])
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

        // Sprint 3
        // Route::post('passport', 'Api\OnboardingController@submitPassport');
        // Route::get('passport/status', 'Api\OnboardingController@passportStatus');
    });
