<?php


return [
    /*
   |--------------------------------------------------------------------------
   | Siop Domain
   |--------------------------------------------------------------------------
   |
   | This is the subdomain where Siop will be accessible from. If this
   | setting is null, Siop will reside under the same domain as the
   | application. Otherwise, this value will serve as the subdomain.
   |
   */

    'domain' => env('SIOP_DOMAIN'),


    /*
    |--------------------------------------------------------------------------
    | Siop Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Siop will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('SIOP_PATH', 'siop'),

    /*
   |--------------------------------------------------------------------------
   | Siop Route Middleware
   |--------------------------------------------------------------------------
   |
   | These middleware will get attached onto each Siop route, giving you
   | the chance to add your own middleware to this list or change any of
   | the existing middleware. Or, you can simply stick with this list.
   |
   */

    'middleware' => ['web'],


    /*
   |--------------------------------------------------------------------------
   | Siop Severity Configuration
   |--------------------------------------------------------------------------
   |
   | These parameters determine severity that will be set to specified security event types
   |
   */
    'xss_severity' => 'medium',
    'sql_injection_severity' => 'medium',
    "honeypot_severity" => 'high',

    /*
   |--------------------------------------------------------------------------
   | Siop Block Method
   |--------------------------------------------------------------------------
   |
   | This parameter determines the method that will be used to block IPs
   |  Fail2Ban - will log IPs to ban, requires additional configuration
   |  middleware - will block IPs using middleware, does not require any additional configuration, however has much worse performance
   |
   */

    'blocking_method' => 'middleware', //middleware or fail2ban


    /*
    |--------------------------------------------------------------------------
    | Siop Fail2Ban integration settings
    |--------------------------------------------------------------------------
    |
    | These parameters are required for integration with fail2ban
    |
    */
    'fail2ban_log_path' => 'logs/fail2ban.log',
    'fail2ban_ban_time' => '10m',


    /*
    |--------------------------------------------------------------------------
    | Siop Block Time
    |--------------------------------------------------------------------------
    |
    | These parameters determine default time for IP blocking when middleware block method is chosen
    |
    */
    'block_time' => '100y',

    //Specify generator class for metadata
    'meta_generator' => \Savrock\Siop\MetaGenerator::class,

    //Honeypot routes
    'enable_honeypots' => true,
    'honeypot_routes' => [
        'wp-admin',
        'wp-login.php',
        'private-api',
    ],


    'enable_pattern_analysis' => true,
    'pattern_analysis_cooldown' => 0.5 //in minutes
];
