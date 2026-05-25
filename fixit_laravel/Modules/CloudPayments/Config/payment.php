<?php
return [
    'name'           => 'CloudPayments',
    'slug'           => 'cloudpayments',
    'image'          => 'modules/cloudpayments/images/logo.svg',
    'title'          => 'CloudPayments',
    'processing_fee' => 1.0,
    'subscription'   => 0,
    'configs' => [
        'cloudpayments_public_id'  => env('CLOUDPAYMENTS_PUBLIC_ID', ''),
        'cloudpayments_api_secret' => env('CLOUDPAYMENTS_API_SECRET', ''),
    ],
    'fields' => [
        'cloudpayments_public_id'  => ['type' => 'text',     'label' => 'Public ID'],
        'cloudpayments_api_secret' => ['type' => 'password', 'label' => 'API Secret'],
    ],
];
