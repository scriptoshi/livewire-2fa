<?php

namespace Scriptoshi\Livewire2fa\Http\Livewire;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;

class TwoFactorChallenge extends Component
{
    /**
     * The authentication code being input.
     *
     * @var string
     */
    public $code = '';

    /**
     * The recovery code being input.
     *
     * @var string
     */
    public $recovery_code = '';

    /**
     * Indicates if recovery codes are being used for authentication.
     *
     * @var bool
     */
    public $recovery = false;

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount()
    {
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }
    }

    /**
     * Toggle between recovery code and regular code entry.
     *
     * @return void
     */
    public function toggleRecovery()
    {
        $this->recovery = !$this->recovery;
        $this->code = '';
        $this->recovery_code = '';
        $this->resetErrorBag();
    }

    /**
     * Attempt to authenticate using the provided code.
     *
     * @param \Illuminate\Contracts\Auth\StatefulGuard $guard
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authenticate(?StatefulGuard $guard = null)
    {
        $guard = $guard ?: Auth::guard();
        $this->resetErrorBag();

        if (!$this->hasValidCode() && !$this->hasValidRecoveryCode()) {
            return;
        }

        $user = $this->challengedUser();

        if ($code = $this->getValidRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        }

        $guard->login($user, session('login.remember', false));

        session()->forget('login.id');
        session()->forget('login.remember');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Determine if the user has provided a valid recovery code.
     *
     * @return bool
     */
    protected function hasValidRecoveryCode()
    {
        if (!$this->recovery || !$this->recovery_code) {
            return false;
        }

        $validated = collect($this->challengedUser()->recoveryCodes())->contains(
            fn($code) => hash_equals($code, $this->recovery_code)
        );

        if (!$validated) {
            $this->addError('recovery_code', __('The provided two factor recovery code was invalid.'));
            return false;
        }

        return true;
    }

    /**
     * Get the valid recovery code if one exists.
     *
     * @return string|null
     */
    protected function getValidRecoveryCode()
    {
        if (!$this->recovery || !$this->recovery_code) {
            return null;
        }

        return collect($this->challengedUser()->recoveryCodes())->first(
            fn($code) => hash_equals($code, $this->recovery_code)
        );
    }

    /**
     * Determine if the user has provided a valid auth code.
     *
     * @return bool
     */
    protected function hasValidCode()
    {
        if ($this->recovery || !$this->code) {
            return false;
        }

        $validated = TwoFactorAuth::verify(
            decrypt($this->challengedUser()->two_factor_secret),
            $this->code
        );

        if (!$validated) {
            $this->addError('code', __('The provided two factor authentication code was invalid.'));
            return false;
        }

        return true;
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return mixed
     */
    protected function challengedUser()
    {
        $userModel = config('auth.providers.users.model');

        if (
            !session()->has('login.id') ||
            !$user = $userModel::find(session('login.id'))
        ) {
            redirect()->route('login');
            return;
        }

        return $user;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('two-factor-auth::livewire.two-factor-challenge');
    }
}
