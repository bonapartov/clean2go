<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Enums\FrontSettingsEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingController extends Controller
{
    /**
     * Secrets that live in the same settings groups as public values but
     * must never reach /api/settings (it is unauthenticated). Server-side
     * code reads them through Helpers::getSettings() as before.
     * - agora.certificate: signs Agora call tokens; must stay server-side.
     * - general.firebase_server_key: legacy FCM server key.
     * - firebase.service_json: Firebase service account; apps now send push
     *   through POST /api/push/send instead of signing FCM calls themselves.
     */
    public const SECRET_KEYS = [
        'agora.certificate',
        'general.firebase_server_key',
        'firebase.service_json',
        // LiveKit call server access (App\Support\Calls): apps get only calls.status
        'calls.livekit_url',
        'calls.api_key',
        'calls.api_secret',
    ];

    public function frontSettings()
    {
        try {

            $settingValues = Helpers::getSettings();
            $filteredValues = Arr::only($settingValues, array_column(FrontSettingsEnum::cases(), 'value'));
            Arr::forget($filteredValues, self::SECRET_KEYS);
            $filteredValues['general']['splash_screen_logo'] = ($filteredValues['general']['splash_screen_logo'] ?? null) ? config('app.url') . $filteredValues['general']['splash_screen_logo'] : null;
            $filteredValues['maintenance']['image'] = ($filteredValues['maintenance']['image'] ?? null) ? config('app.url') . $filteredValues['maintenance']['image'] : null;

            return new SettingResource($filteredValues);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getOnboardingScreens() 
    {
        try {
            $settingValues = Setting::pluck('values')->first();

            $onboardingScreens = array_map(function ($screen) {
                $screen['image'] = config('app.url') . $screen['image'];
                return $screen;
            }, $settingValues['onboarding']);

            return $onboardingScreens;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getAppearance()
    {
        try {

            $settingValues = Setting::pluck('values')->first();
            return $settingValues['appearance'];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getAdvertisement()
    {
        try {

            $settingValues = Setting::pluck('values')->first();
            return $settingValues['advertisement'];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

}
