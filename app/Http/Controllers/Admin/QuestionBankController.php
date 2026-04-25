<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $technology = trim((string) $request->query('technology', ''));
        $topic = trim((string) $request->query('topic', ''));
        $sort = (string) $request->query('sort', 'id_desc');
        $perPage = (int) $request->query('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $query = Question::query()->withCount('options');

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $like = '%'.$escaped.'%';
            $query->where(function ($sub) use ($like): void {
                $sub->where('body', 'like', $like)
                    ->orWhere('technology', 'like', $like)
                    ->orWhere('topic', 'like', $like);
            });
        }

        if ($technology !== '') {
            $query->where('technology', $technology);
        }

        if ($topic !== '') {
            $query->where('topic', $topic);
        }

        match ($sort) {
            'technology_asc' => $query->orderBy('technology')->orderBy('topic')->orderBy('id'),
            'topic_asc' => $query->orderBy('topic')->orderBy('technology')->orderBy('id'),
            'created_asc' => $query->oldest('id'),
            default => $query->latest('id'),
        };

        $questions = $query->paginate($perPage)->withQueryString();

        $filterTechnologies = Question::query()->distinct()->orderBy('technology')->pluck('technology');
        $filterTopics = Question::query()->distinct()->orderBy('topic')->pluck('topic');

        return view('admin.questions.index', [
            'questions' => $questions,
            'filterTechnologies' => $filterTechnologies,
            'filterTopics' => $filterTopics,
            'filters' => [
                'q' => $q,
                'technology' => $technology,
                'topic' => $topic,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.questions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'technology' => ['required', 'string', 'max:120'],
            'topic' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:2000'],
            'correct_index' => ['required', 'integer', 'min:0'],
        ]);

        $options = array_values(array_filter(
            $data['options'],
            fn (?string $x) => $x !== null && trim($x) !== ''
        ));

        if (count($options) < 2) {
            return back()->withErrors(['options' => __('At least two non-empty options required.')]);
        }

        $data['options'] = $options;
        if ((int) $data['correct_index'] >= count($data['options'])) {
            return back()->withErrors(['correct_index' => __('Invalid correct option.')]);
        }

        $question = Question::query()->create([
            'technology' => $data['technology'],
            'topic' => $data['topic'],
            'body' => $data['body'],
            'type' => 'mcq',
        ]);

        foreach ($data['options'] as $i => $text) {
            QuestionOption::query()->create([
                'question_id' => $question->id,
                'body' => $text,
                'is_correct' => (int) $i === (int) $data['correct_index'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.questions.index')->with('status', __('Question added.'));
    }

    public function edit(Request $request, Question $question): View
    {
        $question->load('options');
        $lessonQuizReturn = $this->resolveLessonQuizReturn($request);

        return view('admin.questions.edit', compact('question', 'lessonQuizReturn'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'technology' => ['required', 'string', 'max:120'],
            'topic' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:2000'],
            'correct_index' => ['required', 'integer', 'min:0'],
        ]);

        $options = array_values(array_filter(
            $data['options'],
            fn (?string $x) => $x !== null && trim($x) !== ''
        ));

        if (count($options) < 2) {
            return back()->withErrors(['options' => __('At least two non-empty options required.')]);
        }

        $data['options'] = $options;
        if ((int) $data['correct_index'] >= count($data['options'])) {
            return back()->withErrors(['correct_index' => __('Invalid correct option.')]);
        }

        DB::transaction(function () use ($data, $question): void {
            $question->update([
                'technology' => $data['technology'],
                'topic' => $data['topic'],
                'body' => $data['body'],
            ]);

            $question->options()->delete();

            foreach ($data['options'] as $i => $text) {
                QuestionOption::query()->create([
                    'question_id' => $question->id,
                    'body' => $text,
                    'is_correct' => (int) $i === (int) $data['correct_index'],
                    'sort_order' => $i,
                ]);
            }
        });

        if ($return = $this->resolveLessonQuizReturn($request)) {
            return redirect()->route('admin.quizzes.lesson.edit', [
                $return['course'],
                $return['module'],
                $return['lesson'],
            ])->with('status', __('Question updated.'));
        }

        return redirect()->route('admin.questions.index')->with('status', __('Question updated.'));
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $preview = Str::limit($question->body, 80);
        $return = $this->resolveLessonQuizReturn($request);
        $question->delete();

        if ($return) {
            return redirect()->route('admin.quizzes.lesson.edit', [
                $return['course'],
                $return['module'],
                $return['lesson'],
            ])->with('status', __('Question removed: :preview', ['preview' => $preview]));
        }

        return redirect()->route('admin.questions.index')->with('status', __('Question removed: :preview', ['preview' => $preview]));
    }

    /**
     * @return array{course: Course, module: Module, lesson: Lesson}|null
     */
    private function resolveLessonQuizReturn(Request $request): ?array
    {
        if ($request->input('return_context') !== 'lesson_quiz') {
            return null;
        }

        $courseId = (int) $request->input('return_course_id', 0);
        $moduleId = (int) $request->input('return_module_id', 0);
        $lessonId = (int) $request->input('return_lesson_id', 0);
        if ($courseId < 1 || $moduleId < 1 || $lessonId < 1) {
            return null;
        }

        $course = Course::query()->find($courseId);
        if (! $course) {
            return null;
        }

        $module = Module::query()->whereKey($moduleId)->where('course_id', $course->id)->first();
        if (! $module) {
            return null;
        }

        $lesson = Lesson::query()->whereKey($lessonId)->where('module_id', $module->id)->first();
        if (! $lesson) {
            return null;
        }

        return [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
        ];
    }
}
