<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Savrock\Siop\Http\Controllers\DashboardController;
use Savrock\Siop\Http\Controllers\EventController;



Route::group(
    [
        'prefix' => config('siop.entry_route'),
        'middleware' => config('siop.middleware'),
    ],
    function (){
        //Dashboard
        Route::get('/',function (){
            return redirect(config('siop.entry_route').'/dashboard');
        });
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('siop-dashboard.index');
        Route::get('/dashboard/data', [DashboardController::class, 'showDashboardData'])->name('siop-dashboard.data');

        //Events
        Route::get('/events', [EventController::class, 'list'])->name('siop-events.list');
        Route::get('/event/{event}', [EventController::class, 'show'])->name('siop-events.show');
        Route::delete('/event/{event}', [EventController::class, 'destroy'])->name('siop-events.destroy');
        Route::post('/event/{event}/block-ip', [EventController::class, 'blockIp'])->name('siop-events.block-ip');
        Route::post('/event/{event}/whitelist-ip', [EventController::class, 'whitelistIp'])->name('siop-events.whitelist-ip');
    });




