<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\LessonAccessService;
use App\Services\QuizGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizTakeController extends Controller
{
    public function __construct(
        private LessonAccessService $lessonAccess,
        private QuizGradingService $grading,
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
            if (! $this->lessonAccess->canRecordProgressForLesson($user, $lesson)) {
                abort(403, __('Complete earlier lessons in order before taking this quiz.'));
            }
        } elseif ($quiz->module_id) {
            if (! $this->lessonAccess->canTakeModuleQuiz($user, $quiz)) {
                abort(403);
            }
        }

        return view('quizzes.show', compact('quiz', 'course'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = $request->user();
        $quiz->load(['questions', 'lesson.module.course', 'module.course']);

        if ($quiz->lesson_id) {
            $lesson = $quiz->lesson;
            if (! $this->lessonAccess->canRecordProgressForLesson($user, $lesson)) {
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

        return redirect()
            ->route('quizzes.result', [$quiz, $attempt])
            ->with('status', $attempt->passed ? __('Passed!') : __('Keep trying.'));
    }

    public function result(Request $request, Quiz $quiz, QuizAttempt $attempt): View
    {
        $user = $request->user();
        abort_unless($attempt->quiz_id === $quiz->id && $attempt->user_id === $user->id, 404);

        $attemptModel = $attempt->load(['answers.question.options']);

        $course = $quiz->lesson_id
            ? $quiz->lesson->module->course
            : $quiz->module->course;

        return view('quizzes.result', [
            'quiz' => $quiz,
            'attempt' => $attemptModel,
            'course' => $course,
        ]);
    }
}
