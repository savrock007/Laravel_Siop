<?php


return [
    /*
   |--------------------------------------------------------------------------
   | Siop Domain
   |--------------------------------------------------------------------------
   |
   | This is the subdomain where Horizon will be accessible from. If this
   | setting is null, Horizon will reside under the same domain as the
   | application. Otherwise, this value will serve as the subdomain.
   |
   */

    'domain' => env('SIOP_DOMAIN'),


    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('SIOP_PATH', 'siop'),

    /*
   |--------------------------------------------------------------------------
   | Horizon Route Middleware
   |--------------------------------------------------------------------------
   |
   | These middleware will get attached onto each Horizon route, giving you
   | the chance to add your own middleware to this list or change any of
   | the existing middleware. Or, you can simply stick with this list.
   |
   */

    'middleware' => ['web'],

    //Default severity for XSS attacks
    'xss_severity' => 'medium',
    'sql_injection_severity' => 'medium',
    "honeypot_severity" => 'high',


    'blocking_method' => 'fail2ban', //middleware or fail2ban

    //block time for middleware blocking method
    'block_time' => 100,
    "block_time_unit" => 'year',

    //Specify generator class for metadata
    'meta_generator' => \Savrock\Siop\MetaGenerator::class,

    //Specify which categories of Security events send notification
    'notifications' => [
        "xss" => false,
        "custom" => true,

    ],

    //Honeypot routes
    'honeypot_routes' => [
        'wp-admin',
        'wp-login.php',
        'private-api',
    ],
];
