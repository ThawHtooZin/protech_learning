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

        $user = $request->user();
        $enrolled = $user && $this->lessonAccess->userIsEnrolled($user, $course);
        $adminFullAccess = $user && $user->isAdmin();
        $showCourseContent = $enrolled || $adminFullAccess;

        $completion = 0;
        $completedLessonIds = collect();
        $accessibleLessonIds = collect();
        $accuracyPercent = null;

        if ($user && $showCourseContent) {
            $accessibleLessonIds = $this->lessonAccess->accessibleLessonIds($user, $course);
            $completedLessonIds = $this->lessonAccess->completedLessonIdsForCourse($user, $course);
            $completion = $this->lessonAccess->courseCompletionPercent($user, $course);
            if ($completion === 100) {
                $accuracyPercent = $this->lessonAccess->courseAnswerAccuracyPercent($user, $course);
            }
        }

        return view('courses.show', compact(
            'course',
            'enrolled',
            'adminFullAccess',
            'showCourseContent',
            'completion',
            'completedLessonIds',
            'accessibleLessonIds',
            'accuracyPercent',
        ));
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
