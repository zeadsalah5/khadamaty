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

    // بوابة الدفع الأونلاين (المرحلة 6E) — المفاتيح من .env بإيد صاحب الموقع.
    // لو فاضية، الدفع الأونلاين مابيظهرش والموقع بيكمّل بالدفع عند الاستلام.
    'paymob' => [
        'secret_key'     => env('PAYMOB_SECRET_KEY'),
        'public_key'     => env('PAYMOB_PUBLIC_KEY'),
        'hmac_secret'    => env('PAYMOB_HMAC_SECRET'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'base_url'       => env('PAYMOB_BASE_URL', 'https://accept.paymob.com'),
    ],

    // مهلة إتمام الدفع الأونلاين بالدقائق — بعدها يتلغي الطلب ويرجع المخزون.
    'payment' => [
        'pending_timeout' => (int) env('PAYMENT_PENDING_TIMEOUT_MINUTES', 30),
    ],

];
