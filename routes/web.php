<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BikeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingSettingsController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RentalInspectionController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'admin'])->delete('/bikes/{bike}', [BikeController::class, 'destroy']);

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/export/{type}', [DashboardController::class, 'export'])->name('export');
    Route::resource('bikes', BikeController::class)->except('destroy');
    Route::resource('rentals', RentalController::class);
    Route::get('/rentals/{rental}/return', [RentalInspectionController::class, 'createReturn'])->name('rentals.return.create');
    Route::post('/rentals/{rental}/return', [RentalInspectionController::class, 'storeReturn'])->name('rentals.return.store');
    Route::resource('service', ServiceRecordController::class)->parameters(['service' => 'serviceRecord'])->except('show');
    Route::resource('customers', CustomerController::class);

    Route::middleware('admin')->group(function () {
        Route::delete('/bikes/{bike}', [BikeController::class, 'destroy'])->name('bikes.destroy');
        Route::post('/locations/cities', [LocationController::class, 'storeCity'])->name('locations.cities.store');
        Route::delete('/locations/cities/{city}', [LocationController::class, 'destroyCity'])->name('locations.cities.destroy');
        Route::resource('locations', LocationController::class)->except('show');
        Route::get('/landing', [LandingSettingsController::class, 'edit'])->name('landing.edit');
        Route::put('/landing', [LandingSettingsController::class, 'update'])->name('landing.update');
        Route::resource('users', UserController::class)->except('show');
    });
});
