<?php

namespace Scriptoshi\Livewire2fa\Traits;

use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;

trait TwoFactorAuthenticatable
{
    /**
     * Determine if two-factor authentication has been enabled.
     *
     * @return bool
     */
    public function hasEnabledTwoFactorAuthentication()
    {
        if (config('two-factor-auth.confirm_enable')) {
            return !is_null($this->two_factor_secret) &&
                !is_null($this->two_factor_confirmed_at);
        }

        return !is_null($this->two_factor_secret);
    }

    /**
     * Get the user's two factor authentication recovery codes.
     *
     * @return array
     */
    public function recoveryCodes()
    {
        return json_decode(decrypt($this->two_factor_recovery_codes), true);
    }

    /**
     * Replace the given recovery code with a new one in the user's stored codes.
     *
     * @param  string  $code
     * @return void
     */
    public function replaceRecoveryCode($code)
    {
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(str_replace(
                $code,
                TwoFactorAuth::generateRecoveryCode(),
                decrypt($this->two_factor_recovery_codes)
            )),
        ])->save();
    }

    /**
     * Get the QR code SVG of the user's two factor authentication QR code URL.
     *
     * @return string
     */
    public function twoFactorQrCodeSvg()
    {
        return TwoFactorAuth::generateQrCodeSvg(
            config('app.name'),
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }

    /**
     * Get the two factor authentication QR code URL.
     *
     * @return string
     */
    public function twoFactorQrCodeUrl()
    {
        return TwoFactorAuth::generateQrCodeUrl(
            config('app.name'),
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }
}
