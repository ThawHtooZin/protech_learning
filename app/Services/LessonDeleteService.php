<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class LessonDeleteService
{
    public function delete(Lesson $lesson): void
    {
        DB::transaction(function () use ($lesson) {
            $moduleId = $lesson->module_id;
            $sortOrder = $lesson->sort_order;

            $lesson->delete();

            Lesson::query()
                ->where('module_id', $moduleId)
                ->where('sort_order', '>', $sortOrder)
                ->decrement('sort_order');
        });
    }
}
