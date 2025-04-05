<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Savrock\Siop\SiopApplicationServiceProvider;

class SiopServiceProvider extends SiopApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

    }

    /**
     * Register the Siop gate.
     *
     * This gate determines who can access Siop in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewSiop', function ($user) {
            return $user->id == 1;
        });
    }
}
