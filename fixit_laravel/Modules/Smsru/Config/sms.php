<?php
return [
    'name' => 'Smsru', 'slug' => 'smsru', 'notes' => 'SMS.ru — API-ключ доступен в кабинете sms.ru.',
    'image' => 'modules/smsru/images/logo.svg',
    'configs' => ['smsru_api_key' => env('SMSRU_API_KEY', ''), 'smsru_sender' => env('SMSRU_SENDER', '')],
    'fields' => ['smsru_api_key' => ['type' => 'password', 'label' => 'API Key (sms.ru)'], 'smsru_sender' => ['type' => 'text', 'label' => 'Имя отправителя (опционально)']],
];
