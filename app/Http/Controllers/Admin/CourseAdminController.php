<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseAdminController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->withCount('modules')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        $data['is_published'] = $request->boolean('is_published');

        $course = Course::query()->create($data);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Course created.'));
    }

    public function edit(Course $course): View
    {
        $course->load(['modules.lessons', 'modules.quizzes']);

        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_published' => ['boolean'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $course->update($data);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Saved.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', __('Course “:title” was deleted.', ['title' => $title]));
    }
}
