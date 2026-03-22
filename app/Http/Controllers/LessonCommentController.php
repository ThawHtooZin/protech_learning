<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonComment;
use App\Services\LessonAccessService;
use App\Services\MentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonCommentController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
        private MentionService $mentions,
    ) {}

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        if (! $this->lessonAccess->canViewLesson($user, $lesson)) {
            abort(403);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
            'parent_id' => ['nullable', 'integer', 'exists:lesson_comments,id'],
        ]);

        $comment = LessonComment::query()->create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        $this->mentions->notifyMentionedUsers($user, $data['body'], [
            'message' => __('You were mentioned in a lesson comment.'),
            'base_url' => route('lessons.show', $lesson),
            'source_type' => 'lesson_comment',
            'source_id' => $comment->id,
        ]);

        return redirect()->route('lessons.show', $lesson)->with('status', __('Comment posted.'));
    }
}
