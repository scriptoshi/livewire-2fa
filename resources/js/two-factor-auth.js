document.addEventListener('DOMContentLoaded', function() {
    // Listen for password confirmation requests
    Livewire.on('request-password-confirmation', function(data) {
        if (!data.callback) {
            console.error('No callback provided for password confirmation');
            return;
        }
        
        // Find the password confirmation modal component
        let modal = Livewire.find(
            document.querySelector('[wire\\:component="password-confirmation-modal"]')?.getAttribute('wire:id')
        );
        
        if (modal) {
            modal.show(data.callback);
        } else {
            console.error('Password confirmation modal component not found');
        }
    });
    
    // Listen for 2FA confirmation requests
    Livewire.on('request-2fa-confirmation', function(data) {
        if (!data.callback) {
            console.error('No callback provided for 2FA confirmation');
            return;
        }
        
        // Find the 2FA confirmation modal component
        let modal = Livewire.find(
            document.querySelector('[wire\\:component="two-factor-confirmation-modal"]')?.getAttribute('wire:id')
        );
        
        if (modal) {
            modal.show(data.callback);
        } else {
            console.error('Two-factor confirmation modal component not found');
        }
    });
});
