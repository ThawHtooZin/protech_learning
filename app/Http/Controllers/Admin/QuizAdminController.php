<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuizAdminController extends Controller
{
    public function createLessonQuiz(Course $course, Module $module, Lesson $lesson): RedirectResponse|View
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        if ($lesson->quizzes()->exists()) {
            return redirect()
                ->route('admin.quizzes.lesson.edit', [$course, $module, $lesson])
                ->with('status', __('This lesson already has a quiz — use the editor to change it.'));
        }

        $form = $this->lessonQuizPickerData();

        return view('admin.quizzes.create-lesson', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'quiz' => null,
            'initialQuestionIds' => [],
            ...$form,
        ]);
    }

    public function editLessonQuiz(Course $course, Module $module, Lesson $lesson): RedirectResponse|View
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $quiz = $lesson->quizzes()->withCount('attempts')->with('questions')->first();
        if (! $quiz) {
            return redirect()
                ->route('admin.quizzes.lesson.create', [$course, $module, $lesson])
                ->with('status', __('Add a lesson quiz for this video.'));
        }

        $form = $this->lessonQuizPickerData();
        $initialQuestionIds = $quiz->questions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        return view('admin.quizzes.edit-lesson', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            'quiz' => $quiz,
            'initialQuestionIds' => $initialQuestionIds,
            ...$form,
        ]);
    }

    public function storeLessonQuiz(Request $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        if ($lesson->quizzes()->exists()) {
            return redirect()
                ->route('admin.quizzes.lesson.edit', [$course, $module, $lesson])
                ->with('status', __('This lesson already has a quiz — use the editor to change it.'));
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pass_threshold_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'distinct', 'exists:questions,id'],
        ]);

        $quiz = Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'module_id' => null,
            'title' => $data['title'],
            'pass_threshold_percent' => $data['pass_threshold_percent'],
        ]);

        foreach ($data['question_ids'] as $i => $qid) {
            $quiz->questions()->attach($qid, ['sort_order' => $i]);
        }

        return redirect()->route('admin.quizzes.lesson.edit', [$course, $module, $lesson])->with('status', __('Lesson quiz saved.'));
    }

    public function updateLessonQuiz(Request $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $quiz = $lesson->quizzes()->first();
        if (! $quiz) {
            return redirect()
                ->route('admin.quizzes.lesson.create', [$course, $module, $lesson])
                ->with('status', __('No quiz on this lesson yet.'));
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pass_threshold_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'distinct', 'exists:questions,id'],
        ]);

        $quiz->update([
            'title' => $data['title'],
            'pass_threshold_percent' => $data['pass_threshold_percent'],
        ]);

        $sync = [];
        foreach ($data['question_ids'] as $i => $qid) {
            $sync[(int) $qid] = ['sort_order' => $i];
        }
        $quiz->questions()->sync($sync);

        return redirect()->route('admin.quizzes.lesson.edit', [$course, $module, $lesson])->with('status', __('Lesson quiz updated.'));
    }

    public function destroyLessonQuiz(Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

        $quiz = $lesson->quizzes()->first();
        if (! $quiz) {
            return redirect()->route('admin.courses.edit', $course)->with('status', __('No lesson quiz to remove.'));
        }

        $quiz->delete();
        LessonProgress::query()->where('lesson_id', $lesson->id)->update(['quiz_passed' => false]);

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson quiz removed. Learner completion flags for this lesson were reset.'));
    }

    public function createModuleQuiz(Course $course, Module $module): View
    {
        abort_unless($module->course_id === $course->id, 404);
        $questions = Question::query()->orderBy('technology')->orderBy('topic')->get();

        return view('admin.quizzes.create-module', compact('course', 'module', 'questions'));
    }

    public function storeModuleQuiz(Request $request, Course $course, Module $module): RedirectResponse
    {
        abort_unless($module->course_id === $course->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pass_threshold_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'distinct', 'exists:questions,id'],
        ]);

        $quiz = Quiz::query()->create([
            'lesson_id' => null,
            'module_id' => $module->id,
            'title' => $data['title'],
            'pass_threshold_percent' => $data['pass_threshold_percent'],
        ]);

        foreach ($data['question_ids'] as $i => $qid) {
            $quiz->questions()->attach($qid, ['sort_order' => $i]);
        }

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Module quiz saved.'));
    }

    /**
     * @return array{questionBank: array<int, array<string, mixed>>, technologies: array<int, string>, topics: array<int, string>}
     */
    private function lessonQuizPickerData(): array
    {
        $questions = Question::query()->orderBy('technology')->orderBy('topic')->get();
        $questionBank = $questions->map(fn (Question $q) => [
            'id' => $q->id,
            'technology' => $q->technology,
            'topic' => $q->topic,
            'body' => Str::limit(strip_tags($q->body), 140),
            'type' => $q->type,
        ])->values()->all();

        return [
            'questionBank' => $questionBank,
            'technologies' => $questions->pluck('technology')->unique()->sort()->values()->all(),
            'topics' => $questions->pluck('topic')->unique()->sort()->values()->all(),
        ];
    }
}
