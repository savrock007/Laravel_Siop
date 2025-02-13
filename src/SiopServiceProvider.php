<?php

namespace Savrock\Siop;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\Listeners\SecurityEventListener;
use Savrock\Siop\Services\SiopService;
use Savrock\Siop\Http\Middleware\XssProtection;

class SiopServiceProvider extends ServiceProvider
{
    /**
     *
     * @return void
     */

    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'siop');

        $this->publishes([__DIR__ . '/config/siop.php' => config_path('siop.php')]);
        $this->mergeConfigFrom(
            __DIR__ . '/config/siop.php', 'siop'
        );


        //$this->app['router']->aliasMiddleware('sql_injection', \YourVendor\SecurityPackage\Http\Middleware\SqlInjectionProtection::class);


        Event::listen(NewSecurityEvent::class, SecurityEventListener::class);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('siop', function () {
            return new SiopService();
        });

        app('router')->aliasMiddleware('xss', XssProtection::class);
    }
}
