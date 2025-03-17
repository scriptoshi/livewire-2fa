<?php

namespace Scriptoshi\Livewire2fa\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Scriptoshi\Livewire2fa\Traits\TwoFactorAuthenticatable;
use Symfony\Component\HttpFoundation\Response;

class TwoFactor
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard = null)
    {
        $this->guard = $guard ?: Auth::guard();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate credentials and get user
        $user = $this->validateCredentials($request);

        if (!$user) {
            return $next($request);
        }

        // Check if 2FA confirmation is required
        if (config('two-factor-auth.confirm_enable')) {
            if (
                optional($user)->two_factor_secret &&
                !is_null(optional($user)->two_factor_confirmed_at) &&
                in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
            ) {
                return $this->handleTwoFactorChallenge($request, $user);
            }
        } else if (
            optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))
        ) {
            return $this->handleTwoFactorChallenge($request, $user);
        }

        return $next($request);
    }

    /**
     * Validate the user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function validateCredentials(Request $request)
    {
        $model = $this->guard->getProvider()->getModel();

        return tap($model::where('email', $request->email)->first(), function ($user) use ($request) {
            if (!$user || !$this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                $this->throwFailedAuthenticationException($request);
            }
        });
    }

    /**
     * Throw a validation exception for failed authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Handle the two factor authentication challenge.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleTwoFactorChallenge(Request $request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => true]);
        }

        return redirect()->route('two-factor.challenge');
    }
}
