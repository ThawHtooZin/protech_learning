<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentActivityLog;
use App\Models\Course;
use App\Models\CourseActivityLog;
use App\Models\ForumActivityLog;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Lesson;
use App\Models\LessonActivityLog;
use App\Models\Team;
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

    /**
     * @param  array<string,mixed>  $meta
     */
    public function assignmentInstant(
        User $user,
        string $eventType,
        Team $team,
        ?Assignment $assignment = null,
        array $meta = [],
    ): AssignmentActivityLog {
        return AssignmentActivityLog::query()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'assignment_id' => $assignment?->id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }
}
