# Laravel Livewire 2FA

A simple and elegant two-factor authentication package for Laravel 12 using Livewire.

![Laravel 2FA Banner](https://via.placeholder.com/1200x400/3490dc/ffffff?text=Laravel+2FA)

## Overview

Laravel 2FA provides an easy way to add Google Authenticator compatible two-factor authentication to your Laravel 12 application. Built with Livewire and Alpine.js, it offers a modern, interactive user experience with minimal configuration.

### Features

-   🔒 Google Authenticator compatible (TOTP)
-   ⚡ Livewire-powered interactive components
-   🌓 Full dark mode support with Tailwind CSS
-   🛠️ Simple integration with existing authentication systems
-   🔑 Recovery codes for account access backup
-   🎨 Easily customizable views
-   🛡️ Built-in security features and rate limiting

## Requirements

-   PHP 8.2+
-   Laravel 12.x
-   Livewire 3.x

## Installation

### 1. Install the package via Composer

```bash
composer require scriptoshi/livewire-2fa
```

### 2. Publish the assets (optional)

```bash
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="config"
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="views"
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

## Customization

### Views

Customize the views by publishing them and modifying as needed:

```bash
php artisan vendor:publish --provider="Scriptoshi\Livewire2fa\TwoFactorAuthServiceProvider" --tag="views"
```

This will publish the views to `resources/views/vendor/two-factor-auth/`.

### Styling

The components use Tailwind CSS classes and support dark mode out of the box. You can customize the appearance by:

1. Publishing the views (as shown above)
2. Modifying the Tailwind classes or adding your own CSS
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

## Troubleshooting

### QR Code Not Displaying

Make sure your application has the `bacon/bacon-qr-code` package installed and that the user has a valid 2FA secret.

### Challenge Screen Issues

If users are not being redirected to the challenge screen correctly, ensure:

1. The middleware is properly registered
2. The `login.id` session variable is being set
3. Route names are correct in your configuration

## Security Considerations

This package implements several security best practices:

-   Encrypted storage of secrets and recovery codes
-   Rate limiting on verification attempts
-   Prevention of code reuse through timestamp verification

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
