<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizAdminController extends Controller
{
    public function createLessonQuiz(Course $course, Module $module, Lesson $lesson): View
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);
        $questions = Question::query()->orderBy('technology')->orderBy('topic')->get();

        return view('admin.quizzes.create-lesson', compact('course', 'module', 'lesson', 'questions'));
    }

    public function storeLessonQuiz(Request $request, Course $course, Module $module, Lesson $lesson): RedirectResponse
    {
        abort_unless($module->course_id === $course->id && $lesson->module_id === $module->id, 404);

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

        return redirect()->route('admin.courses.edit', $course)->with('status', __('Lesson quiz saved.'));
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
}
