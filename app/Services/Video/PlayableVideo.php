<?php

namespace App\Services\Video;

final class PlayableVideo
{
    public function __construct(
        public readonly string $kind,
        public readonly ?string $embedHtml = null,
        public readonly ?string $signedUrl = null,
        public readonly ?string $mimeType = null,
        public readonly ?string $externalWatchUrl = null,
    ) {}
}
