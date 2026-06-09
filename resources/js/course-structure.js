import Sortable from 'sortablejs';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function putJson(url, body) {
    const res = await fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    });

    if (!res.ok) {
        throw new Error(`Reorder failed (${res.status})`);
    }
}

function initModuleSortable(root) {
    const list = root.querySelector('[data-module-sortable]');
    const url = root.dataset.modulesReorderUrl;
    if (!list || !url) {
        return;
    }

    Sortable.create(list, {
        handle: '[data-drag-handle]',
        animation: 150,
        ghostClass: 'opacity-40',
        onEnd: async () => {
            const ids = [...list.querySelectorAll('[data-module-id]')].map((el) =>
                parseInt(el.dataset.moduleId, 10)
            );
            try {
                await putJson(url, { module_ids: ids });
            } catch (e) {
                console.error(e);
                window.location.reload();
            }
        },
    });
}

function initLessonSortables(root) {
    root.querySelectorAll('[data-lesson-sortable]').forEach((list) => {
        const url = list.dataset.lessonsReorderUrl;
        if (!url) {
            return;
        }

        Sortable.create(list, {
            handle: '[data-drag-handle]',
            animation: 150,
            ghostClass: 'opacity-40',
            onEnd: async () => {
                const ids = [...list.querySelectorAll('[data-lesson-id]')].map((el) =>
                    parseInt(el.dataset.lessonId, 10)
                );
                try {
                    await putJson(url, { lesson_ids: ids });
                } catch (e) {
                    console.error(e);
                    window.location.reload();
                }
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-course-structure]');
    if (!root) {
        return;
    }
    initModuleSortable(root);
    initLessonSortables(root);
});
