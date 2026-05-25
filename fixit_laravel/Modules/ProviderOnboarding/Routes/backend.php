<?php

use Illuminate\Support\Facades\Route;

Route::prefix('backend')
    ->middleware(['web', 'auth', 'role:admin'])
    ->group(function () {
        Route::get('onboarding/settings', 'Backend\OnboardingSettingsController@index')
            ->name('backend.onboarding.settings');
        Route::post('onboarding/settings', 'Backend\OnboardingSettingsController@update')
            ->name('backend.onboarding.settings.update');

        Route::get('verifications', 'Backend\VerificationController@index')
            ->name('backend.verifications.index');
        Route::get('verifications/{id}', 'Backend\VerificationController@show')
            ->name('backend.verifications.show');
        Route::post('verifications/{id}/approve', 'Backend\VerificationController@approve')
            ->name('backend.verifications.approve');
        Route::post('verifications/{id}/reject', 'Backend\VerificationController@reject')
            ->name('backend.verifications.reject');
        Route::post('verifications/{id}/request-docs', 'Backend\VerificationController@requestDocs')
            ->name('backend.verifications.request-docs');
    });
