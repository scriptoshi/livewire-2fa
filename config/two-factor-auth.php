<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication Enable
    |--------------------------------------------------------------------------
    |
    | This option controls if the two-factor authentication is enabled for your
    | application. When this is set to "true", users can enable 2FA for their
    | accounts. When set to "false", 2FA is completely disabled.
    |
    */

    'enabled' => env('TWO_FACTOR_AUTH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication Requires Confirmation
    |--------------------------------------------------------------------------
    |
    | This option controls if the two-factor authentication requires confirmation
    | before being fully enabled. When this is set to "true", users need to
    | provide a valid authentication code after setting up 2FA to confirm
    | their setup.
    |
    */

    'confirm_enable' => env('TWO_FACTOR_AUTH_CONFIRM', true),

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication Code Window
    |--------------------------------------------------------------------------
    |
    | This configuration option determines how many times a two-factor
    | authentication code can be used. By default, a code can only be used
    | once. But you may modify this setting based on your requirements.
    |
    */

    'window' => env('TWO_FACTOR_AUTH_WINDOW', 1),

    /*
    |--------------------------------------------------------------------------
    | Recovery Code Count
    |--------------------------------------------------------------------------
    |
    | This configuration option determines how many recovery codes are generated
    | when two-factor authentication is enabled. The default is 8 codes.
    |
    */

    'recovery_code_count' => env('TWO_FACTOR_AUTH_RECOVERY_CODE_COUNT', 8),

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication Timeout
    |--------------------------------------------------------------------------
    |
    | This configuration option determines how long (in seconds) a successful
    | two-factor authentication is valid before requiring re-verification.
    | Default is 15 minutes (900 seconds).
    |
    */

    'two_factor_timeout' => env('TWO_FACTOR_AUTH_TIMEOUT', 900),

    /*
    |--------------------------------------------------------------------------
    | Two Factor Routes Middleware
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the middleware stack that should be
    | used for the endpoints that are related to the two-factor authentication
    | management. Typically, 'web' middleware is required as a minimum.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Challenge Route
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the route name for the two-factor
    | authentication challenge page. This is the page that users will see
    | when logging in with 2FA enabled.
    |
    */

    'challenge_route' => 'two-factor.challenge',

];
