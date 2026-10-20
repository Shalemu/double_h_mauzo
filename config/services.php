<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    // Mobishastra (mshastra.com) bulk SMS — same setup as KANISASOFT demo_backend.
    'mshastra' => [
        'user' => env('MSHASTRA_USER'),
        'password' => env('MSHASTRA_PASSWORD'),
        'sender' => env('MSHASTRA_SENDERID'),
        'json_url' => env('MSHASTRA_JSON_URL', 'http://mshastra.com/sendsms_api_json.aspx'),
    ],

];
