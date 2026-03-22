<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\LessonAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseCatalogController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    public function index(Request $request): View
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        $enrolledIds = [];
        if ($request->user()) {
            $enrolledIds = $request->user()->enrollments()->pluck('course_id')->all();
        }

        return view('courses.index', compact('courses', 'enrolledIds'));
    }

    public function show(Request $request, Course $course): View|RedirectResponse
    {
        if (! $course->is_published && ! $request->user()?->isAdmin()) {
            abort(404);
        }

        $course->load(['modules.lessons', 'modules.quizzes']);

        $enrolled = $request->user()
            && $this->lessonAccess->userIsEnrolled($request->user(), $course);

        $completion = $request->user() && $enrolled
            ? $this->lessonAccess->courseCompletionPercent($request->user(), $course)
            : 0;

        $completedLessonIds = collect();
        if ($request->user() && $enrolled) {
            $ids = $course->modules->flatMap(fn ($m) => $m->lessons)->pluck('id');
            $completedLessonIds = $request->user()->lessonProgress()
                ->whereIn('lesson_id', $ids)
                ->where('watched', true)
                ->pluck('lesson_id');
        }

        return view('courses.show', compact('course', 'enrolled', 'completion', 'completedLessonIds'));
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        if (! $course->is_published) {
            abort(404);
        }

        $user->enrollments()->firstOrCreate(['course_id' => $course->id]);

        return redirect()->route('courses.show', $course)->with('status', __('Enrolled.'));
    }
}
