<?php

namespace Scriptoshi\Livewire2fa;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Scriptoshi\Livewire2fa\Http\Livewire\TwoFactorChallenge;
use Scriptoshi\Livewire2fa\Http\Livewire\TwoFactorManagement;

class TwoFactorAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/two-factor-auth.php',
            'two-factor-auth'
        );

        $this->app->singleton('two-factor-auth', function ($app) {
            return new TwoFactorAuthManager(
                $app->make(Google2FA::class),
                $app->make('cache.store')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only register routes if 2FA is enabled
        if ($this->app->make('config')->get('two-factor-auth.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'two-factor-auth');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/two-factor-auth.php' => config_path('two-factor-auth.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/two-factor-auth'),
            ], 'views');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        // Register Livewire components
        Livewire::component('two-factor-challenge', TwoFactorChallenge::class);
        Livewire::component('two-factor-management', TwoFactorManagement::class);
    }
}
