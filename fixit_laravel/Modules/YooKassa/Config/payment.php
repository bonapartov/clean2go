<?php
return [
    'name' => 'YooKassa', 'slug' => 'yookassa',
    'image' => 'modules/yookassa/images/logo.svg', 'title' => 'ЮКасса',
    'processing_fee' => 1.0, 'subscription' => 0,
    // Keys only: values are entered in the admin («Платёжные методы» /
    // «SMS-шлюзы») and read via App\Support\GatewayCredentials, never env().
    'configs' => ['yookassa_shop_id' => '', 'yookassa_secret_key' => ''],
    'fields' => ['yookassa_shop_id' => ['type' => 'text', 'label' => 'Shop ID'], 'yookassa_secret_key' => ['type' => 'password', 'label' => 'Секретный ключ']],
];
