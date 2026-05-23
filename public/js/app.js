const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');

if (menuButton && menu) {
    menuButton.addEventListener('click', () => {
        menu.classList.toggle('is-open');
    });
}

document.querySelectorAll('.app-modal[data-auto-open="true"]').forEach((modalElement) => {
    if (typeof bootstrap === 'undefined') {
        return;
    }

    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
    modalInstance.show();
});
