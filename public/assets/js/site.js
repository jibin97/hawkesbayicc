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

    // ---------- Dialog helper (lightbox + bio pop-ups) ----------
    // Built only when <dialog> is supported; otherwise links open the photo and bios expand in place.
    var canDialog = typeof HTMLDialogElement === 'function';

    function makeDialog(className, label) {
        var d = document.createElement('dialog');
        d.className = 'modal ' + className;
        d.setAttribute('aria-label', label);
        d.innerHTML = '<button type="button" class="modal__close" aria-label="Close">' +
            '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>';
        d.querySelector('.modal__close').addEventListener('click', function () { d.close(); });
        // Click on the backdrop (outside the content box) closes.
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
        document.body.appendChild(d);
        return d;
    }
    function lockScroll(dialog) {
        document.documentElement.classList.add('has-modal');
        dialog.addEventListener('close', function () { document.documentElement.classList.remove('has-modal'); }, { once: true });
    }

    // ---------- Gallery lightbox ----------
    var galleries = document.querySelectorAll('[data-gallery]');
    if (canDialog && galleries.length) {
        var lb = makeDialog('lightbox', 'Photo viewer');
        lb.insertAdjacentHTML('beforeend',
            '<figure class="lightbox__figure"><img class="lightbox__img" alt=""><figcaption class="lightbox__caption" aria-live="polite"></figcaption></figure>' +
            '<button type="button" class="lightbox__nav lightbox__nav--prev" aria-label="Previous photo"><svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
            '<button type="button" class="lightbox__nav lightbox__nav--next" aria-label="Next photo"><svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></button>');
        var lbImg = lb.querySelector('.lightbox__img');
        var lbCap = lb.querySelector('.lightbox__caption');
        var items = [], index = 0, title = '', opener = null;

        function show(i) {
            index = (i + items.length) % items.length;
            var a = items[index];
            lbImg.src = a.href;
            lbImg.alt = a.querySelector('img').alt;
            lbImg.width = +a.dataset.w; lbImg.height = +a.dataset.h;
            lbCap.textContent = title + ' · ' + (index + 1) + ' of ' + items.length;
            // Warm the cache for the neighbours so prev/next feel instant.
            [index - 1, index + 1].forEach(function (n) {
                var b = items[(n + items.length) % items.length];
                if (b) { var pre = new Image(); pre.src = b.href; }
            });
        }
        lb.querySelector('.lightbox__nav--prev').addEventListener('click', function () { show(index - 1); });
        lb.querySelector('.lightbox__nav--next').addEventListener('click', function () { show(index + 1); });
        lb.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); show(index - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); show(index + 1); }
        });
        // Swipe left/right on touch screens.
        var startX = null;
        lb.addEventListener('pointerdown', function (e) { startX = e.clientX; });
        lb.addEventListener('pointerup', function (e) {
            if (startX === null) return;
            var dx = e.clientX - startX; startX = null;
            if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
        });
        lb.addEventListener('close', function () { lbImg.removeAttribute('src'); if (opener) opener.focus(); });

        galleries.forEach(function (g) {
            var links = Array.prototype.slice.call(g.querySelectorAll('a[data-lightbox]'));
            links.forEach(function (a, i) {
                a.addEventListener('click', function (e) {
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return; // allow "open in new tab"
                    e.preventDefault();
                    items = links; title = g.dataset.gallery; opener = a;
                    show(i);
                    lb.showModal(); lockScroll(lb);
                    lb.querySelector('.lightbox__nav--next').focus();
                });
            });
        });
    }

    // ---------- Team bios as pop-ups ----------
    var bios = document.querySelectorAll('[data-bio]');
    if (canDialog && bios.length) {
        var bd = makeDialog('bio-modal', 'Biography');
        var bdBody = document.createElement('div');
        bdBody.className = 'bio-modal__body';
        bd.appendChild(bdBody);
        var bioOpener = null;
        bd.addEventListener('close', function () { if (bioOpener) bioOpener.focus(); });

        bios.forEach(function (details) {
            var summary = details.querySelector('summary');
            summary.setAttribute('aria-haspopup', 'dialog');
            summary.addEventListener('click', function (e) {
                e.preventDefault(); // keep <details> closed; show the dialog instead
                var card = details.closest('.team-card');
                var name = card.querySelector('h3').textContent;
                var photo = card.querySelector('img.avatar');
                bdBody.innerHTML = '';
                var head = document.createElement('header');
                head.className = 'bio-modal__head';
                if (photo) {
                    var big = document.createElement('img');
                    big.className = 'bio-modal__photo';
                    big.src = photo.currentSrc || photo.src; big.alt = ''; big.width = 160; big.height = 160;
                    head.appendChild(big);
                }
                var h = document.createElement('h2'); h.id = 'bio-modal-title'; h.textContent = name;
                var wrap = document.createElement('div'); wrap.appendChild(h);
                wrap.appendChild(card.querySelector('.badge').cloneNode(true));
                head.appendChild(wrap);
                bdBody.appendChild(head);
                bdBody.appendChild(details.querySelector('.bio__body').cloneNode(true));
                bd.setAttribute('aria-labelledby', 'bio-modal-title');
                bioOpener = summary;
                bd.showModal(); lockScroll(bd);
                bdBody.scrollTop = 0;
            });
        });
    }

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
