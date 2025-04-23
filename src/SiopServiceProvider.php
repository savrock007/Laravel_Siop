<?php

namespace Savrock\Siop;

use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\Events\PatternAnalysisEvent;
use Savrock\Siop\Http\Controllers\HoneypotController;
use Savrock\Siop\Http\Middleware\BlockIps;
use Savrock\Siop\Http\Middleware\SiopThrottleRequests;
use Savrock\Siop\Http\Middleware\SqlInjectionProtection;
use Savrock\Siop\Http\Middleware\XssProtection;
use Savrock\Siop\Listeners\PatternAnalysisListener;
use Savrock\Siop\Listeners\SecurityEventListener;

class SiopServiceProvider extends ServiceProvider
{
    /**
     *
     * @return void
     */

    public function boot()
    {
        $this->registerEvents();
        $this->registerRoutes();
        $this->registerResources();
        $this->offerPublishing();
        $this->registerCommands();


        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('siop.xss', XssProtection::class);
        $router->aliasMiddleware('siop.sql', SqlInjectionProtection::class);
        $router->aliasMiddleware('siop.ip_block', BlockIps::class);
        $router->aliasMiddleware('siop.throttle', SiopThrottleRequests::class);

        $this->registerHoneypotRoutes();


        $router->middlewareGroup('siop_security', [
            BlockIps::class,
            XssProtection::class,
            SqlInjectionProtection::class,
        ]);


    }

    public function registerEvents()
    {
        Event::listen(PatternAnalysisEvent::class, PatternAnalysisListener::class);
    }

    public function registerRoutes()
    {

        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        Route::group([
            'domain' => config('siop.domain', null),
            'prefix' => config('siop.path'),
            'namespace' => 'Savrock\Siop\Http\Controllers',
            'middleware' => config('siop.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');
        });
    }

    protected function registerResources()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'siop');
    }

    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/SiopServiceProvider.stub' => app_path('Providers/SiopServiceProvider.php'),
            ], 'siop-provider');

            $this->publishes([
                __DIR__ . '/../config/siop.php' => config_path('siop.php'),
            ], 'siop-config');
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,

            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */

    protected function registerHoneypotRoutes()
    {
        if (!config('siop.enable_honeypots')) {
            return;
        }


        $routes = config('siop.honeypot_routes', []);

        foreach ($routes as $route) {
            Route::any($route, [HoneypotController::class, 'handle'])->name("honeypot.{$route}");
        }
    }
}
