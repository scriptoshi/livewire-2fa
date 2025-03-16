<div>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Two Factor Authentication') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Add additional security to your account using two factor authentication.') }}
            </p>
        </header>

        <div class="mt-5 space-y-6">
            @if (! $this->enabled)
                <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow-md">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('You have not enabled two factor authentication.') }}
                    </h3>

                    <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p>
                            {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <x-confirms-password wire:then="enableTwoFactorAuthentication">
                            <x-button type="button" wire:loading.attr="disabled">
                                {{ __('Enable') }}
                            </x-button>
                        </x-confirms-password>
                    </div>
                </div>
            @else
                <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow-md">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        @if ($this->confirming)
                            {{ __('Finish enabling two factor authentication.') }}
                        @else
                            {{ __('You have enabled two factor authentication.') }}
                        @endif
                    </h3>

                    <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                        <p>
                            {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                        </p>
                    </div>

                    @if ($this->qrCode)
                        <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-semibold">
                                @if ($this->confirming)
                                    {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                                @else
                                    {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                                @endif
                            </p>
                        </div>

                        <div class="mt-4 p-2 inline-block bg-white">
                            {!! $this->qrCode !!}
                        </div>

                        @if ($this->secretKey)
                            <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                                <p class="font-semibold">
                                    {{ __('Setup Key') }}: <span>{{ $this->secretKey }}</span>
                                </p>
                            </div>
                        @endif

                        @if ($this->confirming)
                            <div class="mt-4">
                                <x-label for="code" value="{{ __('Code') }}" />

                                <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric"
                                    wire:model="confirmationCode" autofocus autocomplete="one-time-code" />

                                <x-input-error :for="'confirmationCode'" class="mt-2" />
                            </div>
                        @endif
                    @endif

                    @if (! empty($this->recoveryCodes) && ! $this->confirming)
                        <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-semibold">
                                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 rounded-lg">
                            @foreach (json_decode(decrypt($this->recoveryCodes), true) as $code)
                                <div>{{ $code }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 space-x-3">
                        @if ($this->confirming)
                            <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                                <x-button type="button" class="mr-3" wire:loading.attr="disabled">
                                    {{ __('Confirm') }}
                                </x-button>
                            </x-confirms-password>
                        @else
                            @if (! empty($this->recoveryCodes))
                                <x-confirms-password wire:then="regenerateRecoveryCodes">
                                    <x-button type="button" class="mr-3">
                                        {{ __('Regenerate Recovery Codes') }}
                                    </x-button>
                                </x-confirms-password>
                            @else
                                <x-confirms-password wire:then="showRecoveryCodes">
                                    <x-button type="button" class="mr-3">
                                        {{ __('Show Recovery Codes') }}
                                    </x-button>
                                </x-confirms-password>
                            @endif
                        @endif

                        @if ($this->confirming)
                            <x-confirms-password wire:then="disableTwoFactorAuthentication">
                                <x-secondary-button wire:loading.attr="disabled">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                            </x-confirms-password>
                        @else
                            <x-confirms-password wire:then="disableTwoFactorAuthentication">
                                <x-danger-button wire:loading.attr="disabled">
                                    {{ __('Disable') }}
                                </x-danger-button>
                            </x-confirms-password>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>