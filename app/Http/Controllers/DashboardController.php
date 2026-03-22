<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\LessonAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $courses = Course::query()
            ->where('is_published', true)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->withCount('modules')
            ->orderBy('title')
            ->get();

        $progress = [];
        foreach ($courses as $course) {
            $progress[$course->id] = $this->lessonAccess->courseCompletionPercent($user, $course);
        }

        return view('dashboard', compact('courses', 'progress'));
    }
}
