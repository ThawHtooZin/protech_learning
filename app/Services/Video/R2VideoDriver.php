<?php

namespace App\Services\Video;

use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * S3-compatible (Cloudflare R2) private objects with signed URLs.
 * Configure AWS_* / R2 env in config/filesystems.php and .env.
 */
class R2VideoDriver implements VideoDriver
{
    public function playable(Lesson $lesson): PlayableVideo
    {
        $disk = config('lms.video.r2_disk', 's3');
        if (! config("filesystems.disks.{$disk}")) {
            throw new RuntimeException('R2/S3 disk not configured. Set LMS_VIDEO_R2_DISK and AWS_* / R2 credentials.');
        }

        $path = $lesson->video_ref;
        $ttl = (int) config('lms.video.signed_url_ttl', 3600);

        $url = Storage::disk($disk)->temporaryUrl($path, now()->addSeconds($ttl));

        return new PlayableVideo('html5', signedUrl: $url, mimeType: 'video/mp4');
    }

    public function validateRef(string $ref): bool
    {
        return $ref !== '';
    }
}
