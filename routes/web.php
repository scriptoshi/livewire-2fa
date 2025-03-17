<?php

use Illuminate\Support\Facades\Route;
use Scriptoshi\Livewire2fa\Http\Middleware\TwoFactor;

// Route for handling the 2FA challenge during login
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/two-factor-challenge', function () {
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }
        return view('two-factor-auth::livewire.challenge-page');
    })->name('two-factor.challenge');
});

// Register the middleware to intercept login attempts
Route::middleware(['web'])->group(function () {
    // This route will handle the authentication attempt and redirect to 2FA if needed
    Route::post('/two-factor-auth/login', function () {
        // The middleware will handle redirecting to 2FA challenge if needed
        return redirect()->intended(route('dashboard'));
    })->middleware(TwoFactor::class)->name('two-factor.login');
});
