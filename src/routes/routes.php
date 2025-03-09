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

//Route::get('/settings', [SettingsController::class, 'index'])->name('siop-settings.index');
Route::get('/settings/patterns', [SettingsController::class, 'patterns'])->name('siop.patterns.index');
Route::post('/patterns', [SettingsController::class, 'patternsStore'])->name('siop.patterns.store');
Route::delete('/patterns/{id}', [SettingsController::class, 'patternsDestroy'])->name('siop.patterns.destroy');

//Events
Route::get('/events', [EventController::class, 'list'])->name('siop-events.list');
Route::get('/event/{event}', [EventController::class, 'show'])->name('siop-events.show');
Route::delete('/event/{event}', [EventController::class, 'destroy'])->name('siop-events.destroy');
Route::post('/event/{event}/block-ip', [EventController::class, 'blockIp'])->name('siop-events.block-ip');
Route::post('/event/{event}/whitelist-ip', [EventController::class, 'whitelistIp'])->name('siop-events.whitelist-ip');





