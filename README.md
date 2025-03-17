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

### 2. Publish the assets (optional)

```bash
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="config"
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="views"
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="js"
```

### 3. Run the migrations

This will add the necessary columns to your users table.

```bash
php artisan migrate
```

### 4. Add the trait to your User model

```php
use Scriptoshi\Livewire2fa\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use TwoFactorAuthenticatable;

    // ...
}
```

### 5. Include the JavaScript (if using confirmation modals)

Add the JavaScript file to your application's script bundle, or include it directly:

```html
<script src="{{ asset('js/vendor/two-factor-auth/two-factor-auth.js') }}"></script>
```

## Configuration

The package comes with sensible defaults, but you can customize it via the `config/two-factor-auth.php` file:

```php
return [
    // Enable or disable 2FA functionality entirely
    'enabled' => env('TWO_FACTOR_AUTH_ENABLED', true),

    // Require users to confirm their 2FA setup with a code
    'confirm_enable' => env('TWO_FACTOR_AUTH_CONFIRM', true),

    // How many OTP codes will be accepted (time window)
    'window' => env('TWO_FACTOR_AUTH_WINDOW', 1),

    // Number of recovery codes to generate
    'recovery_code_count' => env('TWO_FACTOR_AUTH_RECOVERY_CODE_COUNT', 8),
    
    // Timeout for 2FA verification (in seconds, default 15 minutes)
    'two_factor_timeout' => env('TWO_FACTOR_AUTH_TIMEOUT', 900),

    // Middleware for the 2FA routes
    'middleware' => ['web', 'auth'],

    // Route name for the challenge page
    'challenge_route' => 'two-factor.challenge',
];
```

## Basic Usage

### Adding 2FA Management to User Profile

Add the Livewire component to your user profile page:

```blade
<livewire:two-factor-management />
```

That's it! The component will handle enabling, disabling, and managing 2FA for the user.

### Integrating with Login

Add the middleware to your login logic. If you're using Laravel's built-in authentication:

1. Add this to your `routes/web.php`:

```php
// Intercept login attempts and handle 2FA if needed
Route::post('/login', [LoginController::class, 'login'])
    ->middleware([Scriptoshi\Livewire2fa\Http\Middleware\RedirectIfTwoFactorAuthenticatable::class]);
```

Alternatively, you can modify your `LoginController` to use the middleware.

## Using Confirmation Modals

This package provides two types of confirmation modals for protecting sensitive actions:

### Password Confirmation Modal

The password confirmation modal prompts users to enter their password before performing sensitive actions.

1. Include the modal component in your template:

```blade
<livewire:password-confirmation-modal />
```

2. Trigger the modal when needed:

```blade
<flux:button 
    wire:click="$dispatch('request-password-confirmation', { callback: 'deleteAccount' })"
>
    Delete Account
</flux:button>
```

3. Define the callback method in your Livewire component:

```php
#[On('deleteAccount')]
public function deleteAccount()
{
    // This only runs after password confirmation
    // Your sensitive action here
}
```

### Two-Factor Confirmation Modal

For even higher security, you can require 2FA confirmation for critical operations.

1. Include the modal component in your template:

```blade
<livewire:two-factor-confirmation-modal />
```

2. Trigger the modal when needed:

```blade
<flux:button 
    wire:click="$dispatch('request-2fa-confirmation', { callback: 'transferFunds' })"
>
    Transfer Funds
</flux:button>
```

3. Define the callback method in your Livewire component:

```php
#[On('transferFunds')]
public function transferFunds()
{
    // This only runs after 2FA confirmation
    // Your highly sensitive action here
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

## Advanced Usage

### Using the Facade Directly

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

### Event Handling

The package dispatches Livewire events that you can listen for:

-   `two-factor-enabled` - When 2FA is enabled
-   `two-factor-confirmed` - When 2FA setup is confirmed
-   `two-factor-disabled` - When 2FA is disabled
-   `recovery-codes-generated` - When new recovery codes are generated

Use these in your Livewire components to respond to 2FA actions.

## Example Components

The package includes example components to demonstrate how to use the confirmation modals:

```blade
<livewire:example-confirmation-modals />
```

This will show a page with examples of both password and 2FA confirmation.

## Troubleshooting

### QR Code Not Displaying

Make sure your application has the `bacon/bacon-qr-code` package installed and that the user has a valid 2FA secret.

### Challenge Screen Issues

If users are not being redirected to the challenge screen correctly, ensure:

1. The middleware is properly registered
2. The `login.id` session variable is being set
3. Route names are correct in your configuration

### Modal Not Showing

If confirmation modals are not appearing, check:

1. You've included the JavaScript file
2. The modal components are included in your template
3. The event names match in your dispatch calls

## Security Considerations

This package implements several security best practices:

-   Encrypted storage of secrets and recovery codes
-   Rate limiting on verification attempts
-   Prevention of code reuse through timestamp verification
-   Secure modal confirmation for sensitive operations

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
