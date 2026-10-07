<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseAccessType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Team;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseAdminController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->withCount('modules')
            ->with([
                'teams:id',
                'enrollments:id,course_id,user_id',
            ])
            ->orderByDesc('updated_at')
            ->paginate(20);

        [$grantUsers, $grantTeams] = $this->grantPickerData();

        return view('admin.courses.index', compact('courses', 'grantUsers', 'grantTeams'));
    }

    public function create(): View
    {
        return view('admin.courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedCourse($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('course-covers', 'public');
        }

        $course = Course::query()->create($data);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Course created.'));
    }

    public function edit(Course $course): View
    {
        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
            'modules.lessons' => fn ($q) => $q->orderBy('sort_order'),
            'teams:id',
            'enrollments:id,course_id,user_id',
        ]);

        [$grantUsers, $grantTeams] = $this->grantPickerData();
        $grantedUserIds = $course->enrollments->pluck('user_id')->all();
        $grantedTeamIds = $course->teams->pluck('id')->all();

        return view('admin.courses.edit', compact(
            'course',
            'grantUsers',
            'grantTeams',
            'grantedUserIds',
            'grantedTeamIds',
        ));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $this->validatedCourse($request);

        if ($request->boolean('remove_cover') && $course->cover_path) {
            Storage::disk('public')->delete($course->cover_path);
            $data['cover_path'] = null;
        }

        if ($request->hasFile('cover')) {
            if ($course->cover_path) {
                Storage::disk('public')->delete($course->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('course-covers', 'public');
        }

        $course->update($data);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Saved.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        if ($course->cover_path) {
            Storage::disk('public')->delete($course->cover_path);
        }
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', __('Course “:title” was deleted.', ['title' => $title]));
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, User>, 1: \Illuminate\Support\Collection<int, Team>}
     */
    private function grantPickerData(): array
    {
        $grantUsers = User::query()
            ->with('profile')
            ->where('role', '!=', UserRole::Admin)
            ->whereNotNull('approved_at')
            ->orderBy('email')
            ->get();

        $grantTeams = Team::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return [$grantUsers, $grantTeams];
    }

    /**
     * @return array{title: string, description: ?string, is_published: bool, access_type: string, price_mmk: ?int, is_listed: bool}
     */
    private function validatedCourse(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'remove_cover' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'access_type' => ['required', Rule::enum(CourseAccessType::class)],
            'price_mmk' => [
                'nullable',
                'integer',
                'min:0',
                Rule::requiredIf(fn () => $request->input('access_type') === CourseAccessType::Public->value),
            ],
            'is_listed' => ['sometimes', 'boolean'],
        ]);

        $data['description'] = HtmlSanitizer::clean($data['description'] ?? null);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_listed'] = $request->boolean('is_listed');

        $access = $data['access_type'] instanceof CourseAccessType
            ? $data['access_type']
            : CourseAccessType::from((string) $data['access_type']);
        $data['access_type'] = $access->value;

        if ($access === CourseAccessType::Private) {
            $data['price_mmk'] = null;
        }

        unset($data['cover'], $data['remove_cover']);

        return $data;
    }
}
