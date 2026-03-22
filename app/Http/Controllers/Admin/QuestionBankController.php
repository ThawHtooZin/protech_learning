<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function index(): View
    {
        $questions = Question::query()->with('options')->latest()->paginate(30);

        return view('admin.questions.index', compact('questions'));
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

    public function edit(Question $question): View
    {
        $question->load('options');

        return view('admin.questions.edit', compact('question'));
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

        return redirect()->route('admin.questions.index')->with('status', __('Question updated.'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $preview = Str::limit($question->body, 80);
        $question->delete();

        return redirect()->route('admin.questions.index')->with('status', __('Question removed: :preview', ['preview' => $preview]));
    }
}
