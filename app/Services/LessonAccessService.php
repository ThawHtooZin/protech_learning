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
    public function userIsEnrolled(User $user, Course $course): bool
    {
        return $course->enrollments()->where('user_id', $user->id)->exists();
    }

    /**
     * User may open the lesson page (published course). Learners need enrollment + prior steps; admins may open any lesson.
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

        if (! $this->userIsEnrolled($user, $course)) {
            return false;
        }

        return $this->priorLessonsStepComplete($user, $lesson);
    }

    /**
     * Lesson IDs the user may open (sequential: complete prior steps first; no skipping).
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function accessibleLessonIds(User $user, Course $course): \Illuminate\Support\Collection
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

        $ids = collect();
        foreach ($course->orderedLessons() as $lesson) {
            if ($this->priorLessonsStepComplete($user, $lesson)) {
                $ids->push($lesson->id);
            }
        }

        return $ids;
    }

    /**
     * Course order: modules by sort_order, then lessons by sort_order (see Course::orderedLessons()).
     * First lesson in that order may always be opened (enrolled). Each later lesson requires every
     * earlier lesson’s lesson quiz submitted — recorded in lesson_progress.quiz_passed (no module recap gate).
     */
    private function priorLessonsStepComplete(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        if (! $course) {
            return false;
        }

        $ordered = $course->orderedLessons();
        $index = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
        if ($index === false) {
            return false;
        }

        for ($j = 0; $j < $index; $j++) {
            if (! $this->isLessonCompleteForUser($user, $ordered[$j])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Progress (quizzes, outline): enrolled learners follow order; admins may record progress on any published lesson.
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
            if (! $this->isLessonCompleteForUser($user, $prior)) {
                return false;
            }
        }

        return true;
    }

    /**
     * May start/submit this lesson’s quiz (enrolled and prior steps complete — same as progress).
     */
    public function canTakeLessonQuiz(User $user, Lesson $lesson): bool
    {
        return $this->canRecordProgressForLesson($user, $lesson);
    }

    /** Next lesson in course order, or null if this is the last lesson. */
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

    /**
     * Per-lesson progress for gating and completion %: lesson quiz submitted (lesson_progress.quiz_passed).
     */
    public function isStepCompleteForUser(User $user, Lesson $lesson): bool
    {
        return $this->isLessonCompleteForUser($user, $lesson);
    }

    /**
     * “Completed” = row in lesson_progress with quiz_passed for this lesson’s lesson quiz (the check record).
     */
    public function isLessonCompleteForUser(User $user, Lesson $lesson): bool
    {
        $progress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $progress) {
            return false;
        }

        $lessonQuiz = $lesson->quizzes()->where('lesson_id', $lesson->id)->first();

        if (! $lessonQuiz) {
            return false;
        }

        return (bool) $progress->quiz_passed;
    }

    /**
     * Outline checkmarks: lesson quiz submitted (lessons without a quiz never show complete).
     *
     * @return Collection<int, int>
     */
    public function completedLessonIdsForCourse(User $user, Course $course): Collection
    {
        $course->loadMissing('modules.lessons.quizzes');

        $lessons = $course->modules->flatMap(fn ($m) => $m->lessons);
        $lessonIds = $lessons->pluck('id');
        if ($lessonIds->isEmpty()) {
            return collect();
        }

        $progressByLesson = $user->lessonProgress()
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        return $lessons->filter(function (Lesson $lesson) use ($progressByLesson) {
            if ($lesson->quizzes->isEmpty()) {
                return false;
            }
            $p = $progressByLesson->get($lesson->id);

            return $p && $p->quiz_passed;
        })->pluck('id');
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
        if (! $course || ! $course->is_published) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $this->userIsEnrolled($user, $course)) {
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
            if ($this->isLessonCompleteForUser($user, $lesson)) {
                $done++;
            }
        }

        return (int) round(100 * $done / $lessons->count());
    }

    /**
     * Share of correct answers across all quiz attempts in this course (KD-style: correct / total answered).
     */
    public function courseAnswerAccuracyPercent(User $user, Course $course): ?float
    {
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
