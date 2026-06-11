<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class LessonAccessService
{
    public function learnerQuizzesEnabled(): bool
    {
        return (bool) config('lms.quizzes.learner_enabled', false);
    }

    public function userIsEnrolled(User $user, Course $course): bool
    {
        return $course->enrollments()->where('user_id', $user->id)->exists();
    }

    /**
     * Enrolled learners (and admins) may open any lesson in a published course — no quiz or order gate.
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

    public function canTakeLessonQuiz(User $user, Lesson $lesson): bool
    {
        if (! $this->learnerQuizzesEnabled()) {
            return false;
        }

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
     * Lesson complete = video watched (threshold set via progress endpoint), not quiz.
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

    public function canTakeModuleQuiz(User $user, Quiz $quiz): bool
    {
        if (! $this->learnerQuizzesEnabled()) {
            return false;
        }

        if (! $quiz->module_id || $quiz->lesson_id) {
            return false;
        }

        $module = $quiz->module()->with('course')->first();
        $course = $module?->course;
        if (! $course || ! $course->is_published) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->userIsEnrolled($user, $course);
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

    public function courseAnswerAccuracyPercent(User $user, Course $course): ?float
    {
        if (! $this->learnerQuizzesEnabled()) {
            return null;
        }

        $quizIds = $this->quizIdsForCourse($course);
        if ($quizIds === []) {
            return null;
        }

        $attempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->with('answers')
            ->get();

        $correct = 0;
        $total = 0;
        foreach ($attempts as $attempt) {
            foreach ($attempt->answers as $answer) {
                $total++;
                if ($answer->is_correct) {
                    $correct++;
                }
            }
        }

        if ($total === 0) {
            return null;
        }

        return round(100 * $correct / $total, 1);
    }

    /**
     * @return array<int, int>
     */
    private function quizIdsForCourse(Course $course): array
    {
        $course->loadMissing(['modules.lessons.quizzes', 'modules.quizzes']);

        $ids = [];
        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                foreach ($lesson->quizzes as $q) {
                    $ids[] = $q->id;
                }
            }
            foreach ($module->quizzes as $q) {
                if ($q->lesson_id === null) {
                    $ids[] = $q->id;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}
