function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function initLessonPlayer(root) {
    const progressUrl = root.dataset.progressUrl;
    const startPosition = parseInt(root.dataset.startPosition || '0', 10);
    const intervalSec = parseInt(root.dataset.saveInterval || '15', 10);
    const kind = root.dataset.playerKind;

    if (!progressUrl) {
        return;
    }

    let lastSaved = 0;
    let pendingPosition = startPosition;

    const save = async (position) => {
        pendingPosition = Math.max(0, Math.floor(position));
        try {
            await fetch(progressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ position_seconds: pendingPosition }),
            });
            lastSaved = pendingPosition;
        } catch (e) {
            console.error('Progress save failed', e);
        }
    };

    const maybeSave = (position) => {
        const pos = Math.floor(position);
        if (pos === lastSaved) {
            return;
        }
        if (Math.abs(pos - lastSaved) >= intervalSec || pos < lastSaved) {
            save(pos);
        }
    };

    if (kind === 'html5') {
        const video = root.querySelector('video');
        if (!video) {
            return;
        }
        if (startPosition > 0) {
            video.addEventListener('loadedmetadata', () => {
                video.currentTime = startPosition;
            }, { once: true });
        }
        video.addEventListener('timeupdate', () => maybeSave(video.currentTime));
        video.addEventListener('pause', () => save(video.currentTime));
        window.addEventListener('beforeunload', () => save(video.currentTime));
        return;
    }

    if (kind === 'youtube') {
        const videoId = root.dataset.youtubeId;
        if (!videoId || !window.YT) {
            return;
        }

        const targetId = root.querySelector('[data-youtube-target]')?.id;
        if (!targetId) {
            return;
        }

        const player = new window.YT.Player(targetId, {
            videoId,
            playerVars: {
                modestbranding: 1,
                rel: 0,
                start: startPosition > 0 ? startPosition : undefined,
            },
            events: {
                onStateChange: (event) => {
                    if (event.data === window.YT.PlayerState.PAUSED || event.data === window.YT.PlayerState.ENDED) {
                        save(player.getCurrentTime());
                    }
                },
            },
        });

        setInterval(() => {
            if (player && typeof player.getCurrentTime === 'function') {
                maybeSave(player.getCurrentTime());
            }
        }, intervalSec * 1000);

        window.addEventListener('beforeunload', () => {
            if (player && typeof player.getCurrentTime === 'function') {
                save(player.getCurrentTime());
            }
        });
    }
}

function loadYouTubeApi(callback) {
    if (window.YT && window.YT.Player) {
        callback();
        return;
    }
    const prev = window.onYouTubeIframeAPIReady;
    window.onYouTubeIframeAPIReady = () => {
        if (typeof prev === 'function') {
            prev();
        }
        callback();
    };
    if (!document.querySelector('script[src*="youtube.com/iframe_api"]')) {
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-lesson-player]');
    if (!root) {
        return;
    }

    if (root.dataset.playerKind === 'youtube') {
        loadYouTubeApi(() => initLessonPlayer(root));
    } else {
        initLessonPlayer(root);
    }
});
