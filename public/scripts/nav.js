// Bootstrap handles navbar toggling automatically
// Wait for both DOM and Bootstrap to be ready
window.addEventListener('load', function () { // Close navbar when clicking on a non-dropdown link (mobile)
const navLinks = document.querySelectorAll('.navbar-collapse .nav-link:not(.dropdown-toggle)');
const navbarCollapse = document.querySelector('.navbar-collapse');

if (navbarCollapse && navLinks.length > 0) {
navLinks.forEach(link => {
link.addEventListener('click', function () { // Use Bootstrap's collapse if available, otherwise just hide
if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
if (bsCollapse) {
bsCollapse.hide();
}
} else { // Fallback: manually toggle class
navbarCollapse.classList.remove('show');
}
});
});
}
});