<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledInCourse
{
    /**
     * Block lesson routes unless the user is enrolled in that content’s course.
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
            abort(403, __('Enroll in this course to access lessons.'));
        }

        return $next($request);
    }

    private function resolveCourseId(Request $request): ?int
    {
        $lesson = $request->route('lesson');
        if ($lesson instanceof Lesson) {
            return $lesson->course?->id;
        }

        return null;
    }
}
