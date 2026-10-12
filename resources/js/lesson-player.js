function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function initLessonPlayer(root) {
    const progressUrl = root.dataset.progressUrl;
    const startPosition = parseInt(root.dataset.startPosition || '0', 10);
    const intervalSec = parseInt(root.dataset.saveInterval || '15', 10);
    const kind = root.dataset.playerKind;
    const resumeBtn = document.querySelector('[data-resume-playback]');

    if (!progressUrl) {
        return;
    }

    let lastSaved = 0;
    let pendingPosition = startPosition;
    let seekTo = null;

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

    if (resumeBtn) {
        resumeBtn.addEventListener('click', (event) => {
            event.preventDefault();
            const at = parseInt(resumeBtn.dataset.resumeAt || String(startPosition), 10);
            if (typeof seekTo === 'function') {
                seekTo(at, true);
            }
        });
    }

    if (kind === 'html5') {
        const video = root.querySelector('video');
        if (!video) {
            return;
        }

        seekTo = (seconds, play) => {
            const apply = () => {
                video.currentTime = Math.max(0, seconds);
                if (play) {
                    video.play().catch(() => {});
                }
            };
            if (video.readyState >= 1) {
                apply();
            } else {
                video.addEventListener('loadedmetadata', apply, { once: true });
            }
        };

        if (startPosition > 0) {
            seekTo(startPosition, false);
        }

        video.addEventListener('timeupdate', () => maybeSave(video.currentTime));
        video.addEventListener('pause', () => save(video.currentTime));
        window.addEventListener('beforeunload', () => save(video.currentTime));
        return;
    }

    if (kind === 'youtube') {
        const videoId = root.dataset.youtubeId;
        if (!videoId || !window.YT?.Player) {
            return;
        }

        const targetId = root.querySelector('[data-youtube-target]')?.id;
        if (!targetId) {
            return;
        }

        let player = null;

        seekTo = (seconds, play) => {
            if (!player || typeof player.seekTo !== 'function') {
                return;
            }
            player.seekTo(Math.max(0, seconds), true);
            if (play && typeof player.playVideo === 'function') {
                player.playVideo();
            }
        };

        player = new window.YT.Player(targetId, {
            videoId,
            playerVars: {
                modestbranding: 1,
                rel: 0,
                start: startPosition > 0 ? startPosition : undefined,
            },
            events: {
                onReady: () => {
                    if (startPosition > 0) {
                        seekTo(startPosition, false);
                    }
                },
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
