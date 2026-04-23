/**
 * Modal + toast helpers shared across admin pages.
 *
 * Modal usage:
 *   const m = UI.modal({
 *     title: 'Edit project',
 *     body: '<form>...</form>',
 *     size: 'lg',
 *     onOpen: (root) => { ... wire the form ... },
 *     footer: [
 *       { label: 'Cancel', variant: 'ghost', close: true },
 *       { label: 'Save',   variant: 'primary', onClick: () => {...} },
 *     ],
 *   });
 *   m.open();  // later: m.close();
 *
 * Toast usage: UI.toast('Saved', 'success');
 * Confirm: await UI.confirm('Delete this?', 'Cannot be undone.');
 */
(function () {
    function modal({ title = '', body = '', size, onOpen, footer = [] } = {}) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        const sizeClass = size === 'lg' ? 'modal-lg' : size === 'sm' ? 'modal-sm' : '';
        backdrop.innerHTML = `
            <div class="modal ${sizeClass}">
                <div class="modal-header">
                    <h3>${escapeHtml(title)}</h3>
                    <button class="btn btn-ghost btn-icon" data-close aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="modal-body"></div>
                ${footer.length ? '<div class="modal-footer"></div>' : ''}
            </div>
        `;

        const bodyEl = backdrop.querySelector('.modal-body');
        if (body instanceof Node) bodyEl.appendChild(body);
        else bodyEl.innerHTML = body;

        const footerEl = backdrop.querySelector('.modal-footer');
        if (footerEl) {
            footer.forEach(btn => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = `btn btn-${btn.variant || 'ghost'}`;
                b.innerHTML = escapeHtml(btn.label);
                b.addEventListener('click', async () => {
                    if (btn.onClick) {
                        b.disabled = true;
                        const origText = b.innerHTML;
                        b.innerHTML = '<span class="spinner"></span>';
                        try {
                            const shouldClose = await btn.onClick();
                            if (shouldClose !== false) close();
                        } finally {
                            b.disabled = false;
                            b.innerHTML = origText;
                        }
                    }
                    if (btn.close) close();
                });
                footerEl.appendChild(b);
            });
        }

        const close = () => {
            backdrop.classList.remove('is-open');
            setTimeout(() => backdrop.remove(), 150);
        };
        const open = () => {
            document.body.appendChild(backdrop);
            requestAnimationFrame(() => backdrop.classList.add('is-open'));
            if (onOpen) onOpen(bodyEl);
        };

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) close();
            if (e.target.closest('[data-close]')) close();
        });
        document.addEventListener('keydown', function escHandler(e) {
            if (!document.body.contains(backdrop)) {
                document.removeEventListener('keydown', escHandler);
                return;
            }
            if (e.key === 'Escape') close();
        });

        return { open, close, root: backdrop, bodyEl };
    }

    function toast(message, variant = 'default', timeout = 3500) {
        let host = document.querySelector('.toast-host');
        if (!host) {
            host = document.createElement('div');
            host.className = 'toast-host';
            document.body.appendChild(host);
        }
        const t = document.createElement('div');
        t.className = `toast ${variant}`;
        t.textContent = message;
        host.appendChild(t);
        setTimeout(() => {
            t.style.opacity = '0';
            t.style.transform = 'translateX(20px)';
            t.style.transition = 'all .2s';
            setTimeout(() => t.remove(), 200);
        }, timeout);
    }

    function confirm(title, message = '', { confirmLabel = 'Yes', cancelLabel = 'Cancel', variant = 'danger' } = {}) {
        return new Promise((resolve) => {
            const m = modal({
                title,
                size: 'sm',
                body: `<p style="margin:0; color: var(--text-mute);">${escapeHtml(message)}</p>`,
                footer: [
                    { label: cancelLabel, variant: 'ghost', onClick: () => { resolve(false); } },
                    { label: confirmLabel, variant, onClick: () => { resolve(true); } },
                ],
            });
            m.open();
        });
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]);
    }

    function formToObject(form) {
        const data = {};
        new FormData(form).forEach((v, k) => {
            if (k in data) {
                if (!Array.isArray(data[k])) data[k] = [data[k]];
                data[k].push(v);
            } else data[k] = v;
        });
        return data;
    }

    function applyValidationErrors(form, errors) {
        form.querySelectorAll('.error').forEach(n => n.remove());
        form.querySelectorAll('.is-invalid').forEach(n => n.classList.remove('is-invalid'));
        for (const [field, msgs] of Object.entries(errors || {})) {
            const el = form.querySelector(`[name="${field}"]`);
            if (!el) continue;
            el.classList.add('is-invalid');
            const err = document.createElement('div');
            err.className = 'error';
            err.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
            el.parentNode.appendChild(err);
        }
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        try { return new Date(iso).toLocaleString(); } catch (e) { return iso; }
    }

    window.UI = { modal, toast, confirm, formToObject, applyValidationErrors, fmtDate };
})();
