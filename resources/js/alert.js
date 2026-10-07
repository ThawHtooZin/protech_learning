/**
 * Clean global alerts — toast + confirm (SweetAlert-like, lighter).
 * Usage: window.ProtechAlert.toast('Saved'), ProtechAlert.confirm({ title, text }).then(...)
 */
function ensureRoot() {
    let root = document.getElementById('protech-alert-root');
    if (root) return root;

    root = document.createElement('div');
    root.id = 'protech-alert-root';
    root.innerHTML = `
        <div data-toast-host class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4"></div>
        <div data-modal-host class="fixed inset-0 z-[110] hidden items-center justify-center bg-zinc-900/40 p-4 backdrop-blur-[2px]"></div>
    `;
    document.body.appendChild(root);
    return root;
}

function toast(message, type = 'info', ms = 4200) {
    const root = ensureRoot();
    const host = root.querySelector('[data-toast-host]');
    const el = document.createElement('div');
    const tone =
        type === 'success'
            ? 'border-emerald-200 bg-white text-emerald-900'
            : type === 'error'
              ? 'border-red-200 bg-white text-red-900'
              : type === 'warning'
                ? 'border-amber-200 bg-white text-amber-950'
                : 'border-zinc-200 bg-white text-zinc-900';

    el.className = `pointer-events-auto w-full max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg shadow-zinc-900/10 ring-1 ring-black/5 transition duration-300 ${tone}`;
    el.setAttribute('role', 'status');
    el.innerHTML = `<div class="flex items-start gap-3">
        <p class="min-w-0 flex-1 leading-relaxed">${escapeHtml(message)}</p>
        <button type="button" class="shrink-0 text-zinc-400 hover:text-zinc-700" aria-label="Close">&times;</button>
    </div>`;

    const close = () => {
        el.classList.add('translate-y-[-6px]', 'opacity-0');
        setTimeout(() => el.remove(), 220);
    };
    el.querySelector('button')?.addEventListener('click', close);
    host.appendChild(el);
    requestAnimationFrame(() => el.classList.add('animate-[fadeIn_.2s_ease-out]'));
    if (ms > 0) setTimeout(close, ms);
}

function confirmDialog({ title = 'Confirm', text = '', confirmText = 'OK', cancelText = 'Cancel', danger = false } = {}) {
    return new Promise((resolve) => {
        const root = ensureRoot();
        const host = root.querySelector('[data-modal-host]');
        host.classList.remove('hidden');
        host.classList.add('flex');
        host.innerHTML = `
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl shadow-zinc-900/10" role="dialog" aria-modal="true">
                <h3 class="text-lg font-semibold text-zinc-900">${escapeHtml(title)}</h3>
                ${text ? `<p class="mt-2 text-sm leading-relaxed text-zinc-600">${escapeHtml(text)}</p>` : ''}
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" data-cancel class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">${escapeHtml(cancelText)}</button>
                    <button type="button" data-ok class="rounded-lg px-4 py-2 text-sm font-semibold text-white ${danger ? 'bg-red-600 hover:bg-red-500' : 'bg-emerald-600 hover:bg-emerald-500'}">${escapeHtml(confirmText)}</button>
                </div>
            </div>
        `;

        const finish = (value) => {
            host.classList.add('hidden');
            host.classList.remove('flex');
            host.innerHTML = '';
            resolve(value);
        };

        host.querySelector('[data-cancel]')?.addEventListener('click', () => finish(false));
        host.querySelector('[data-ok]')?.addEventListener('click', () => finish(true));
        host.addEventListener('click', (e) => {
            if (e.target === host) finish(false);
        }, { once: true });
    });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function bindFlashAndConfirms() {
    const flash = document.getElementById('protech-flash');
    if (flash) {
        const msg = flash.dataset.message;
        const type = flash.dataset.type || 'info';
        if (msg) toast(msg, type);
    }

    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', async (e) => {
            const message = el.getAttribute('data-confirm') || 'Are you sure?';
            e.preventDefault();
            const ok = await confirmDialog({
                title: el.getAttribute('data-confirm-title') || 'Confirm',
                text: message,
                confirmText: el.getAttribute('data-confirm-ok') || 'OK',
                cancelText: el.getAttribute('data-confirm-cancel') || 'Cancel',
                danger: el.hasAttribute('data-confirm-danger'),
            });
            if (!ok) return;
            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
                return;
            }
            const form = el.closest('form');
            if (form) form.submit();
        });
    });
}

window.ProtechAlert = {
    toast,
    success: (m, ms) => toast(m, 'success', ms),
    error: (m, ms) => toast(m, 'error', ms),
    warning: (m, ms) => toast(m, 'warning', ms),
    confirm: confirmDialog,
};

document.addEventListener('DOMContentLoaded', bindFlashAndConfirms);
