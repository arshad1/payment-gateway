<?php

return [
    /*
     * ---------------------------------------------------------------
     * formatting
     * ---------------------------------------------------------------
     *
     */

    'ecn_key' => env('PAYMENT_GATEWAY_ENC_KEY'),

    'secure_secret' => env('PAYMENT_GATEWAY_SECURE_SECRET'),

    'merchant_access_code' => env('PAYMENT_GATEWAY_MERCHANT_ACCESS_CODE'),

    'merchant_id' => env('PAYMENT_GATEWAY_MERCHANT_ID'),

    'gateway_url' => env('PAYMENT_GATEWAY_URL'),

    'redirect_url' => env('PAYMENT_GATEWAY_RETURN_URL'),

    'version' => env('PAYMENT_GATEWAY_VERSION',1),


];