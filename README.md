# Laravel Livewire 2FA

A simple and elegant two-factor authentication package for Laravel 12 using Livewire and Flux components. Built to work seamlessly with Laravel 12's authentication stack.

## Overview

Laravel 2FA provides an easy way to add Google Authenticator compatible two-factor authentication to your Laravel 12 application. Built with Livewire and Flux components, it offers a modern, interactive user experience with minimal configuration.

### Features

-   🔒 Google Authenticator compatible (TOTP)
-   ⚡ Livewire-powered interactive components
-   🎨 Beautiful UI with Flux components
-   🌓 Full dark mode support
-   🛠️ Simple integration with existing authentication systems
-   🔑 Recovery codes for account access backup
-   🛡️ On-demand password and 2FA confirmation modals
-   🔄 Compatible with Laravel 12 and Livewire 3

## Requirements

-   PHP 8.2+
-   Laravel 12.x
-   Livewire 3.x
-   Flux components

## Installation

### 1. Install the package via Composer

```bash
composer require scriptoshi/livewire-2fa
```

### 2. (optional) If you need to customize, publish the assets

```bash
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="config"
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="views"
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="migrations"
```

### 3. Run the migrations

This will add the required columns to your users table.

```bash
php artisan migrate
```

### 4. Include the TwoFactorAuthenticatable trait in your User model

```php
use Scriptoshi\Livewire2fa\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use TwoFactorAuthenticatable;

    // ...
}
```

## Configuration

The package comes with sensible defaults, but you can customize it using the corresponding `.env` variables.
Add the following lines to your .env to customise the config

```bash
# Enable or disable 2FA functionality entirely
TWO_FACTOR_AUTH_ENABLED=true
# Require users to confirm their 2FA setup with a code
TWO_FACTOR_AUTH_CONFIRM=true
# How many OTP codes will be accepted (time window)
TWO_FACTOR_AUTH_WINDOW=1
# Number of recovery codes to generate
TWO_FACTOR_AUTH_RECOVERY_CODE_COUNT=8
# Timeout for 2FA verification (in seconds, default 15 minutes)
TWO_FACTOR_AUTH_TIMEOUT=600
```

## Basic Usage

### Adding 2FA Management to User Profile

Add the Livewire component to your user profile page:

```blade
<livewire:two-factor-management />
```

On Laravel 12 Starter kit:

1. Edit `resources/views/components/settings/layout.blade.php` and add:

```blade
<flux:navlist>
    <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
    <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
    <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
    <!--Add two factor-->
    <flux:navlist.item :href="route('settings.twofactor')" wire:navigate>{{ __('Two factor Auth') }}</flux:navlist.item>
</flux:navlist>
```

2. Create the two factor auth view:
   `resources/views/livewire/settings/twofactor.blade.php`

```blade
<?php
use Livewire\Volt\Component;

new class extends Component {

}; ?>

<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('Two Factor Auth')" :subheading="__('Manage two factor authentication')">
        <livewire:two-factor-management />
    </x-settings.layout>
</section>
```

3. Add the route to `routes/web.php`:

```php
Volt::route('settings/twofactor', 'settings.twofactor')->name('settings.twofactor');
```

### Integrating with Login (Laravel 12 Starter Kit)

The easiest way to integrate 2FA with Laravel 12's Livewire login is by adding the `WithTwoFactorAuthentication` trait directly to your login component:

1. Update your `resources/views/livewire/auth/login.blade.php` Volt component:

```php
<?php

use Scriptoshi\Livewire2fa\Traits\WithTwoFactorAuthentication;

new #[Layout('components.layouts.auth')] class extends Component {
    use WithTwoFactorAuthentication; // add this line

   ..... rest of the code

}; ?>
```

update the Login method on the form submit from `wire:submit="login"` to `wire:submit="twoFactorLogin"`

```html
<form wire:submit="twoFactorLogin">.... rest of the form</form>
```

Include the 2FA Modal after the form closing tag.

```html
 </form>  <!-- Form -->
    <!-- Include the 2FA Modal -->
    <x-two-factor-auth-modal />
```

That's it! The login component will now:

1. Attempt normal login with email/password
2. Check if the user has 2FA enabled
3. If 2FA is enabled, show the 2FA modal for code verification
4. Complete the login process after successful 2FA verification

## Using Confirmation Modals

For sensitive actions in your application, you can require password or 2FA verification:

### Password Confirmation Modal

1. Wrap the sensitive action in your component:

```blade
<x-confirms-password wire:then="enableAdminMode">
    <flux:button type="button" wire:loading.attr="disabled">
        {{ __('Enable') }}
    </flux:button>
</x-confirms-password>
```

2. Include the trait in your Livewire component:

```php
use Scriptoshi\Livewire2fa\Traits\ConfirmsPasswords;

class AdminForm extends Component
{
    use ConfirmsPasswords;

    public function enableAdminMode(): void
    {
        $this->ensurePasswordIsConfirmed();
        // Action logic here
    }
}
```

### Two-Factor Confirmation Modal

For even higher security, you can require 2FA confirmation for critical operations:

1. Include the modal component in your template:

```blade
<x-confirms-2fa wire:then="enableAdminMode">
    <flux:button type="button" wire:loading.attr="disabled">
        {{ __('Perform Critical Action') }}
    </flux:button>
</x-confirms-2fa>
```

2. Include the trait in your Livewire component:

```php
use Scriptoshi\Livewire2fa\Traits\ConfirmsTwoFactor;

class TwoFactorAuthenticationForm extends Component
{
    use ConfirmsTwoFactor;

    public function enableAdminMode(): void
    {
        $this->ensureTwoFactorIsConfirmed();
        // Action logic here
    }
}
```

## Customization

### Views

Customize the views by publishing them and modifying as needed:

```bash
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="views"
```

This will publish the views to `resources/views/vendor/two-factor-auth/`.

### Styling

The components use Flux components and support dark mode out of the box. You can customize the appearance by:

1. Publishing the views (as shown above)
2. Modifying the Flux component usage or class attributes
3. For more extensive customization, you can extend or override the Livewire components

## Using the Facade Directly

You can use the `TwoFactorAuth` facade directly for advanced use cases:

```php
use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;

// Generate a secret key
$secret = TwoFactorAuth::generateSecretKey();

// Verify a code
$valid = TwoFactorAuth::verify($secret, $code);

// Generate QR code SVG
$svg = TwoFactorAuth::generateQrCodeSvg($appName, $email, $secret);
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
