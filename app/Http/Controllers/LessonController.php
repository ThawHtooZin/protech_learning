<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonAccessService;
use App\Services\MarkdownRenderer;
use App\Services\Video\VideoDriverFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
        private MarkdownRenderer $markdown,
        private VideoDriverFactory $videoFactory,
    ) {}

    public function show(Request $request, Lesson $lesson): View
    {
        $lesson->load([
            'module.quizzes',
            'module.course.modules.lessons.quizzes',
            'module.course.modules.quizzes',
            'lessonComments' => fn ($q) => $q->with('user.profile')->orderBy('created_at'),
            'quizzes.questions.options',
        ]);

        $course = $lesson->module->course;
        $user = $request->user();

        if (! $user) {
            abort(403, 'Login required.');
        }

        if (! $this->lessonAccess->canViewLesson($user, $lesson)) {
            abort(403);
        }

        $recordProgress = $this->lessonAccess->canRecordProgressForLesson($user, $lesson);

        if ($recordProgress) {
            $progress = $user->lessonProgress()
                ->firstOrCreate(
                    ['lesson_id' => $lesson->id],
                    ['last_position_seconds' => 0]
                );

            if (! $progress->started) {
                $progress->started = true;
                $progress->save();
            }
        } else {
            $progress = LessonProgress::make([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'last_position_seconds' => 0,
                'started' => false,
                'watched' => false,
                'quiz_passed' => false,
            ]);
        }

        $playable = $this->videoFactory->forLesson($lesson)->playable($lesson);

        $docHtml = $lesson->documentation_markdown
            ? $this->markdown->toHtml($lesson->documentation_markdown)
            : '';

        $lessonQuiz = $lesson->quizzes->first();

        $moduleQuiz = $lesson->module->quizzes->first();
        $moduleQuizPassed = $moduleQuiz
            ? $moduleQuiz->attempts()->where('user_id', $user->id)->where('passed', true)->exists()
            : false;
        $canTakeModuleQuiz = $moduleQuiz
            ? $this->lessonAccess->canTakeModuleQuiz($user, $moduleQuiz)
            : false;

        $orderedLessons = $course->orderedLessons();
        $position = $orderedLessons->search(fn (Lesson $l) => $l->id === $lesson->id);
        $nextLesson = ($position !== false && isset($orderedLessons[$position + 1]))
            ? $orderedLessons[$position + 1]
            : null;

        $lessonIdsInCourse = $course->modules->flatMap(fn ($m) => $m->lessons)->pluck('id');
        $completedLessonIds = $user->lessonProgress()
            ->whereIn('lesson_id', $lessonIdsInCourse)
            ->where('watched', true)
            ->pluck('lesson_id');

        return view('lessons.show', [
            'course' => $course,
            'lesson' => $lesson,
            'progress' => $progress,
            'playable' => $playable,
            'docHtml' => $docHtml,
            'lessonQuiz' => $lessonQuiz,
            'moduleQuiz' => $moduleQuiz,
            'moduleQuizPassed' => $moduleQuizPassed,
            'canTakeModuleQuiz' => $canTakeModuleQuiz,
            'recordProgress' => $recordProgress,
            'completedLessonIds' => $completedLessonIds,
            'nextLesson' => $nextLesson,
        ]);
    }
}
