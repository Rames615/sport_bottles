// Bootstrap handles navbar toggling automatically
// Turbo-compatible: re-initialize on both initial load and Turbo navigations
(function() {
    var _controller = null;

    function initNav() {
        if (_controller) _controller.abort();
        _controller = new AbortController();
        var signal = _controller.signal;

        // Close navbar when clicking on a non-dropdown link (mobile)
        var navLinks = document.querySelectorAll('.navbar-collapse .nav-link:not(.dropdown-toggle)');
        var navbarCollapse = document.querySelector('.navbar-collapse');

        if (navbarCollapse && navLinks.length > 0) {
            navLinks.forEach(function(link) {
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

    document.addEventListener('turbo:load', initNav);
    document.addEventListener('DOMContentLoaded', initNav);
})();