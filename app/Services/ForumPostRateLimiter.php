<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ForumPostRateLimiter
{
    public function assertWithinLimit(User $user): void
    {
        $max = (int) config('lms.forum.max_posts_per_day', 5);
        $start = Carbon::now()->startOfDay();
        $count = ForumPost::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $start)
            ->count();

        if ($count >= $max) {
            throw ValidationException::withMessages([
                'body' => __('You have reached the daily limit of :n forum posts.', ['n' => $max]),
            ]);
        }
    }
}
