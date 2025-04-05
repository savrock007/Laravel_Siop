<?php

namespace Savrock\Siop;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class SiopApplicationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorization();
    }

    /**
     * Configure the Siop authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();
    }

    protected function gate()
    {
        Gate::define('viewSiop', function ($user) {
            return $user->id == 1;
        });
    }



    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
