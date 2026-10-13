<?php

namespace App\Enums;

enum FrontSettingsEnum: string
{
    case GENERAL = 'general';
    case ACTIVATION = 'activation';
    case DEFAULT_CREATION_LIMITS = 'default_creation_limits';
    case SUBSCRIPTION_PLAN = 'subscription_plan';
    case AGORA = 'agora';
    case FIREBASE = 'firebase';
    case PROVIDER_COMMISSIONS = 'provider_commissions';
    case SERVICE_REQUEST = 'service_request';
    case MAINTENANCE_MODE = 'maintenance';
    case APPEARANCE = 'appearance';
    case APP_SETTINGS = 'app_settings';
    case ON_BOARDING_SCREENS = 'onboarding';
    // «Застраховать услугу»: apps need it to show the checkbox and price
    case INSURANCE = 'insurance';
    // Звонки через свой LiveKit: приложениям нужен только calls.status
    case CALLS = 'calls';
}
