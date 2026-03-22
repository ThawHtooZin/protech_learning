<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonCompleteController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
    ) {}

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $this->lessonAccess->canViewLesson($user, $lesson)) {
            abort(403);
        }

        if (! $this->lessonAccess->canRecordProgressForLesson($user, $lesson)) {
            return redirect()->route('lessons.show', $lesson)->with('error', __('Complete earlier lessons in order before marking this one complete.'));
        }

        $data = $request->validate([
            'completed' => ['required', 'in:0,1'],
        ]);

        $completed = $data['completed'] === '1';

        $progress = LessonProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['last_position_seconds' => 0]
        );

        $progress->started = true;
        $progress->watched = $completed;
        $progress->last_checkpoint_at = $completed ? now() : null;
        $progress->save();

        if (! $completed) {
            return redirect()->route('lessons.show', $lesson)->with('status', __('Lesson marked incomplete.'));
        }

        $ordered = $lesson->module->course->orderedLessons();
        $idx = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
        if ($idx !== false && isset($ordered[$idx + 1])) {
            return redirect()
                ->route('lessons.show', $ordered[$idx + 1])
                ->with('status', __('Lesson marked complete. Here’s the next one.'));
        }

        return redirect()
            ->route('lessons.show', $lesson)
            ->with('status', __('Lesson marked complete. That was the last lesson in this course.'));
    }
}
