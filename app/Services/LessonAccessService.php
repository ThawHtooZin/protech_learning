<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class LessonAccessService
{
    public function userIsEnrolled(User $user, Course $course): bool
    {
        return $course->enrollments()->where('user_id', $user->id)->exists();
    }

    /**
     * Enrolled learners (and admins) may open any lesson in a published course.
     */
    public function canViewLesson(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        if (! $course || ! $course->is_published) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->userIsEnrolled($user, $course);
    }

    /**
     * @return Collection<int, int>
     */
    public function accessibleLessonIds(User $user, Course $course): Collection
    {
        if (! $course->is_published) {
            return collect();
        }

        if ($user->isAdmin()) {
            return $course->orderedLessons()->pluck('id');
        }

        if (! $this->userIsEnrolled($user, $course)) {
            return collect();
        }

        return $course->orderedLessons()->pluck('id');
    }

    public function canRecordProgressForLesson(User $user, Lesson $lesson): bool
    {
        return $this->canViewLesson($user, $lesson);
    }

    public function nextLessonAfter(Lesson $lesson): ?Lesson
    {
        $course = $lesson->course;
        if (! $course) {
            return null;
        }

        $ordered = $course->orderedLessons();
        $idx = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
        if ($idx === false) {
            return null;
        }

        return $ordered->get($idx + 1);
    }

    public function isStepCompleteForUser(User $user, Lesson $lesson): bool
    {
        return $this->isLessonCompleteForUser($user, $lesson);
    }

    /**
     * Lesson complete = video watched (threshold set via progress endpoint).
     */
    public function isLessonCompleteForUser(User $user, Lesson $lesson): bool
    {
        $progress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return $progress && (bool) $progress->watched;
    }

    /**
     * @return Collection<int, int>
     */
    public function completedLessonIdsForCourse(User $user, Course $course): Collection
    {
        $lessonIds = $course->orderedLessons()->pluck('id');
        if ($lessonIds->isEmpty()) {
            return collect();
        }

        return $user->lessonProgress()
            ->whereIn('lesson_id', $lessonIds)
            ->where('watched', true)
            ->pluck('lesson_id');
    }

    public function courseCompletionPercent(User $user, Course $course): int
    {
        $lessons = $course->orderedLessons();
        if ($lessons->isEmpty()) {
            return 0;
        }

        $done = $this->completedLessonIdsForCourse($user, $course)->count();

        return (int) round(100 * $done / $lessons->count());
    }
}
