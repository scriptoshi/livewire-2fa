<?php

namespace Scriptoshi\Livewire2fa\Http\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;
use Scriptoshi\Livewire2fa\Traits\ConfirmsPasswords;

class TwoFactorManagement extends Component
{
    use ConfirmsPasswords;
    
    /**
     * Indicates if 2FA is enabled.
     *
     * @var bool
     */
    public $enabled = false;

    /**
     * Indicates if the user is confirming their 2FA setup.
     *
     * @var bool
     */
    public $confirming = false;

    /**
     * The QR code SVG.
     *
     * @var string|null
     */
    public $qrCode = null;

    /**
     * The secret key.
     *
     * @var string|null
     */
    public $secretKey = null;

    /**
     * The recovery codes.
     *
     * @var string|null
     */
    public $recoveryCodes = null;

    /**
     * The confirmation code.
     *
     * @var string
     */
    public $confirmationCode = '';

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $user = Auth::user();

        $this->enabled = $user->hasEnabledTwoFactorAuthentication();

        if ($this->enabled) {
            $this->recoveryCodes = $user->two_factor_recovery_codes;

            // Check if confirmation is required but not yet provided
            if (
                config('two-factor-auth.confirm_enable') &&
                !is_null($user->two_factor_secret) &&
                is_null($user->two_factor_confirmed_at)
            ) {
                $this->confirming = true;
                $this->showQrCode();
                $this->showSecretKey();
            }
        }
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @return void
     */
    public function enableTwoFactorAuthentication()
    {
        // Ensure the user has confirmed their password before proceeding
        $this->ensurePasswordIsConfirmed();
        
        $user = Auth::user();

        // Generate secret and recovery codes
        $user->forceFill([
            'two_factor_secret' => encrypt(TwoFactorAuth::generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return TwoFactorAuth::generateRecoveryCode();
            })->all())),
        ])->save();

        $this->enabled = true;
        $this->confirming = config('two-factor-auth.confirm_enable', true);

        // Show QR code and setup key
        $this->showQrCode();
        $this->showSecretKey();
        $this->showRecoveryCodes();

        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm the two factor authentication configuration.
     *
     * @return void
     */
    public function confirmTwoFactorAuthentication()
    {
        // Ensure the user has confirmed their password before proceeding
        $this->ensurePasswordIsConfirmed();
        
        $user = Auth::user();

        if (
            empty($user->two_factor_secret) ||
            empty($this->confirmationCode) ||
            !TwoFactorAuth::verify(decrypt($user->two_factor_secret), $this->confirmationCode)
        ) {
            throw ValidationException::withMessages([
                'confirmationCode' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->confirming = false;
        $this->dispatch('two-factor-confirmed');
    }

    /**
     * Display the QR code to the user.
     *
     * @return void
     */
    public function showQrCode()
    {
        $user = Auth::user();

        if (!is_null($user->two_factor_secret)) {
            $this->qrCode = $user->twoFactorQrCodeSvg();
        }
    }

    /**
     * Display the setup key to the user.
     *
     * @return void
     */
    public function showSecretKey()
    {
        $user = Auth::user();

        if (!is_null($user->two_factor_secret)) {
            $this->secretKey = decrypt($user->two_factor_secret);
        }
    }

    /**
     * Display the recovery codes to the user.
     *
     * @return void
     */
    public function showRecoveryCodes()
    {
        // Ensure the user has confirmed their password before proceeding
        $this->ensurePasswordIsConfirmed();
        
        $user = Auth::user();

        if (!is_null($user->two_factor_recovery_codes)) {
            $this->recoveryCodes = $user->two_factor_recovery_codes;
        }
    }

    /**
     * Generate new recovery codes for the user.
     *
     * @return void
     */
    public function regenerateRecoveryCodes()
    {
        // Ensure the user has confirmed their password before proceeding
        $this->ensurePasswordIsConfirmed();
        
        $user = Auth::user();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return TwoFactorAuth::generateRecoveryCode();
            })->all())),
        ])->save();

        $this->showRecoveryCodes();
        $this->dispatch('recovery-codes-generated');
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @return void
     */
    public function disableTwoFactorAuthentication()
    {
        // Ensure the user has confirmed their password before proceeding
        $this->ensurePasswordIsConfirmed();
        
        $user = Auth::user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->enabled = false;
        $this->confirming = false;
        $this->qrCode = null;
        $this->secretKey = null;
        $this->recoveryCodes = null;

        $this->dispatch('two-factor-disabled');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('two-factor-auth::livewire.two-factor-management');
    }
}
