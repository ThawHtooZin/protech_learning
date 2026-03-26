<?php

namespace App\Http\Controllers;

use App\Models\CourseActivityLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\LessonAccessService;
use App\Services\QuizGradingService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\QuizActivityLog;

class QuizTakeController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
        private QuizGradingService $grading,
        private ActivityLogger $activity,
    ) {}

    public function show(Request $request, Quiz $quiz): View
    {
        $user = $request->user();
        $quiz->load(['questions.options', 'lesson.module.course', 'module.course']);

        $course = $quiz->lesson_id
            ? $quiz->lesson->module->course
            : $quiz->module->course;

        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (! $this->lessonAccess->canTakeLessonQuiz($user, $lesson)) {
                abort(403, __('Complete earlier lessons in order before taking this quiz.'));
            }
        } elseif ($quiz->module_id) {
            if (! $this->lessonAccess->canTakeModuleQuiz($user, $quiz)) {
                abort(403);
            }
        }

        $startLog = $this->activity->quizStart(
            user: $user,
            eventType: 'quiz_started',
            course: $course,
            lesson: $quiz->lesson_id ? $quiz->lesson : null,
            quiz: $quiz,
        );
        $request->session()->put("quiz_started_log_id.{$quiz->id}", $startLog->id);

        return view('quizzes.show', compact('quiz', 'course'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = $request->user();
        $quiz->load(['questions', 'lesson.module.course', 'module.course']);

        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (! $this->lessonAccess->canTakeLessonQuiz($user, $lesson)) {
                abort(403, __('Complete earlier lessons in order before taking this quiz.'));
            }
        } elseif ($quiz->module_id) {
            if (! $this->lessonAccess->canTakeModuleQuiz($user, $quiz)) {
                abort(403);
            }
        }

        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }

        $attempt = $this->grading->grade($user, $quiz, $answers);

        $course = $quiz->lesson_id
            ? $quiz->lesson->module->course
            : $quiz->module->course;

        $startLogId = $request->session()->pull("quiz_started_log_id.{$quiz->id}");
        if ($startLogId) {
            $log = QuizActivityLog::query()->find($startLogId);
            if ($log && (int) $log->user_id === (int) $user->id) {
                $this->activity->quizEnd($log, $attempt);
            }
        } else {
            $this->activity->quizStart(
                user: $user,
                eventType: 'quiz_submitted',
                course: $course,
                lesson: $quiz->lesson_id ? $quiz->lesson : null,
                quiz: $quiz,
                meta: [
                    'attempt_id' => $attempt->id,
                    'score_percent' => $attempt->score_percent,
                    'passed' => $attempt->passed,
                ],
            );
        }

        if ($this->lessonAccess->courseCompletionPercent($user, $course) === 100) {
            $already = CourseActivityLog::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('event_type', 'course_completed')
                ->exists();
            if (! $already) {
                $this->activity->courseInstant($user, 'course_completed', $course, [
                    'answer_accuracy_percent' => $this->lessonAccess->courseAnswerAccuracyPercent($user, $course),
                ]);
            }
        }

        $attempt->loadMissing('answers');
        $flash = $quiz->lesson_id
            ? __('You got :correct of :total correct.', [
                'correct' => $attempt->answers->where('is_correct', true)->count(),
                'total' => $attempt->answers->count(),
            ])
            : ($attempt->passed
                ? __('Passed! You can continue to the next module.')
                : __('You need a higher score to unlock the next module.'));

        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            $nextLesson = $this->lessonAccess->nextLessonAfter($lesson);
            if ($nextLesson && $this->lessonAccess->canViewLesson($user, $nextLesson)) {
                return redirect()
                    ->route('lessons.show', $nextLesson)
                    ->with('status', $flash);
            }
        }

        return redirect()
            ->route('quizzes.result', [$quiz, $attempt])
            ->with('status', $flash);
    }

    public function result(Request $request, Quiz $quiz, QuizAttempt $attempt): View
    {
        $user = $request->user();
        abort_unless($attempt->quiz_id === $quiz->id && $attempt->user_id === $user->id, 404);

        $attemptModel = $attempt->load(['answers.question.options']);

        $course = $quiz->lesson_id
            ? $quiz->lesson->module->course
            : $quiz->module->course;

        $correctCount = $attemptModel->answers->where('is_correct', true)->count();
        $totalQuestions = $attemptModel->answers->count();

        $nextLesson = null;
        if ($quiz->lesson_id && $quiz->lesson) {
            $candidate = $this->lessonAccess->nextLessonAfter($quiz->lesson);
            if ($candidate && $this->lessonAccess->canViewLesson($user, $candidate)) {
                $nextLesson = $candidate;
            }
        }

        return view('quizzes.result', [
            'quiz' => $quiz,
            'attempt' => $attemptModel,
            'course' => $course,
            'correctCount' => $correctCount,
            'totalQuestions' => $totalQuestions,
            'isLessonQuiz' => (bool) $quiz->lesson_id,
            'isModuleQuiz' => (bool) $quiz->module_id && $quiz->lesson_id === null,
            'nextLesson' => $nextLesson,
        ]);
    }
}
