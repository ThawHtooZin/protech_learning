<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleAdminController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $max = (int) $course->modules()->max('sort_order');

        $course->modules()->create([
            'title' => $data['title'],
            'sort_order' => $max + 1,
        ]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Module added.'));
    }

    public function destroy(Course $course, Module $module): RedirectResponse
    {
        abort_unless($module->course_id === $course->id, 404);
        $module->delete();

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Module removed.'));
    }
}
