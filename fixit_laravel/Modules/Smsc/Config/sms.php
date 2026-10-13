<?php
return [
    'name' => 'Smsc', 'slug' => 'smsc', 'notes' => 'SMSC.ru — резервный провайдер. Логин/пароль из кабинета smsc.ru.',
    'image' => 'modules/smsc/images/logo.svg',
    // Keys only: values are entered in the admin («Платёжные методы» /
    // «SMS-шлюзы») and read via App\Support\GatewayCredentials, never env().
    'configs' => ['smsc_login' => '', 'smsc_password' => '', 'smsc_sender' => ''],
    'fields' => ['smsc_login' => ['type' => 'text', 'label' => 'Логин (smsc.ru)'], 'smsc_password' => ['type' => 'password', 'label' => 'Пароль (smsc.ru)'], 'smsc_sender' => ['type' => 'text', 'label' => 'Имя отправителя (опционально)']],
];
