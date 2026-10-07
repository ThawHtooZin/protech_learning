<?php

namespace App\Http\Middleware;

use App\Models\Lesson;
use App\Services\LessonAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledInCourse
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    /**
     * Block lesson routes unless the user can access that lesson’s course.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $lesson = $request->route('lesson');
        if (! $lesson instanceof Lesson) {
            return $next($request);
        }

        $course = $lesson->course;
        if (! $course) {
            abort(404);
        }

        if (! $this->lessonAccess->canAccessCourse($user, $course)) {
            abort(403);
        }

        return $next($request);
    }
}
