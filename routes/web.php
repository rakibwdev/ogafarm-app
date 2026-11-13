<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InviteController;
use Illuminate\Support\Facades\Route;

// Public landing (S01) — fully browsable without an app install.
Route::get('/', fn () => view('landing'))->name('landing');

// Invite deep link — attribution in session, then registration (spec §7).
Route::get('/invite/{code}', [InviteController::class, 'resolve'])->name('invite.resolve');

// Auth.
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Email verification (spec §4: verify contact step).
    Route::get('/email/verify', \App\Http\Controllers\Auth\VerificationController::class.'@show')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', \App\Http\Controllers\Auth\VerificationController::class.'@verify')
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', \App\Http\Controllers\Auth\VerificationController::class.'@resend')
        ->middleware('throttle:6,1')->name('verification.send');

    // Role-aware dashboard (spec §2).
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')->name('dashboard');
});

require __DIR__.'/subscriptions.php';
