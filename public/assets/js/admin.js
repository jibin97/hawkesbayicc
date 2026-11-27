/* Admin enhancements (the admin works without this file). Loaded as a file: the admin CSP blocks inline scripts. */
(function () {
    'use strict';

    // Section picker: show the chosen place as soon as it changes.
    document.querySelectorAll('form[data-autosubmit] select').forEach(function (select) {
        select.addEventListener('change', function () { select.form.submit(); });
    });

    // Ask before destructive actions.
    document.querySelectorAll('[data-confirm]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            if (!window.confirm(button.getAttribute('data-confirm'))) e.preventDefault();
        });
    });

    // Uploads can take a while: show progress and prevent double submits.
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            if (!button) return;
            setTimeout(function () { button.disabled = true; }, 0); // after the click's value is submitted
            button.setAttribute('aria-busy', 'true');
            button.textContent = 'Uploading…';
        });
    });
})();
