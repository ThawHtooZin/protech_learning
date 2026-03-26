<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\LessonAccessService;
use App\Services\MarkdownRenderer;
use App\Services\ActivityLogger;
use App\Services\Video\VideoDriverFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
        private MarkdownRenderer $markdown,
        private VideoDriverFactory $videoFactory,
        private ActivityLogger $activity,
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
            abort(403, __('Complete earlier lessons and quizzes in order to open this one.'));
        }

        $this->activity->lessonInstant($user, 'lesson_opened', $course, $lesson, [
            'record_progress' => $this->lessonAccess->canRecordProgressForLesson($user, $lesson),
        ]);

        $recordProgress = $this->lessonAccess->canRecordProgressForLesson($user, $lesson);
        $canTakeLessonQuiz = $this->lessonAccess->canTakeLessonQuiz($user, $lesson);

        $lessonQuiz = $lesson->quizzes->first();

        $progress = $user->lessonProgress()
            ->firstOrCreate(
                ['lesson_id' => $lesson->id],
                ['last_position_seconds' => 0]
            );

        if (! $progress->started) {
            $progress->started = true;
            $progress->save();
        }

        $playable = $this->videoFactory->forLesson($lesson)->playable($lesson);

        $docHtml = $lesson->documentation_markdown
            ? $this->markdown->toHtml($lesson->documentation_markdown)
            : '';

        $moduleQuiz = $lesson->module->quizzes->first();
        $moduleQuizPassed = $moduleQuiz
            ? $moduleQuiz->attempts()->where('user_id', $user->id)->where('passed', true)->exists()
            : false;
        $canTakeModuleQuiz = $moduleQuiz
            ? $this->lessonAccess->canTakeModuleQuiz($user, $moduleQuiz)
            : false;

        $course->loadMissing('modules.lessons.quizzes');
        $completedLessonIds = $this->lessonAccess->completedLessonIdsForCourse($user, $course);
        $accessibleLessonIds = $this->lessonAccess->accessibleLessonIds($user, $course);

        $lessonDebugStatus = null;
        if (config('app.debug')) {
            $ordered = $course->orderedLessons();
            $idx = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
            $prevLesson = ($idx !== false && $idx > 0) ? $ordered->get($idx - 1) : null;
            $canPlayVideo = (bool) $playable;
            $canSubmitLessonQuizNow = $canTakeLessonQuiz && $lessonQuiz && ! $progress->quiz_passed;
            $enrolled = $this->lessonAccess->userIsEnrolled($user, $course);

            $watchSummary = $canPlayVideo
                ? 'Video player is shown (playable payload OK).'
                : 'Video blocked: no playable URL/embed (check video driver / lesson video_ref).';
            $quizSummary = ! $lessonQuiz
                ? 'No lesson quiz on this lesson (admin must add one).'
                : ($progress->quiz_passed
                    ? 'Lesson quiz already submitted.'
                    : ($canTakeLessonQuiz
                        ? 'Lesson quiz can be taken.'
                        : 'Lesson quiz locked (complete earlier lessons in order / enroll).'));

            $lessonDebugStatus = [
                'page' => 'lessons.show',
                'userId' => $user->id,
                'isAdmin' => $user->isAdmin(),
                'enrolledInCourse' => $enrolled,
                'course' => [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                ],
                'lesson' => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'sort_order' => $lesson->sort_order,
                    'module_id' => $lesson->module_id,
                ],
                'access' => [
                    'canPlayVideo' => $canPlayVideo,
                    'videoPlaceholderShown' => ! $canPlayVideo,
                    'canTakeLessonQuiz' => (bool) $canTakeLessonQuiz,
                    'canSubmitLessonQuizNow' => (bool) $canSubmitLessonQuizNow,
                    'lessonQuizDone' => (bool) $progress->quiz_passed,
                    'recordProgress' => (bool) $recordProgress,
                ],
                'summary' => [
                    'watch' => $watchSummary,
                    'quiz' => $quizSummary,
                ],
                'orderInCourse' => [
                    'index' => $idx !== false ? $idx : null,
                    'orderedLessonIds' => $ordered->pluck('id')->values()->all(),
                    'previousLessonId' => $prevLesson?->id,
                    'previousLessonQuizPassed' => $prevLesson
                        ? $this->lessonAccess->isLessonCompleteForUser($user, $prevLesson)
                        : null,
                ],
                'flags' => [
                    'recordProgress' => $recordProgress,
                    'canTakeLessonQuiz' => $canTakeLessonQuiz,
                    'canTakeModuleQuiz' => $canTakeModuleQuiz,
                    'moduleQuizPassed' => $moduleQuizPassed,
                ],
                'progressRow' => [
                    'started' => (bool) $progress->started,
                    'watched' => (bool) $progress->watched,
                    'quiz_passed' => (bool) $progress->quiz_passed,
                ],
                'lessonQuizId' => $lessonQuiz?->id,
                'accessibleLessonIds' => $accessibleLessonIds->values()->all(),
                'completedLessonIds' => $completedLessonIds->values()->all(),
                'flashStatus' => session('status'),
            ];
        }

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
            'canTakeLessonQuiz' => $canTakeLessonQuiz,
            'completedLessonIds' => $completedLessonIds,
            'accessibleLessonIds' => $accessibleLessonIds,
            'lessonDebugStatus' => $lessonDebugStatus,
        ]);
    }
}
