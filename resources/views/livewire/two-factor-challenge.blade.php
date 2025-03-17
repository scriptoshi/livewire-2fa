<div>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        @if (!$recovery)
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        @else
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        @endif
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="authenticate">
        <div class="mt-4" x-data="{}"
            x-on:confirming-two-factor-authentication.window="setTimeout(() => $refs.code.focus(), 250)">
            @if (!$recovery)
                <flux:input 
                    wire:model="code" 
                    id="code" 
                    label="{{ __('Code') }}"
                    type="text" 
                    class="w-full" 
                    inputmode="numeric"
                    name="code" 
                    autofocus 
                    autocomplete="one-time-code" 
                />
            @else
                <flux:input 
                    wire:model="recovery_code" 
                    id="recovery_code" 
                    label="{{ __('Recovery Code') }}"
                    type="text"
                    class="w-full"
                    name="recovery_code" 
                    autocomplete="one-time-code" 
                />
            @endif

            <flux:error name="{{ $recovery ? 'recovery_code' : 'code' }}" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="button"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline cursor-pointer"
                wire:click="toggleRecovery">
                {{ $recovery ? __('Use an authentication code') : __('Use a recovery code') }}
            </button>

            <flux:button class="ml-4">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>
</div>
