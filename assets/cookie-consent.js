// Cookie consent logic
// - shows the Bootstrap modal on first visit
// - stores consent in localStorage under 'cookies_accepted'
// - honors decline (stores 'declined')

document.addEventListener('DOMContentLoaded', () => {
    const key = 'cookies_accepted';
    const status = localStorage.getItem(key);

    // If already accepted/declined, do nothing
    if (status === 'accepted' || status === 'declined') return;

    // Show modal
    const modalEl = document.getElementById('cookieConsentModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    const cookieModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    cookieModal.show();

    const acceptBtn = document.getElementById('cookie-accept');
    const declineBtn = document.getElementById('cookie-decline');

    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            localStorage.setItem(key, 'accepted');
            cookieModal.hide();
        });
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', () => {
            localStorage.setItem(key, 'declined');
            cookieModal.hide();
        });
    }
});
