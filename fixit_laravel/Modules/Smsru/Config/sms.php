<?php
return [
    'name' => 'Smsru', 'slug' => 'smsru', 'notes' => 'SMS.ru — API-ключ доступен в кабинете sms.ru.',
    'image' => 'modules/smsru/images/logo.svg',
    // Keys only: values are entered in the admin («Платёжные методы» /
    // «SMS-шлюзы») and read via App\Support\GatewayCredentials, never env().
    'configs' => ['smsru_api_key' => '', 'smsru_sender' => ''],
    'fields' => ['smsru_api_key' => ['type' => 'password', 'label' => 'API Key (sms.ru)'], 'smsru_sender' => ['type' => 'text', 'label' => 'Имя отправителя (опционально)']],
];
