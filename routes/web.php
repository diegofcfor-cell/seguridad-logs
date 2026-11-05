<?php

use App\Http\Controllers\SecurityDashboardController;
use App\Http\Controllers\SecurityLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/security-login', function () {
    return view('security-login');
})->middleware('guest');

Route::post('/security-login', [SecurityLoginController::class, 'login'])
    ->middleware('guest');

Route::get('/security-dashboard', [SecurityDashboardController::class, 'index'])
    ->middleware(['auth', 'can:viewSecurityDashboard']);

Route::get('/security-login', function () {
    return view('security-login');
})->middleware('guest')->name('login');

Route::get('/security-dashboard/export', [SecurityDashboardController::class, 'export'])
    ->middleware(['auth', 'can:viewSecurityDashboard']);

