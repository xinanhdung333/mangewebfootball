<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
'sepay' => [
    'webhook_secret' => env(
        'SEPAY_WEBHOOK_SECRET'
    ),
],
    'mbbank' => [
        'bank_bin' => env('MBBANK_BIN', '970422'),
        'account_no' => env('MBBANK_ACCOUNT_NO'),
        'account_name' => env('MBBANK_ACCOUNT_NAME'),
        'qr_template' => env('MBBANK_QR_TEMPLATE', 'compact2'),
        // 'webhook_secret' => env('SEPAY_WEBHOOK_SECRET'),
    ],

    'ghn' => [
        'mode' => env('GHN_MODE', 'demo'), // ghn or demo
        'token' => env('GHN_TOKEN'),
        'shop_id' => env('GHN_SHOP_ID'),
        'endpoint' => env('GHN_ENDPOINT', 'https://dev-online-gateway.ghn.vn/shiip/public-api'),
        'from_name' => env('GHN_FROM_NAME', 'Shop Football'),
        'from_phone' => env('GHN_FROM_PHONE', '0901234567'),
        'from_address' => env('GHN_FROM_ADDRESS', '123 Test Street'),
        'from_ward_name' => env('GHN_FROM_WARD_NAME', 'Phường Từ Liêm'),
        'from_district_name' => env('GHN_FROM_DISTRICT_NAME', 'Quận Bắc Từ Liêm'),
        'from_province_name' => env('GHN_FROM_PROVINCE_NAME', 'Hà Nội'),
    ],

    'ghn_demo' => [
        'pickup_lat' => env('GHN_DEMO_PICKUP_LAT', 10.776889),
        'pickup_lng' => env('GHN_DEMO_PICKUP_LNG', 106.700806),
        'delivery_address' => env('GHN_DEMO_DELIVERY_ADDRESS', '72 Thành Thái, Phường 14, Quận 10, Hồ Chí Minh'),
        'delivery_ward' => env('GHN_DEMO_DELIVERY_WARD', 'Phường 14'),
        'delivery_district' => env('GHN_DEMO_DELIVERY_DISTRICT', 'Quận 10'),
        'delivery_province' => env('GHN_DEMO_DELIVERY_PROVINCE', 'Hồ Chí Minh'),
    ],

    // OpenRouteService - tính khoảng cách + phí ship
    'ors' => [
        'api_key'        => env('ORS_API_KEY'),
        'endpoint'       => env('ORS_ENDPOINT', 'https://api.heigit.org/openrouteservice/v2/directions/driving-car/json'),
        'shop_lat'       => env('SHOP_LAT', 21.0285),
        'shop_lng'       => env('SHOP_LNG', 105.8542),
        'base_fee'       => (int) env('SHIPPING_BASE_FEE', 15000),
        'fee_per_km'     => (int) env('SHIPPING_FEE_PER_KM', 5000),
        'free_threshold' => (int) env('SHIPPING_FREE_THRESHOLD', 200000),
    ],

];

