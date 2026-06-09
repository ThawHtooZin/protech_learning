<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourseStructureReorderService
{
    /**
     * @param  array<int, int>  $moduleIds
     */
    public function reorderModules(Course $course, array $moduleIds): void
    {
        $existing = $course->modules()->pluck('id')->sort()->values()->all();
        $incoming = collect($moduleIds)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

        if ($existing !== $incoming) {
            throw new InvalidArgumentException('Module IDs must match this course exactly.');
        }

        DB::transaction(function () use ($moduleIds) {
            foreach ($moduleIds as $index => $moduleId) {
                Module::query()->whereKey($moduleId)->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * @param  array<int, int>  $lessonIds
     */
    public function reorderLessons(Module $module, array $lessonIds): void
    {
        $existing = $module->lessons()->pluck('id')->sort()->values()->all();
        $incoming = collect($lessonIds)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

        if ($existing !== $incoming) {
            throw new InvalidArgumentException('Lesson IDs must match this module exactly.');
        }

        DB::transaction(function () use ($lessonIds) {
            foreach ($lessonIds as $index => $lessonId) {
                Lesson::query()->whereKey($lessonId)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
