<?php

namespace Scriptoshi\Livewire2fa\Traits;


use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Scriptoshi\Livewire2fa\Facades\TwoFactorAuth;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Session;

trait WithTwoFactorAuthentication
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    /**
     * Indicates if the two-factor authentication modal should be displayed.
     *
     * @var bool
     */
    public $showingTwoFactorModal = false;

    /**
     * The two-factor authentication code.
     *
     * @var string
     */
    public $twoFactorCode = '';

    /**
     * Indicates if recovery codes are being used for authentication.
     *
     * @var bool
     */
    public $usingRecoveryCode = false;

    /**
     * The recovery code being input.
     *
     * @var string
     */
    public $recoveryCode = '';

    /**
     * The user waiting for two-factor authentication.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $twoFactorAuthenticatingUser = null;

    /**
     * Attempt a login with two-factor authentication support.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attemptTwoFactorLogin(array $credentials, bool $remember = false): bool
    {
        // First try to authenticate with the provided credentials
        if (!Auth::attempt($credentials, $remember)) {
            return false;
        }
        $user = Auth::user();
        // Check if 2FA is enabled for the user
        if ($user && method_exists($user, 'hasEnabledTwoFactorAuthentication') && $user->hasEnabledTwoFactorAuthentication()) {
            // Store the authenticated user and log them out temporarily
            $this->twoFactorAuthenticatingUser = $user;
            Auth::logout();
            // Show the 2FA modal
            $this->showingTwoFactorModal = true;
            return false; // Return false since the login is not complete yet
        }
        // If 2FA is not enabled, authentication is complete
        return true;
    }

    /**
     * Verify the two-factor authentication code.
     *
     * @return void
     */
    public function verifyTwoFactorCode(): void
    {
        $this->resetErrorBag();

        if (!$this->twoFactorAuthenticatingUser) {
            $this->addError('twoFactorCode', __('Authentication error. Please try logging in again.'));
            return;
        }

        $valid = false;

        if ($this->usingRecoveryCode) {
            // Verify the recovery code
            $valid = collect($this->twoFactorAuthenticatingUser->recoveryCodes())->contains(
                fn($code) => hash_equals($code, $this->recoveryCode)
            );

            if ($valid) {
                // Replace the used recovery code
                $user = clone $this->twoFactorAuthenticatingUser;
                Auth::login($user);
                $user->replaceRecoveryCode($this->recoveryCode);
                Auth::logout();
            } else {
                $this->addError('recoveryCode', __('The provided recovery code is invalid.'));
                return;
            }
        } else {
            // Verify the 2FA code
            $valid = TwoFactorAuth::verify(
                decrypt($this->twoFactorAuthenticatingUser->two_factor_secret),
                $this->twoFactorCode
            );

            if (!$valid) {
                $this->addError('twoFactorCode', __('The provided two-factor authentication code was invalid.'));
                return;
            }
        }

        if ($valid) {
            // Complete the login
            Auth::login($this->twoFactorAuthenticatingUser, $this->remember ?? false);
            // Reset the 2FA state
            $this->resetTwoFactorState();
            // Redirect - we'll rely on the component's post-login redirect logic
            $this->dispatch('auth-success');
        }
    }

    /**
     * Cancel the two-factor authentication.
     *
     * @return void
     */
    public function cancelTwoFactorAuthentication(): void
    {
        $this->resetTwoFactorState();
    }

    /**
     * Toggle between using a recovery code and a regular authentication code.
     *
     * @return void
     */
    public function toggleRecoveryMode(): void
    {
        $this->usingRecoveryCode = !$this->usingRecoveryCode;
        $this->twoFactorCode = '';
        $this->recoveryCode = '';
        $this->resetErrorBag();
    }

    /**
     * Reset the two-factor authentication state.
     *
     * @return void
     */
    protected function resetTwoFactorState(): void
    {
        $this->showingTwoFactorModal = false;
        $this->twoFactorCode = '';
        $this->recoveryCode = '';
        $this->usingRecoveryCode = false;
        $this->twoFactorAuthenticatingUser = null;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    /**
     * Handle an incoming authentication request.
     */
    public function twoFactorLogin(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();
        // Try to login with 2FA if enabled
        if ($this->attemptTwoFactorLogin([
            'email' => $this->email,
            'password' => $this->password
        ], $this->remember)) {
            // If 2FA is not enabled or already verified, proceed with login
            Session::regenerate();
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
        // If we're showing the 2FA modal, don't proceed further
        if ($this->showingTwoFactorModal) {
            RateLimiter::clear($this->throttleKey());
            return;
        }
        // If we've reached here, login failed
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}
