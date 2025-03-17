@props(['title' => __('Verify Two Factor Authentication'), 'content' => __('For additional security, please confirm your two-factor authentication code to continue.'), 'button' => __('Verify')])

@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingTwoFactor('{{ $confirmableId }}')"
    x-on:two-factor-confirmed.window="setTimeout(() => $event.detail.id === '{{ $confirmableId }}' && $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false })), 250);"
>
    {{ $slot }}
</span>

@once
<flux:modal wire:model.live="confirmingTwoFactor" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:subheading>{{ $content }}</flux:subheading>
        </div>

        <div x-data="{}" x-on:confirming-two-factor.window="setTimeout(() => $refs.two_factor_code.focus(), 250)">
            <flux:input 
                type="text" 
                inputmode="numeric"
                placeholder="{{ __('Authentication Code') }}" 
                autocomplete="one-time-code"
                x-ref="two_factor_code"
                wire:model="twoFactorCode"
                wire:keydown.enter="confirmTwoFactor" 
            />

            <flux:error name="twoFactorCode" class="mt-2" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:button variant="ghost" wire:click="stopConfirmingTwoFactor" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </flux:button>

            <flux:button variant="primary" wire:click="confirmTwoFactor" wire:loading.attr="disabled">
                {{ $button }}
            </flux:button>
        </div>
    </div>
</flux:modal>
@endonce
