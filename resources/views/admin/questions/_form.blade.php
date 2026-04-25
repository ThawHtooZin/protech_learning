@php
    $lessonQuizReturn = $lessonQuizReturn ?? null;
    $isEdit = isset($question);
    if ($isEdit) {
        $optOld = old('options');
        if (is_array($optOld)) {
            $vals = array_values(array_map(fn ($x) => $x ?? '', $optOld));
            $rows = max(6, count($vals));
            $vals = array_pad($vals, $rows, '');
        } else {
            $vals = $question->options->pluck('body')->all();
            $rows = max(6, count($vals));
            $vals = array_pad($vals, $rows, '');
        }
        if (old('correct_index') !== null && old('correct_index') !== '') {
            $ci = (int) old('correct_index');
        } else {
            $found = $question->options->values()->search(fn ($o) => $o->is_correct);
            $ci = $found === false ? 0 : (int) $found;
        }
    } else {
        $optOld = old('options');
        $vals = array_pad(is_array($optOld) ? array_values($optOld) : [], 6, '');
        $rows = 6;
        $ci = (int) old('correct_index', 0);
    }
    $formAction = $isEdit ? route('admin.questions.update', $question) : route('admin.questions.store');
    $cancelUrl = $lessonQuizReturn
        ? route('admin.quizzes.lesson.edit', [$lessonQuizReturn['course'], $lessonQuizReturn['module'], $lessonQuizReturn['lesson']])
        : route('admin.questions.index');
@endphp
<form method="POST" action="{{ $formAction }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if($lessonQuizReturn)
        <input type="hidden" name="return_context" value="lesson_quiz">
        <input type="hidden" name="return_course_id" value="{{ $lessonQuizReturn['course']->id }}">
        <input type="hidden" name="return_module_id" value="{{ $lessonQuizReturn['module']->id }}">
        <input type="hidden" name="return_lesson_id" value="{{ $lessonQuizReturn['lesson']->id }}">
    @endif

    <div>
        <label class="block text-sm text-zinc-400">{{ __('Technology') }}</label>
        <input type="text" name="technology" value="{{ old('technology', $isEdit ? $question->technology : '') }}" required maxlength="120"
            class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
    </div>
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Topic') }}</label>
        <input type="text" name="topic" value="{{ old('topic', $isEdit ? $question->topic : '') }}" required maxlength="120"
            class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
    </div>
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Question stem') }}</label>
        <textarea name="body" rows="4" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">{{ old('body', $isEdit ? $question->body : '') }}</textarea>
    </div>

    <div class="rounded-lg border border-zinc-800 bg-zinc-900/30 p-4">
        <h3 class="text-sm font-semibold text-white">{{ __('Answer choices') }}</h3>
        <p class="mt-1 text-xs text-zinc-500">{{ __('Select the radio next to the correct answer. The first two options are required; leave extras blank if you do not need them.') }}</p>
        <div class="mt-4 space-y-3">
            @for($i = 0; $i < $rows; $i++)
                <div class="rounded-lg border border-zinc-800 bg-zinc-950/50 p-3">
                    <div class="flex flex-wrap items-start gap-3 sm:flex-nowrap">
                        <div class="flex shrink-0 items-center pt-2 sm:pt-2.5">
                            <input type="radio" name="correct_index" value="{{ $i }}" id="correct_{{ $i }}"
                                @checked((int) $ci === $i)
                                class="h-4 w-4 border-zinc-600 bg-zinc-900 text-emerald-600 focus:ring-emerald-600 focus:ring-offset-0"
                                aria-label="{{ __('Correct answer: option :n', ['n' => $i + 1]) }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="opt_{{ $i }}" class="block text-sm text-zinc-400">
                                {{ __('Option') }} {{ $i + 1 }}
                                @if($i < 2)
                                    <span class="text-red-400">*</span>
                                @endif
                            </label>
                            <input id="opt_{{ $i }}" type="text" name="options[]" value="{{ $vals[$i] ?? '' }}"
                                class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white"
                                @if($i < 2) required @endif>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white hover:bg-emerald-500">{{ $isEdit ? __('Save changes') : __('Save') }}</button>
        <a href="{{ $cancelUrl }}" class="inline-flex items-center rounded-md border border-zinc-600 px-6 py-2 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Cancel') }}</a>
    </div>
</form>
