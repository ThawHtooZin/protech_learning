<?php

namespace App\Http\Controllers;

use App\Enums\CourseAccessType;
use App\Enums\PurchaseStatus;
use App\Models\BankAccount;
use App\Models\Course;
use App\Models\CoursePurchase;
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
            ->where(function ($q) {
                $q->where('access_type', CourseAccessType::Public->value)
                    ->orWhere(function ($q2) {
                        $q2->where('access_type', CourseAccessType::Private->value)
                            ->where('is_listed', true);
                    });
            })
            ->with(['modules.lessons'])
            ->orderBy('title')
            ->get();

        $accessIds = [];
        if ($request->user()) {
            $user = $request->user();
            foreach ($courses as $course) {
                if ($this->lessonAccess->canAccessCourse($user, $course)) {
                    $accessIds[] = $course->id;
                }
            }
        }

        return view('courses.index', compact('courses', 'accessIds'));
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        if (! $this->canViewCoursePage($user, $course)) {
            abort(404);
        }

        $course->load(['modules.lessons']);

        $hasAccess = $user && $this->lessonAccess->canAccessCourse($user, $course);
        $pendingPurchase = null;
        if ($user && $course->isPublic() && ! $hasAccess) {
            $pendingPurchase = CoursePurchase::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', PurchaseStatus::Pending)
                ->latest()
                ->first();
        }

        $completion = 0;
        $completedLessonIds = collect();

        if ($user && $hasAccess) {
            $completedLessonIds = $this->lessonAccess->completedLessonIdsForCourse($user, $course);
            $completion = $this->lessonAccess->courseCompletionPercent($user, $course);
        }

        $lessonCount = $course->orderedLessons()->count();
        $totalMinutes = (int) ceil($course->totalDurationSeconds() / 60);
        $bankAccounts = $course->isPublic() && ! $hasAccess
            ? BankAccount::query()->active()->ordered()->get()
            : collect();

        return view('courses.show', compact(
            'course',
            'hasAccess',
            'pendingPurchase',
            'completion',
            'completedLessonIds',
            'lessonCount',
            'totalMinutes',
            'bankAccounts',
        ));
    }

    public function purchase(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $course->is_published || ! $course->isPublic()) {
            abort(404);
        }

        if ($this->lessonAccess->canAccessCourse($user, $course)) {
            return redirect()->route('courses.show', $course);
        }

        $existing = CoursePurchase::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', PurchaseStatus::Pending)
            ->exists();

        if ($existing) {
            return redirect()
                ->route('courses.show', $course)
                ->with('status', __('Purchase pending.'));
        }

        $maxKb = (int) config('lms.payments.slip_max_kb', 5120);
        $disk = (string) config('lms.payments.disk', 'local');

        $validated = $request->validate([
            'slip' => ['required', 'file', 'max:'.$maxKb, 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $path = $validated['slip']->store('course-slips/'.$course->id, $disk);

        CoursePurchase::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'slip_path' => $path,
            'slip_disk' => $disk,
            'status' => PurchaseStatus::Pending,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('status', __('Purchase pending.'));
    }

    private function canViewCoursePage(?\App\Models\User $user, Course $course): bool
    {
        if ($user?->isAdmin()) {
            return true;
        }

        if (! $course->is_published) {
            return false;
        }

        if ($course->isPublic()) {
            return true;
        }

        // Private: listed pages are public; unlisted only for people who already have access
        if ($course->is_listed) {
            return true;
        }

        return $user && $this->lessonAccess->canAccessCourse($user, $course);
    }
}
