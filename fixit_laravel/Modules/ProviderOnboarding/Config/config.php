<?php

return [
    'mock_mode' => env('ONBOARDING_MOCK_MODE', true), // Sprint 1: всегда true пока нет реальных API
    'passport_polling_timeout_minutes' => 30,
    'contract_sms_ttl_minutes' => 15,
    'contract_sms_max_attempts' => 3,
    'inn_rate_limit' => 5,       // запросов в час на IP
    'sms_rate_limit' => 3,       // SMS в час на номер
];
