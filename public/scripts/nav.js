/**
 * nav.js - Navbar behaviour (Turbo-compatible)
 *
 * 1. Re-initialises Bootstrap dropdowns after every Turbo navigation so that
 *    the "Se connecter" and user-account dropdowns respond immediately on the
 *    first click without requiring a hard page refresh.
 * 2. Closes the mobile collapsed navbar when a non-dropdown link is clicked.
 *
 * Uses AbortController so event listeners from the previous page are cleanly
 * removed before attaching new ones.
 */
(function () {
    'use strict';

    var _controller = null;

    function initNav() {
        if (_controller) _controller.abort();
        _controller = new AbortController();
        var signal = _controller.signal;

        /* ── Re-initialise Bootstrap dropdowns ─────────────────────────
         * Bootstrap auto-initialises components on the initial DOMContentLoaded,
         * but after Turbo replaces the <body> it does NOT re-run auto-init.
         * We therefore call getOrCreateInstance() explicitly here so every
         * dropdown (including "Se connecter") works on the very first click
         * after a Turbo navigation.
         * ────────────────────────────────────────────────────────────── */
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
                bootstrap.Dropdown.getOrCreateInstance(el);
            });
            /* Collapse re-init handled by reinitBootstrap() in base.html.twig
               with {toggle:false} on the actual .collapse element. */
        }

        /* ── Mobile: close navbar on nav-link click ─────────────────── */
        var navLinks = document.querySelectorAll('.navbar-collapse .nav-link:not(.dropdown-toggle)');
        var navbarCollapse = document.querySelector('.navbar-collapse');

        if (navbarCollapse && navLinks.length > 0) {
            navLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    } else {
                        navbarCollapse.classList.remove('show');
                    }
                }, { signal: signal });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initNav);
    document.addEventListener('turbo:load', initNav);
}());