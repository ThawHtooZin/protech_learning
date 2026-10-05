<?php

return [
    'video' => [
        'default_driver' => env('DEFAULT_VIDEO_DRIVER', 'youtube'),
        'r2_disk' => env('LMS_VIDEO_R2_DISK', 's3'),
        'signed_url_ttl' => (int) env('LMS_VIDEO_SIGNED_URL_TTL', 3600),
        /*
         * YouTube iframe embed (see YoutubeVideoDriver).
         * - use_nocookie: privacy-enhanced domain; some networks/localhost hit "Sign in to confirm you're not a bot" more often — try false to use youtube.com/embed.
         * - APP_URL is passed as origin= to align the embed with your site (helps in production).
         */
        'youtube' => [
            'use_nocookie' => filter_var(env('YOUTUBE_EMBED_USE_NOCOOKIE', true), FILTER_VALIDATE_BOOL),
        ],
    ],
    'forum' => [
        'max_posts_per_day' => (int) env('LMS_FORUM_MAX_POSTS_PER_DAY', 5),
    ],
    'watch' => [
        'completed_percent' => (int) env('LMS_WATCH_COMPLETED_PERCENT', 90),
        'progress_save_interval_seconds' => (int) env('LMS_PROGRESS_SAVE_INTERVAL', 15),
    ],

    'assignments' => [
        'disk' => env('LMS_ASSIGNMENT_DISK', 'local'),
        'max_files' => 5,
        'max_file_kb' => 20 * 1024,
        // Any file type allowed (py, html, css, js, zip, etc.). Size/count only.
    ],

    'locales' => [
        'available' => ['en', 'my'],
        'labels' => [
            'en' => 'English',
            'my' => 'မြန်မာ',
        ],
    ],
];
