<section class="mt-5 space-y-6 w-full">
    @if (!$this->enabled)
        <div class="rounded  bg-white dark:bg-gray-800 ">
            <flux:heading size="lg">
                {{ __('You have not enabled two factor authentication.') }}
            </flux:heading>

            <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                </p>
            </div>

            <div class="mt-5">
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <flux:button type="button" wire:loading.attr="disabled">
                        {{ __('Enable') }}
                    </flux:button>
                </x-confirms-password>
            </div>
        </div>
    @else
        <div class="rounded  bg-white dark:bg-gray-800 ">
            <flux:heading size="lg">
                @if ($this->confirming)
                    {{ __('Finish enabling two factor authentication.') }}
                @else
                    {{ __('You have enabled two factor authentication.') }}
                @endif
            </flux:heading>

            <div class="mt-3 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                </p>
            </div>

            @if ($this->qrCode && $this->confirming)
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
                        <flux:input wire:model="confirmationCode" id="code" label="{{ __('Code') }}"
                            type="text" name="code" class="w-1/2" inputmode="numeric" autofocus
                            autocomplete="one-time-code" />

                        <flux:error name="confirmationCode" class="mt-2" />
                    </div>
                @endif
            @endif

            @if (!empty($this->recoveryCodes) && !$this->confirming)
                <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 dark:bg-gray-900 rounded-lg">
                    @foreach (json_decode(decrypt($this->recoveryCodes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif

            <div class="mt-5 space-x-3">
                @if ($this->confirming)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <flux:button type="button" class="mr-3" wire:loading.attr="disabled">
                            {{ __('Confirm') }}
                        </flux:button>
                    </x-confirms-password>
                @else
                    @if (!empty($this->recoveryCodes))
                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <flux:button type="button" class="mr-3">
                                {{ __('Regenerate Recovery Codes') }}
                            </flux:button>
                        </x-confirms-password>
                        <flux:button type="button" wire:click="hideRecoveryCodes" variant="outline" class="mr-3">
                            {{ __('Done') }}
                        </flux:button>
                    @else
                        <x-confirms-password wire:then="showRecoveryCodes">
                            <flux:button type="button" class="mr-3">
                                {{ __('Show Recovery Codes') }}
                            </flux:button>
                        </x-confirms-password>
                    @endif
                @endif

                @if ($this->confirming)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <flux:button variant="ghost" wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </flux:button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <flux:button variant="danger" wire:loading.attr="disabled">
                            {{ __('Disable') }}
                        </flux:button>
                    </x-confirms-password>
                @endif
            </div>
        </div>
    @endif
</section>
