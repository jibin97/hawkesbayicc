/*
 * Progressive enhancement only: every feature here has a no-JS fallback.
 * - Mobile menu (<details>): close on Escape, outside click, or link tap.
 * - Scroll reveal: fade-in for .reveal elements (skipped when reduced motion is preferred).
 */
(function () {
    'use strict';

    // ---------- Mobile menu ----------
    document.querySelectorAll('details[data-menu]').forEach(function (menu) {
        var summary = menu.querySelector('summary');

        function close(returnFocus) {
            if (!menu.open) return;
            menu.open = false;
            if (returnFocus && summary) summary.focus();
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') close(true);
        });
        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target)) close(false);
        });
        menu.addEventListener('click', function (event) {
            if (event.target.closest('a')) close(false);
        });
        // Close if the screen grows past the mobile breakpoint while open.
        window.matchMedia('(min-width: 64em)').addEventListener('change', function (mq) {
            if (mq.matches) close(false);
        });
    });

    // ---------- Scroll reveal ----------
    var reveals = document.querySelectorAll('.reveal');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!reveals.length) return;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        reveals.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 });

    reveals.forEach(function (el) { observer.observe(el); });
})();
