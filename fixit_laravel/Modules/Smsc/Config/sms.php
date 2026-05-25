<?php
return [
    'name' => 'Smsc', 'slug' => 'smsc', 'notes' => 'SMSC.ru — резервный провайдер. Логин/пароль из кабинета smsc.ru.',
    'image' => 'modules/smsc/images/logo.svg',
    'configs' => ['smsc_login' => env('SMSC_LOGIN', ''), 'smsc_password' => env('SMSC_PASSWORD', ''), 'smsc_sender' => env('SMSC_SENDER', '')],
    'fields' => ['smsc_login' => ['type' => 'text', 'label' => 'Логин (smsc.ru)'], 'smsc_password' => ['type' => 'password', 'label' => 'Пароль (smsc.ru)'], 'smsc_sender' => ['type' => 'text', 'label' => 'Имя отправителя (опционально)']],
];
