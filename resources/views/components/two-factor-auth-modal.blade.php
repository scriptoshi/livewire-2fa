<flux:modal wire:model.live="showingTwoFactorModal" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Two Factor Authentication') }}</flux:heading>
            <flux:subheading>
                @if (!isset($usingRecoveryCode) || !$usingRecoveryCode)
                    {{ __('Please enter the authentication code from your authenticator app.') }}
                @else
                    {{ __('Please enter one of your emergency recovery codes.') }}
                @endif
            </flux:subheading>
        </div>

        <div x-data="{}" x-on:showing-modal.window="setTimeout(() => $refs.twoFactorInput.focus(), 250)">
            @if (!isset($usingRecoveryCode) || !$usingRecoveryCode)
                <flux:input 
                    wire:model="twoFactorCode" 
                    type="text" 
                    inputmode="numeric" 
                    class="w-full" 
                    placeholder="{{ __('Authentication Code') }}"
                    x-ref="twoFactorInput"
                    wire:keydown.enter="verifyTwoFactorCode"
                />
                <flux:error name="twoFactorCode" class="mt-2" />
            @else
                <flux:input 
                    wire:model="recoveryCode" 
                    type="text" 
                    class="w-full" 
                    placeholder="{{ __('Recovery Code') }}"
                    x-ref="twoFactorInput"
                    wire:keydown.enter="verifyTwoFactorCode"
                />
                <flux:error name="recoveryCode" class="mt-2" />
            @endif
        </div>

        <div class="flex flex-col space-y-4">
            <button type="button"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline cursor-pointer text-center"
                wire:click="toggleRecoveryMode">
                {{ isset($usingRecoveryCode) && $usingRecoveryCode 
                    ? __('Use an authentication code') 
                    : __('Use a recovery code') 
                }}
            </button>

            <div class="flex justify-end space-x-3">
                <flux:button variant="ghost" wire:click="cancelTwoFactorAuthentication" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button variant="primary" wire:click="verifyTwoFactorCode" wire:loading.attr="disabled">
                    {{ __('Verify') }}
                </flux:button>
            </div>
        </div>
    </div>
</flux:modal>
