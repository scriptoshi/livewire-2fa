<?php

use Illuminate\Support\Facades\Route;
use Scriptoshi\Livewire2fa\Http\Middleware\TwoFactor;

// Legacy routes for middleware-based approach
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/two-factor-challenge', function () {
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }
        return view('two-factor-auth::livewire.challenge-page');
    })->name('two-factor.challenge');

    Route::post('/two-factor-auth/login', function () {
        return redirect()->intended(route('dashboard'));
    })->middleware(TwoFactor::class)->name('two-factor.login');
});
