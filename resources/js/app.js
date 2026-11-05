import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert-auto-dismiss').forEach((alert) => {
        window.setTimeout(() => {
            if (alert && alert.parentNode) {
                const instance = bootstrap.Alert.getOrCreateInstance(alert);
                instance.close();
            }
        }, 5000);
    });

    const scanInput = document.querySelector('[data-rfid-scan-input]');
    if (scanInput) {
        scanInput.focus();
        document.addEventListener('click', (event) => {
            if (!event.target.closest('input, textarea, select, button, a')) {
                scanInput.focus();
            }
        });
    }
});
