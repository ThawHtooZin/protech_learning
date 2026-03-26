<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\StudyEvent;
use App\Models\User;

class StudyEventLogger
{
    /**
     * @param  array<string,mixed>  $meta
     */
    public function logInstant(
        User $user,
        string $eventType,
        ?User $actor = null,
        ?Course $course = null,
        ?Lesson $lesson = null,
        ?Quiz $quiz = null,
        array $meta = [],
    ): StudyEvent {
        return StudyEvent::query()->create([
            'user_id' => $user->id,
            'actor_user_id' => $actor?->id,
            'course_id' => $course?->id,
            'lesson_id' => $lesson?->id,
            'quiz_id' => $quiz?->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function start(
        User $user,
        string $eventType,
        ?User $actor = null,
        ?Course $course = null,
        ?Lesson $lesson = null,
        ?Quiz $quiz = null,
        array $meta = [],
    ): StudyEvent {
        return StudyEvent::query()->create([
            'user_id' => $user->id,
            'actor_user_id' => $actor?->id,
            'course_id' => $course?->id,
            'lesson_id' => $lesson?->id,
            'quiz_id' => $quiz?->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'started_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function end(StudyEvent $event, array $meta = []): StudyEvent
    {
        $merged = array_merge($event->meta ?? [], $meta);

        $event->forceFill([
            'occurred_at' => now(),
            'ended_at' => now(),
            'meta' => $merged ?: null,
        ])->save();

        return $event;
    }
}

