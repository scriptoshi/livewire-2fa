<?php

namespace Scriptoshi\Livewire2fa\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait ConfirmsPasswords
{
    /**
     * Indicates if the user's password is being confirmed.
     *
     * @var bool
     */
    public $confirmingPassword = false;

    /**
     * The ID of the operation being confirmed.
     *
     * @var string|null
     */
    public $confirmableId = null;

    /**
     * The user's password.
     *
     * @var string
     */
    public $confirmablePassword = '';

    /**
     * Start confirming the user's password.
     *
     * @param  string  $confirmableId
     * @return void
     */
    public function startConfirmingPassword(string $confirmableId)
    {
        $this->resetErrorBag();

        if ($this->passwordIsConfirmed()) {
            return $this->dispatch(
                'password-confirmed',
                id: $confirmableId,
            );
        }

        $this->confirmingPassword = true;
        $this->confirmableId = $confirmableId;
        $this->confirmablePassword = '';

        $this->dispatch('confirming-password');
    }

    /**
     * Stop confirming the user's password.
     *
     * @return void
     */
    public function stopConfirmingPassword()
    {
        $this->confirmingPassword = false;
        $this->confirmableId = null;
        $this->confirmablePassword = '';
    }

    /**
     * Confirm the user's password.
     *
     * @return void
     */
    public function confirmPassword()
    {
        if (!$this->verifyPassword($this->confirmablePassword)) {
            throw ValidationException::withMessages([
                'confirmablePassword' => [__('This password does not match our records.')],
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->dispatch(
            'password-confirmed',
            id: $this->confirmableId,
        );

        $this->stopConfirmingPassword();
    }

    /**
     * Verify the given password.
     *
     * @param  string  $password
     * @return bool
     */
    protected function verifyPassword($password)
    {
        return Auth::guard()->validate([
            'email' => Auth::user()->email,
            'password' => $password,
        ]);
    }

    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return void
     */
    protected function ensurePasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        $this->passwordIsConfirmed($maximumSecondsSinceConfirmation) ? null : abort(403);
    }

    /**
     * Determine if the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return bool
     */
    protected function passwordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
