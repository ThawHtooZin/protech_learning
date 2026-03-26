<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use App\Models\Quiz;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledInCourse
{
    /**
     * Block lesson/quiz routes unless the user is enrolled in that content’s course.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $courseId = $this->resolveCourseId($request);

        if ($courseId === null) {
            return $next($request);
        }

        if (! $user->enrollments()->where('course_id', $courseId)->exists()) {
            abort(403, __('Enroll in this course to access lessons and quizzes.'));
        }

        return $next($request);
    }

    private function resolveCourseId(Request $request): ?int
    {
        $lesson = $request->route('lesson');
        if ($lesson instanceof Lesson) {
            return $lesson->course?->id;
        }

        $quiz = $request->route('quiz');
        if ($quiz instanceof Quiz) {
            $quiz->loadMissing(['lesson.module.course', 'module.course']);
            if ($quiz->lesson_id && $quiz->lesson) {
                return $quiz->lesson->course?->id;
            }
            if ($quiz->module_id && $quiz->module) {
                return $quiz->module->course_id;
            }
        }

        return null;
    }
}
