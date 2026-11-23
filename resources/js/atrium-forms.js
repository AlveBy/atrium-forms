/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 *
 * Sends the forms of alveby/atrium-forms without reloading the page and opens
 * them in a dialog. No dependencies; the page includes it once, deferred:
 *
 *   <form data-atrium-form …>                 sent with fetch, errors at the fields
 *   <a data-atrium-form-open="contact" …>      opens <dialog data-atrium-dialog="contact">
 *
 * The form says how it went with events: atrium-form:sent ({message}) and
 * atrium-form:failed ({status, errors}).
 */
(() => {
    if (window.AtriumForms) {
        return;
    }

    let opener = null;

    function cookie(name) {
        const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

        return match ? decodeURIComponent(match[1]) : null;
    }

    function describe(control, id, add) {
        const ids = (control.getAttribute('aria-describedby') || '').split(' ').filter((item) => item && item !== id);

        if (add) {
            ids.push(id);
        }

        ids.length ? control.setAttribute('aria-describedby', ids.join(' ')) : control.removeAttribute('aria-describedby');
    }

    function clear(form) {
        form.querySelectorAll('[data-atrium-error]').forEach((node) => {
            form.querySelectorAll(`[aria-describedby~="${node.id}"]`).forEach((control) => describe(control, node.id, false));
            node.remove();
        });
        form.querySelectorAll('[aria-invalid]').forEach((control) => control.removeAttribute('aria-invalid'));
        form.querySelectorAll('[data-atrium-sent], [data-atrium-alert]').forEach((node) => {
            node.textContent = '';
        });
    }

    // The message under its field ("fields.email") or by the captcha
    // ("captcha"); the first invalid control is returned.
    function fieldError(form, key, message) {
        const field = key.startsWith('fields.') ? key.slice(7) : null;
        const box = field === null
            ? [...form.querySelectorAll('[data-error-for]')].find((node) => node.dataset.errorFor === key)
            : [...form.querySelectorAll('[data-field]')].find((node) => node.dataset.field === field);

        const controls = box ? box.querySelectorAll('input:not([type=hidden]), select, textarea') : [];

        if (!controls.length) {
            return null;
        }

        const id = `${form.id}-${field ?? key}-error`;
        const error = document.createElement('p');
        error.className = 'atrium-form__error';
        error.id = id;
        error.dataset.atriumError = '';
        error.textContent = message;
        box.append(error);

        controls.forEach((control) => control.setAttribute('aria-invalid', 'true'));
        describe(box.querySelector('fieldset') || controls[0], id, true);

        return controls[0];
    }

    // A message of the whole form: anti-spam, too many tries, the network.
    function say(form, message) {
        const node = form.querySelector('[data-atrium-alert]');

        if (node) {
            node.textContent = [node.textContent, message].filter(Boolean).join(' ');
        }

        return node;
    }

    // A captcha is used up by every answer: the server sends a new one.
    function captcha(form, html) {
        const box = form.querySelector('.atrium-captcha');

        if (window.grecaptcha) {
            window.grecaptcha.reset();
        } else if (box && html) {
            box.innerHTML = html;
        }
    }

    function busy(form, on) {
        form.toggleAttribute('data-atrium-busy', on);
        form.setAttribute('aria-busy', on ? 'true' : 'false');
        form.querySelectorAll('[type=submit]').forEach((button) => {
            button.disabled = on;
        });
    }

    async function submit(form) {
        if (form.hasAttribute('data-atrium-busy')) {
            return;
        }

        busy(form, true);
        clear(form);

        const body = new FormData(form);
        const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        const xsrf = cookie('XSRF-TOKEN');

        // A page kept in a cache carries an old _token, and Laravel trusts
        // the field before the header: the fresh cookie goes alone.
        if (xsrf) {
            body.delete('_token');
            headers['X-XSRF-TOKEN'] = xsrf;
        }

        let status = 0;
        let data = {};

        try {
            const response = await fetch(form.getAttribute('action'), { method: 'POST', body, headers, credentials: 'same-origin' });
            status = response.status;
            data = await response.json().catch(() => ({}));
        } catch {
            status = 0;
        }

        busy(form, false);
        captcha(form, data.captcha);

        if (status >= 200 && status < 300) {
            form.reset();
            const sent = form.querySelector('[data-atrium-sent]');

            if (sent) {
                sent.textContent = data.message || '';
                sent.focus();
            }

            form.dispatchEvent(new CustomEvent('atrium-form:sent', { bubbles: true, detail: { message: data.message } }));

            return;
        }

        const errors = status === 422 && data.errors ? data.errors : {};
        let first = null;

        for (const [key, messages] of Object.entries(errors)) {
            const message = [].concat(messages)[0];
            const control = fieldError(form, key, message);

            if (control) {
                first ??= control;
            } else {
                say(form, message);
            }
        }

        if (!Object.keys(errors).length) {
            say(form, status === 429 && data.message ? data.message : form.dataset.atriumFailed);
        }

        (first || form.querySelector('[data-atrium-alert]'))?.focus();
        form.dispatchEvent(new CustomEvent('atrium-form:failed', { bubbles: true, detail: { status, errors } }));
    }

    function dialogOf(code) {
        return [...document.querySelectorAll('dialog[data-atrium-dialog]')].find((node) => node.dataset.atriumDialog === code);
    }

    function focusable(dialog) {
        return [...dialog.querySelectorAll('a[href], button, input, select, textarea, [tabindex]')]
            .filter((node) => !node.disabled && node.tabIndex >= 0 && node.type !== 'hidden' && node.getClientRects().length);
    }

    function open(code, from = null) {
        const dialog = dialogOf(code);

        if (!dialog) {
            return false;
        }

        opener = from;
        opener?.setAttribute('aria-expanded', 'true');
        dialog.showModal();
        (focusable(dialog.querySelector('form') || dialog)[0] || dialog).focus();

        return true;
    }

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-atrium-form]');

        if (form) {
            event.preventDefault();
            submit(form);
        }
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-atrium-form-open]');

        if (trigger && open(trigger.dataset.atriumFormOpen, trigger)) {
            event.preventDefault();

            return;
        }

        const dialog = event.target.closest('dialog[data-atrium-dialog]');

        // The close button, or a click on the dimmed page around the window.
        if (dialog && (event.target.closest('[data-atrium-form-close]') || event.target === dialog)) {
            dialog.close();
        }
    });

    // Tab and Shift+Tab go round the window and never leave it.
    document.addEventListener('keydown', (event) => {
        const dialog = document.querySelector('dialog[data-atrium-dialog][open]');

        if (event.key !== 'Tab' || !dialog) {
            return;
        }

        const items = focusable(dialog);
        const index = items.indexOf(document.activeElement);
        const next = event.shiftKey ? (index <= 0 ? items.length - 1 : index - 1) : (index === items.length - 1 ? 0 : index + 1);

        event.preventDefault();
        items[next]?.focus();
    });

    document.addEventListener('close', (event) => {
        if (event.target.matches?.('dialog[data-atrium-dialog]') && opener) {
            opener.setAttribute('aria-expanded', 'false');
            opener.focus();
            opener = null;
        }
    }, true);

    window.AtriumForms = { open, submit };
})();
