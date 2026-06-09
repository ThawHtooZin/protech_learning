<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $this->lessonAccess->canRecordProgressForLesson($user, $lesson)) {
            abort(403);
        }

        $data = $request->validate([
            'position_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $position = $data['position_seconds'];
        $duration = (int) ($lesson->duration_seconds ?? 0);
        $threshold = (int) config('lms.watch.completed_percent', 90);
        $watched = $duration > 0 && $position >= (int) floor($duration * $threshold / 100);

        $progress = LessonProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['last_position_seconds' => 0]
        );

        $progress->started = true;
        $progress->last_position_seconds = $position;
        if ($watched) {
            $progress->watched = true;
        }
        $progress->last_checkpoint_at = now();
        $progress->save();

        return response()->json([
            'ok' => true,
            'position_seconds' => $progress->last_position_seconds,
            'watched' => (bool) $progress->watched,
        ]);
    }
}
