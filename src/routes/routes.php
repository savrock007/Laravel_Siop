<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Savrock\Siop\Http\Controllers\DashboardController;
use Savrock\Siop\Http\Controllers\EventController;
use Savrock\Siop\Http\Controllers\SettingsController;


//Dashboard
Route::get('/', function () {
    return redirect(config('siop.path') . '/dashboard');
});
Route::get('/dashboard', [DashboardController::class, 'index'])->name('siop-dashboard.index');
Route::get('/dashboard/data', [DashboardController::class, 'showDashboardData'])->name('siop-dashboard.data');


//Settings
Route::get('/settings', [SettingsController::class, 'index'])->name('siop-settings.index');



//Events
Route::group([
    'prefix' => 'events'
], function () {
    Route::get('/', [EventController::class, 'list'])->name('siop-events.list');
    Route::get('/{event}', [EventController::class, 'show'])->name('siop-events.show');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('siop-events.destroy');
    Route::post('/{event}/block-ip', [EventController::class, 'blockIp'])->name('siop-events.block-ip');
    Route::post('/{event}/whitelist-ip', [EventController::class, 'whitelistIp'])->name('siop-events.whitelist-ip');

});






