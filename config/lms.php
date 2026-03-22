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
];
