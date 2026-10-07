/**
 * Animated dialog helper — open / close, backdrop click-away.
 */
function resolveDialog(target) {
    return typeof target === 'string' ? document.getElementById(target) : target;
}

function openModal(target) {
    const dialog = resolveDialog(target);
    if (!dialog || typeof dialog.showModal !== 'function') return;

    dialog.classList.remove('ui-modal--closing', 'ui-modal--visible');
    if (!dialog.open) {
        dialog.showModal();
    }
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            dialog.classList.add('ui-modal--visible');
        });
    });
}

function closeModal(target) {
    const dialog = resolveDialog(target);
    if (!dialog || !dialog.open) return;
    if (dialog.classList.contains('ui-modal--closing')) return;

    dialog.classList.remove('ui-modal--visible');
    dialog.classList.add('ui-modal--closing');

    let finished = false;
    const finish = () => {
        if (finished) return;
        finished = true;
        dialog.classList.remove('ui-modal--closing');
        if (dialog.open) {
            dialog.close();
        }
        dialog.removeEventListener('transitionend', onEnd);
    };
    const onEnd = (e) => {
        if (e.target === dialog) finish();
    };
    dialog.addEventListener('transitionend', onEnd);
    setTimeout(finish, 240);
}

function bindModal(dialog) {
    if (!dialog || dialog.dataset.modalBound === '1') return;
    dialog.dataset.modalBound = '1';

    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) {
            closeModal(dialog);
        }
    });

    dialog.addEventListener('cancel', (e) => {
        e.preventDefault();
        closeModal(dialog);
    });

    dialog.querySelectorAll('[data-modal-close]').forEach((btn) => {
        btn.addEventListener('click', () => closeModal(dialog));
    });
}

function bootModals() {
    document.querySelectorAll('dialog.ui-modal').forEach(bindModal);
}

window.ProtechModal = { open: openModal, close: closeModal, bind: bindModal };

document.addEventListener('DOMContentLoaded', bootModals);
