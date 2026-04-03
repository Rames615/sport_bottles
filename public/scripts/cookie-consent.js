/**
 * cookie-consent.js — RGPD-compliant cookie consent (CNIL)
 *
 * - Shows Bootstrap modal on first visit (no prior consent stored).
 * - Stores consent in a first-party cookie `cookie_consent` with a 6-month expiry (CNIL max).
 * - Provides a global `openCookieConsent()` so the footer "Gérer les cookies" button
 *   can re-open the modal at any time (Art. 7(3) RGPD — right to withdraw).
 * - Turbo-compatible (listens to both DOMContentLoaded and turbo:load).
 */
(function () {
    'use strict';

    var COOKIE_NAME = 'cookie_consent';
    var MAX_AGE     = 60 * 60 * 24 * 180; // 180 days ≈ 6 months (CNIL recommendation)

    /* ── Cookie helpers ──────────────────────────────────────── */
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function setCookie(name, value) {
        document.cookie = name + '=' + encodeURIComponent(value)
            + '; path=/; max-age=' + MAX_AGE
            + '; SameSite=Lax';
    }

    /* ── Modal logic ─────────────────────────────────────────── */
    function showConsentModal() {
        var modalEl = document.getElementById('cookieConsentModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var cookieModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: 'static',
            keyboard: false
        });
        cookieModal.show();

        var acceptBtn  = document.getElementById('cookie-accept');
        var declineBtn = document.getElementById('cookie-decline');

        function onAccept() {
            setCookie(COOKIE_NAME, 'accepted');
            cookieModal.hide();
            cleanup();
        }
        function onDecline() {
            setCookie(COOKIE_NAME, 'declined');
            cookieModal.hide();
            cleanup();
        }
        function cleanup() {
            if (acceptBtn)  acceptBtn.removeEventListener('click', onAccept);
            if (declineBtn) declineBtn.removeEventListener('click', onDecline);
        }

        if (acceptBtn)  acceptBtn.addEventListener('click', onAccept);
        if (declineBtn) declineBtn.addEventListener('click', onDecline);
    }

    /* ── Init ────────────────────────────────────────────────── */
    function initCookieConsent() {
        var status = getCookie(COOKIE_NAME);
        if (status === 'accepted' || status === 'declined') return;
        showConsentModal();
    }

    /* ── Public API — allows re-opening from footer button ── */
    window.openCookieConsent = function () {
        showConsentModal();
    };

    document.addEventListener('DOMContentLoaded', initCookieConsent);
    document.addEventListener('turbo:load', initCookieConsent);
}());
