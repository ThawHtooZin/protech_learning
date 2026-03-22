<?php

namespace App\Services\Video;

use App\Models\Lesson;

class VideoDriverFactory
{
    public function forLesson(Lesson $lesson): VideoDriver
    {
        return match ($lesson->video_driver) {
            'youtube' => app(YoutubeVideoDriver::class),
            'r2' => app(R2VideoDriver::class),
            default => app(YoutubeVideoDriver::class),
        };
    }
}
