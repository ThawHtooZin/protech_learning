<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseActivityLog;
use App\Models\ForumActivityLog;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Lesson;
use App\Models\LessonActivityLog;
use App\Models\Quiz;
use App\Models\QuizActivityLog;
use App\Models\QuizAttempt;
use App\Models\User;

class ActivityLogger
{
    /**
     * @param  array<string,mixed>  $meta
     */
    public function lessonInstant(User $user, string $eventType, Course $course, Lesson $lesson, array $meta = []): LessonActivityLog
    {
        return LessonActivityLog::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function quizStart(User $user, string $eventType, Course $course, ?Lesson $lesson, Quiz $quiz, array $meta = []): QuizActivityLog
    {
        return QuizActivityLog::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson?->id,
            'quiz_id' => $quiz->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'started_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function quizEnd(QuizActivityLog $log, QuizAttempt $attempt, array $meta = []): QuizActivityLog
    {
        $startedAt = $log->started_at;
        $durationSeconds = $startedAt ? max(0, now()->diffInSeconds($startedAt)) : null;
        $merged = array_merge($log->meta ?? [], $meta);

        $log->forceFill([
            'occurred_at' => now(),
            'ended_at' => now(),
            'attempt_id' => $attempt->id,
            'score_percent' => $attempt->score_percent,
            'passed' => $attempt->passed,
            'duration_seconds' => $durationSeconds,
            'meta' => $merged ?: null,
        ])->save();

        QuizActivityLog::query()->create([
            'user_id' => $log->user_id,
            'course_id' => $log->course_id,
            'lesson_id' => $log->lesson_id,
            'quiz_id' => $log->quiz_id,
            'event_type' => 'quiz_submitted',
            'occurred_at' => now(),
            'started_at' => $log->started_at,
            'ended_at' => $log->ended_at,
            'attempt_id' => $attempt->id,
            'score_percent' => $attempt->score_percent,
            'passed' => $attempt->passed,
            'duration_seconds' => $durationSeconds,
            'meta' => array_merge($merged ?: [], ['start_log_id' => $log->id]),
        ]);

        return $log;
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function forumInstant(
        User $user,
        string $eventType,
        ForumCategory $category,
        ForumThread $thread,
        ?ForumPost $post = null,
        ?ForumPost $parentPost = null,
        array $meta = [],
    ): ForumActivityLog {
        $meta = array_merge([
            'forum_category_name' => $category->name,
            'thread_title' => $thread->title,
        ], $meta);

        return ForumActivityLog::query()->create([
            'user_id' => $user->id,
            'forum_category_id' => $category->id,
            'forum_thread_id' => $thread->id,
            'forum_post_id' => $post?->id,
            'parent_post_id' => $parentPost?->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function courseInstant(User $user, string $eventType, Course $course, array $meta = []): CourseActivityLog
    {
        return CourseActivityLog::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }
}

