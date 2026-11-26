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

    // ---------- Forms: inline validation + sending state ----------
    // Server-side validation is authoritative; this only gives faster feedback with the same wording.
    function fieldInputs(field) {
        return field.querySelectorAll('input:not([type="hidden"]), select, textarea');
    }

    function fieldError(field) {
        var inputs = fieldInputs(field);
        var first = inputs[0];
        if (!first) return '';

        if (first.type === 'radio') {
            var anyChecked = Array.prototype.some.call(inputs, function (i) { return i.checked; });
            return first.required && !anyChecked ? field.dataset.msgRequired : '';
        }
        if (first.type === 'checkbox' && inputs.length === 1) {
            return first.required && !first.checked ? field.dataset.msgRequired : '';
        }
        if (first.type === 'checkbox') return '';

        var v = first.validity;
        if (v.valueMissing || (first.required && first.value.trim() === '')) return field.dataset.msgRequired;
        if (first.type === 'date' && v.rangeUnderflow) return field.dataset.msgFuture;
        if (!v.valid) return field.dataset.msgInvalid;
        return '';
    }

    function showFieldError(field, message) {
        var box = field.querySelector('.field__error');
        var text = box && box.querySelector('[data-error-text]');
        field.classList.toggle('field--error', !!message);
        if (box && text) {
            text.textContent = message;
            box.hidden = !message;
        }
        fieldInputs(field).forEach(function (input) {
            if (message) input.setAttribute('aria-invalid', 'true');
            else input.removeAttribute('aria-invalid');
            if (!box) return;
            var ids = (input.getAttribute('aria-describedby') || '').split(' ').filter(function (id) { return id && id !== box.id; });
            if (message) ids.push(box.id);
            if (ids.length) input.setAttribute('aria-describedby', ids.join(' '));
            else input.removeAttribute('aria-describedby');
        });
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var fields = form.querySelectorAll('[data-field]');
        var summary = form.querySelector('[data-error-summary]');
        var submit = form.querySelector('[data-submit]');
        var attempted = false;

        form.setAttribute('novalidate', '');

        function validateAll() {
            var problems = [];
            fields.forEach(function (field) {
                var msg = fieldError(field);
                showFieldError(field, msg);
                if (msg) {
                    var target = fieldInputs(field)[0];
                    problems.push({ id: target ? target.id : '', message: msg });
                }
            });
            return problems;
        }

        function renderSummary(problems) {
            if (!summary) return;
            var list = summary.querySelector('ul');
            var title = summary.querySelector('.form__summary-title');
            list.innerHTML = '';
            problems.forEach(function (p) {
                var li = document.createElement('li');
                var a = document.createElement('a');
                a.href = '#' + p.id;
                a.textContent = p.message;
                li.appendChild(a);
                list.appendChild(li);
            });
            title.textContent = problems.length === 1 ? 'Please fix this problem:' : 'Please fix these ' + problems.length + ' problems:';
            summary.hidden = problems.length === 0;
        }

        form.addEventListener('submit', function (event) {
            attempted = true;
            var problems = validateAll();
            if (problems.length) {
                event.preventDefault();
                renderSummary(problems);
                if (summary) summary.focus();
                return;
            }
            if (submit) {
                submit.setAttribute('aria-busy', 'true');
                submit.dataset.label = submit.textContent;
                submit.textContent = 'Sending…';
                // Prevent double submits without disabling (a disabled button isn't sent with the form).
                form.addEventListener('submit', function (e) { e.preventDefault(); });
            }
        });

        // After the first attempt, re-check each field as the visitor fixes it.
        form.addEventListener('change', function (event) {
            if (!attempted) return;
            var field = event.target.closest('[data-field]');
            if (field) showFieldError(field, fieldError(field));
        });
        form.addEventListener('focusout', function (event) {
            if (!attempted) return;
            var field = event.target.closest('[data-field]');
            if (field) showFieldError(field, fieldError(field));
        });

        // Error summary links: focus the field, not just scroll to it.
        if (summary) {
            summary.addEventListener('click', function (event) {
                var link = event.target.closest('a[href^="#"]');
                if (!link) return;
                var target = document.getElementById(link.getAttribute('href').slice(1));
                if (target) { event.preventDefault(); target.focus(); }
            });
            if (!summary.hidden) summary.focus();
        }
    });

    // Restore the button if the page is shown again from the back/forward cache.
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;
        document.querySelectorAll('[data-submit][aria-busy="true"]').forEach(function (b) {
            b.removeAttribute('aria-busy');
            if (b.dataset.label) b.textContent = b.dataset.label;
        });
    });

    // Success panel after redirect: move focus there so screen readers announce it.
    var success = document.querySelector('[data-form-success]');
    if (success) success.focus();

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
