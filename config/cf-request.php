<?php

return [
    'cloudflare' => [
        'api' => 'https://api.cloudflare.com/client/v4',
        'token' => env('CF_API_TOKEN'),
        'zoneId' => env('CF_API_ZONE_ID'),
    ],
    'allowStatusView' => env('CF_ALLOW_STATUS_VIEW', true),
];
