document.querySelectorAll('.modal[data-auto-open="true"]').forEach((modalElement) => {
    if (typeof bootstrap === 'undefined') {
        return;
    }

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
});
