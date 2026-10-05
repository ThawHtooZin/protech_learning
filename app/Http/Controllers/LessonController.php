<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\ActivityLogger;
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
        private ActivityLogger $activity,
    ) {}

    public function show(Request $request, Lesson $lesson): View
    {
        $lesson->load([
            'module.course.modules.lessons',
            'lessonComments' => fn ($q) => $q->with('user.profile')->orderBy('created_at'),
        ]);

        $course = $lesson->module->course;
        $user = $request->user();

        if (! $user) {
            abort(403, 'Login required.');
        }

        if (! $this->lessonAccess->canViewLesson($user, $lesson)) {
            abort(403, __('Enroll in this course to access lessons.'));
        }

        $this->activity->lessonInstant($user, 'lesson_opened', $course, $lesson, [
            'record_progress' => $this->lessonAccess->canRecordProgressForLesson($user, $lesson),
        ]);

        $recordProgress = $this->lessonAccess->canRecordProgressForLesson($user, $lesson);

        $progress = $user->lessonProgress()
            ->firstOrCreate(
                ['lesson_id' => $lesson->id],
                ['last_position_seconds' => 0]
            );

        if (! $progress->started) {
            $progress->started = true;
            $progress->save();
        }

        $driver = $this->videoFactory->forLesson($lesson);
        $playable = $driver->playable($lesson);
        $playerKind = null;
        $youtubeVideoId = null;
        if ($lesson->video_driver === 'youtube' && $playable) {
            $playerKind = 'youtube';
            $youtubeVideoId = method_exists($driver, 'videoIdFromLesson')
                ? $driver->videoIdFromLesson($lesson)
                : null;
        } elseif ($lesson->video_driver === 'r2' && $playable?->signedUrl) {
            $playerKind = 'html5';
        }

        $docHtml = $lesson->documentation_markdown
            ? $this->markdown->toHtml($lesson->documentation_markdown)
            : '';

        $completedLessonIds = $this->lessonAccess->completedLessonIdsForCourse($user, $course);
        $accessibleLessonIds = $this->lessonAccess->accessibleLessonIds($user, $course);

        $lessonDebugStatus = null;
        if (config('app.debug')) {
            $ordered = $course->orderedLessons();
            $idx = $ordered->search(fn (Lesson $l) => $l->id === $lesson->id);
            $prevLesson = ($idx !== false && $idx > 0) ? $ordered->get($idx - 1) : null;
            $canPlayVideo = (bool) $playable;
            $enrolled = $this->lessonAccess->userIsEnrolled($user, $course);

            $watchSummary = $canPlayVideo
                ? 'Video player is shown (playable payload OK).'
                : 'Video blocked: no playable URL/embed (check video driver / lesson video_ref).';

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
                    'recordProgress' => (bool) $recordProgress,
                ],
                'summary' => [
                    'watch' => $watchSummary,
                ],
                'orderInCourse' => [
                    'index' => $idx !== false ? $idx : null,
                    'orderedLessonIds' => $ordered->pluck('id')->values()->all(),
                    'previousLessonId' => $prevLesson?->id,
                    'previousLessonComplete' => $prevLesson
                        ? $this->lessonAccess->isLessonCompleteForUser($user, $prevLesson)
                        : null,
                ],
                'progressRow' => [
                    'started' => (bool) $progress->started,
                    'watched' => (bool) $progress->watched,
                ],
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
            'playerKind' => $playerKind,
            'youtubeVideoId' => $youtubeVideoId,
            'docHtml' => $docHtml,
            'recordProgress' => $recordProgress,
            'completedLessonIds' => $completedLessonIds,
            'accessibleLessonIds' => $accessibleLessonIds,
            'lessonDebugStatus' => $lessonDebugStatus,
        ]);
    }
}
