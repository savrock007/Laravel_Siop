<?php


return [
    //Root path
    'entry_route' => 'security',

    //Default severity for XSS attacks
    'xss_middlware_severity' => 'medium',

    'block_time' => 100,
    "block_time_unit" => 'year',
    //Specify generator class for metadata
    'meta_generator' => \Savrock\Siop\MetaGenerator::class,

    //Specify notifier class
    'notifier' => \Savrock\Siop\Notifier::class,

    //Specify which categories of Security events send notification
    'notifications' => [
        "xss" => false,
        "custom" => true,

    ]
];
