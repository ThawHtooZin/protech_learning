<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Services\CourseStructureReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseStructureController extends Controller
{
    public function __construct(
        private CourseStructureReorderService $reorder,
    ) {}

    public function reorderModules(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'module_ids' => ['required', 'array', 'min:1'],
            'module_ids.*' => ['integer', 'distinct'],
        ]);

        $this->reorder->reorderModules($course, $data['module_ids']);

        return response()->json(['ok' => true]);
    }

    public function reorderLessons(Request $request, Course $course, Module $module): JsonResponse
    {
        abort_unless($module->course_id === $course->id, 404);

        $data = $request->validate([
            'lesson_ids' => ['required', 'array', 'min:1'],
            'lesson_ids.*' => ['integer', 'distinct'],
        ]);

        $this->reorder->reorderLessons($module, $data['lesson_ids']);

        return response()->json(['ok' => true]);
    }
}
