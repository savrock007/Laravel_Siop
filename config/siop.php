<?php


return [
    //Root path
    'entry_route' => 'security',

    //Middleware that determines who can access security dashboard
    'middleware' => ['web', 'auth'],

    //Default severity for XSS attacks
    'xss_severity' => 'medium',
    'sql_injection_severity' => 'medium',

    'enable_ip_block' => 'false',

    'block_time' => 100,
    "block_time_unit" => 'year',

    //Specify generator class for metadata
    'meta_generator' => \Savrock\Siop\MetaGenerator::class,

    //Specify which categories of Security events send notification
    'notifications' => [
        "xss" => false,
        "custom" => true,

    ]
];
