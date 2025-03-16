<?php

namespace Scriptoshi\Livewire2fa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateSecretKey()
 * @method static string generateRecoveryCode()
 * @method static array generateRecoveryCodes(int $count = 8)
 * @method static string generateQrCodeUrl(string $companyName, string $companyEmail, string $secret)
 * @method static string generateQrCodeSvg(string $companyName, string $companyEmail, string $secret)
 * @method static bool verify(string $secret, string $code)
 * 
 * @see \Scriptoshi\Livewire2fa\TwoFactorAuthManager
 */
class TwoFactorAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'two-factor-auth';
    }
}
