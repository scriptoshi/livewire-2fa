<?php

namespace Scriptoshi\Livewire2fa\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;

trait ConfirmsTwoFactor
{
    /**
     * Indicates if the two-factor authentication is being confirmed.
     *
     * @var bool
     */
    public $confirmingTwoFactor = false;

    /**
     * The ID of the operation being confirmed.
     *
     * @var string|null
     */
    public $twoFactorConfirmableId = null;

    /**
     * The two-factor authentication code.
     *
     * @var string
     */
    public $twoFactorCode = '';

    /**
     * Start confirming the two-factor authentication.
     *
     * @param  string  $confirmableId
     * @return void
     */
    public function startConfirmingTwoFactor(string $confirmableId)
    {
        $this->resetErrorBag();

        if ($this->twoFactorIsConfirmed()) {
            return $this->dispatch('two-factor-confirmed', 
                id: $confirmableId,
            );
        }

        $this->confirmingTwoFactor = true;
        $this->twoFactorConfirmableId = $confirmableId;
        $this->twoFactorCode = '';

        $this->dispatch('confirming-two-factor');
    }

    /**
     * Stop confirming the two-factor authentication.
     *
     * @return void
     */
    public function stopConfirmingTwoFactor()
    {
        $this->confirmingTwoFactor = false;
        $this->twoFactorConfirmableId = null;
        $this->twoFactorCode = '';
    }

    /**
     * Confirm the two-factor authentication code.
     *
     * @return void
     */
    public function confirmTwoFactor()
    {
        $user = Auth::user();

        if (empty($user->two_factor_secret) || !$user->hasEnabledTwoFactorAuthentication()) {
            throw ValidationException::withMessages([
                'twoFactorCode' => [__('Two-factor authentication is not enabled for this account.')],
            ]);
        }

        if (empty($this->twoFactorCode) || !TwoFactorAuth::verify(
            decrypt($user->two_factor_secret),
            $this->twoFactorCode
        )) {
            throw ValidationException::withMessages([
                'twoFactorCode' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        session(['auth.two_factor_confirmed_at' => time()]);

        $this->dispatch('two-factor-confirmed',
            id: $this->twoFactorConfirmableId,
        );

        $this->stopConfirmingTwoFactor();
    }

    /**
     * Ensure that the user has recently confirmed their two-factor authentication.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return void
     */
    protected function ensureTwoFactorIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.two_factor_timeout', 900);

        $this->twoFactorIsConfirmed($maximumSecondsSinceConfirmation) ? null : abort(403);
    }

    /**
     * Determine if the user's two-factor authentication has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return bool
     */
    protected function twoFactorIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.two_factor_timeout', 900);

        return (time() - session('auth.two_factor_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
