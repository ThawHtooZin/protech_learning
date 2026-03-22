<?php

namespace App\Services\Video;

use App\Models\Lesson;

class YoutubeVideoDriver implements VideoDriver
{
    public function playable(Lesson $lesson): PlayableVideo
    {
        $id = $this->normalizeToVideoId($lesson->video_ref);
        $watchUrl = 'https://www.youtube.com/watch?v='.rawurlencode($id);

        $host = config('lms.video.youtube.use_nocookie', true)
            ? 'www.youtube-nocookie.com'
            : 'www.youtube.com';

        $embedSrc = sprintf(
            'https://%s/embed/%s?%s',
            $host,
            rawurlencode($id),
            http_build_query($this->embedQueryParams())
        );

        $embed = sprintf(
            '<iframe class="aspect-video w-full rounded-lg" src="%s" title="Lesson video" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>',
            e($embedSrc)
        );

        return new PlayableVideo('embed', embedHtml: $embed, externalWatchUrl: $watchUrl);
    }

    public function validateRef(string $ref): bool
    {
        return $this->normalizeToVideoId($ref) !== '';
    }

    /**
     * @return array<string, string>
     */
    private function embedQueryParams(): array
    {
        $params = [
            'modestbranding' => '1',
            'rel' => '0',
            'enablejsapi' => '1',
        ];

        $origin = $this->embedOrigin();
        if ($origin !== null && $origin !== '') {
            $params['origin'] = $origin;
        }

        return $params;
    }

    private function embedOrigin(): ?string
    {
        $url = config('app.url');
        if (! is_string($url) || $url === '') {
            return null;
        }

        return rtrim($url, '/');
    }

    private function normalizeToVideoId(string $ref): string
    {
        $ref = trim($ref);
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})~', $ref, $m)) {
            return $m[1];
        }
        if (preg_match('~^([a-zA-Z0-9_-]{11})$~', $ref)) {
            return $ref;
        }

        return '';
    }
}
