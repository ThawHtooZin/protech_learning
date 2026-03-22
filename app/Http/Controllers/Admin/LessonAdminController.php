<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonAdminController extends Controller
{
    public function create(Course $course, Module $module): View
    {
        abort_unless($module->course_id === $course->id, 404);

        return view('admin.lessons.create', compact('course', 'module'));
    }

    public function store(Request $request, Course $course, Module $module): RedirectResponse
    {
        abort_unless($module->course_id === $course->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video_driver' => ['required', 'in:youtube,r2'],
            'video_ref' => ['required', 'string', 'max:500'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'documentation_markdown' => ['nullable', 'string'],
        ]);

        $max = (int) $module->lessons()->max('sort_order');

        $module->lessons()->create([
            'title' => $data['title'],
            'sort_order' => $max + 1,
            'video_driver' => $data['video_driver'],
            'video_ref' => $data['video_ref'],
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'documentation_markdown' => $data['documentation_markdown'] ?? null,
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson created.'));
    }

    public function edit(Course $course, Module $module, Lesson $lesson): View
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        return view('admin.lessons.edit', compact('course', 'module', 'lesson'));
    }

    public function update(Request $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video_driver' => ['required', 'in:youtube,r2'],
            'video_ref' => ['required', 'string', 'max:500'],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],
            'documentation_markdown' => ['nullable', 'string'],
        ]);

        $lesson->update($data);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson saved.'));
    }
}
