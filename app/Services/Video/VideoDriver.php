<?php

namespace App\Services\Video;

use App\Models\Lesson;

interface VideoDriver
{
    public function playable(Lesson $lesson): PlayableVideo;

    public function validateRef(string $ref): bool;
}
