<?php
return [
    'name' => 'YooKassa', 'slug' => 'yookassa',
    'image' => 'modules/yookassa/images/logo.svg', 'title' => 'ЮКасса',
    'processing_fee' => 1.0, 'subscription' => 0,
    'configs' => ['yookassa_shop_id' => env('YOOKASSA_SHOP_ID', ''), 'yookassa_secret_key' => env('YOOKASSA_SECRET_KEY', '')],
    'fields' => ['yookassa_shop_id' => ['type' => 'text', 'label' => 'Shop ID'], 'yookassa_secret_key' => ['type' => 'password', 'label' => 'Секретный ключ']],
];
