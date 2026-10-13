<?php
return [
    'name'           => 'CloudPayments',
    'slug'           => 'cloudpayments',
    'image'          => 'modules/cloudpayments/images/logo.svg',
    'title'          => 'CloudPayments',
    'processing_fee' => 1.0,
    'subscription'   => 0,
    // Keys only: values are entered in the admin («Платёжные методы» /
    // «SMS-шлюзы») and read via App\Support\GatewayCredentials, never env().
    'configs' => [
        'cloudpayments_public_id'  => '',
        'cloudpayments_api_secret' => '',
    ],
    'fields' => [
        'cloudpayments_public_id'  => ['type' => 'text',     'label' => 'Public ID'],
        'cloudpayments_api_secret' => ['type' => 'password', 'label' => 'API Secret'],
    ],
];
