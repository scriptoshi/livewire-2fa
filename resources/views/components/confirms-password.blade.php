@props(['title' => __('Confirm Password'), 'content' => __('For your security, please confirm your password to continue.'), 'button' => __('Confirm')])

@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="setTimeout(() => $event.detail.id === '{{ $confirmableId }}' && $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false })), 250);"
>
    {{ $slot }}
</span>

@once
<flux:modal wire:model.live="confirmingPassword" class="md:w-96 overflow-hidden">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:subheading>{{ $content }}</flux:subheading>
        </div>

        <div x-data="{}" x-on:confirming-password.window="setTimeout(() => $refs.confirmable_password.focus(), 250)">
            <flux:input 
                type="password" 
                placeholder="{{ __('Password') }}" 
                autocomplete="current-password"
                x-ref="confirmable_password"
                wire:model="confirmablePassword"
                wire:keydown.enter="confirmPassword" 
            />

            <flux:error name="confirmablePassword" class="mt-2" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:button variant="ghost" wire:click="stopConfirmingPassword" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </flux:button>

            <flux:button variant="primary" wire:click="confirmPassword" wire:loading.attr="disabled">
                {{ $button }}
            </flux:button>
        </div>
    </div>
</flux:modal>
@endonce
