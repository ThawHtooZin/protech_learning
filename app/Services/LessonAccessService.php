<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\User;

class LessonAccessService
{
    public function userIsEnrolled(User $user, Course $course): bool
    {
        return $course->enrollments()->where('user_id', $user->id)->exists();
    }

    /**
     * User may open the lesson page (enrolled learner or admin; course published).
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
     * Checkpoints, watch state, and lesson quizzes update only when prior lessons in order are complete.
     * Opening a lesson out of order is allowed; progress stays empty until the path catches up.
     */
    public function canRecordProgressForLesson(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        if (! $course || ! $course->is_published) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $this->userIsEnrolled($user, $course)) {
            return false;
        }

        $ordered = $course->orderedLessons();
        $index = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
        if ($index === false) {
            return false;
        }

        for ($j = 0; $j < $index; $j++) {
            /** @var Lesson $prior */
            $prior = $ordered[$j];
            if (! $this->isStepCompleteForUser($user, $prior)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lesson "step" complete: watched + optional lesson quiz; if last in module, module quiz passed.
     */
    public function isStepCompleteForUser(User $user, Lesson $lesson): bool
    {
        if (! $this->isLessonCompleteForUser($user, $lesson)) {
            return false;
        }

        if ($this->isLastLessonInModule($lesson)) {
            return $this->isModuleCompleteForUser($user, $lesson->module);
        }

        return true;
    }

    public function isLessonCompleteForUser(User $user, Lesson $lesson): bool
    {
        $progress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $progress || ! $progress->watched) {
            return false;
        }

        $lessonQuiz = $lesson->quizzes()->where('lesson_id', $lesson->id)->first();
        if ($lessonQuiz && ! $progress->quiz_passed) {
            return false;
        }

        return true;
    }

    private function isLastLessonInModule(Lesson $lesson): bool
    {
        $max = (int) $lesson->module->lessons()->max('sort_order');

        return (int) $lesson->sort_order === $max;
    }

    public function isModuleCompleteForUser(User $user, \App\Models\Module $module): bool
    {
        foreach ($module->lessons as $lesson) {
            if (! $this->isLessonCompleteForUser($user, $lesson)) {
                return false;
            }
        }

        $moduleQuiz = Quiz::query()
            ->where('module_id', $module->id)
            ->whereNull('lesson_id')
            ->first();

        if ($moduleQuiz) {
            $passed = $moduleQuiz->attempts()
                ->where('user_id', $user->id)
                ->where('passed', true)
                ->exists();

            return $passed;
        }

        return true;
    }

    public function canTakeModuleQuiz(User $user, Quiz $quiz): bool
    {
        if (! $quiz->module_id || $quiz->lesson_id) {
            return false;
        }

        $module = $quiz->module()->with(['lessons', 'course'])->first();
        if (! $module) {
            return false;
        }

        $course = $module->course;
        if (! $course || ! $this->userIsEnrolled($user, $course)) {
            return false;
        }

        foreach ($module->lessons as $lesson) {
            if (! $this->isLessonCompleteForUser($user, $lesson)) {
                return false;
            }
        }

        return true;
    }

    public function courseCompletionPercent(User $user, Course $course): int
    {
        $lessons = $course->orderedLessons();
        if ($lessons->isEmpty()) {
            return 0;
        }

        $done = 0;
        foreach ($lessons as $lesson) {
            if ($this->isStepCompleteForUser($user, $lesson)) {
                $done++;
            }
        }

        return (int) round(100 * $done / $lessons->count());
    }
}
