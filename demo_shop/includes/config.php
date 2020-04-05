<?php
$config = [
    'sign_password' => '73cdb059d0f29f275a34b370f8e4f900',        // your password
    'projectid' => 6028,                                          // your project id

    'test' => 1,            // disable in production
    'accepturl' => get_address('response.php?answer=accept'),
    'cancelurl' => get_address('response.php?answer=cancel'),
    'callbackurl' => get_address('response.php?answer=callback'),
];

$shopItems = [            // just sample shop items; unrelated to WebToPay library
    [
        'title' => 'Item A',
        'price' => 100,
        'currency' => 'EUR',
    ],
    [
        'title' => 'Item B',
        'price' => 2000,
        'currency' => 'EUR',
    ],
    [
        'title' => 'Item C',
        'price' => 4990,
        'currency' => 'EUR',
    ],
];