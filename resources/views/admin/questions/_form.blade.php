@php
    $isEdit = isset($question);
    if ($isEdit) {
        $optOld = old('options');
        if (is_array($optOld)) {
            $vals = array_values(array_map(fn ($x) => $x ?? '', $optOld));
            $rows = max(4, count($vals));
            $vals = array_pad($vals, $rows, '');
        } else {
            $vals = $question->options->pluck('body')->all();
            $rows = max(4, count($vals));
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
        $vals = array_pad(is_array($optOld) ? array_values($optOld) : [], 4, '');
        $rows = 4;
        $ci = (int) old('correct_index', 0);
    }
    $formAction = $isEdit ? route('admin.questions.update', $question) : route('admin.questions.store');
@endphp
<form method="POST" action="{{ $formAction }}" class="space-y-4">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Technology') }}</label>
        <input type="text" name="technology" value="{{ old('technology', $isEdit ? $question->technology : '') }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
    </div>
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Topic') }}</label>
        <input type="text" name="topic" value="{{ old('topic', $isEdit ? $question->topic : '') }}" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
    </div>
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Question') }}</label>
        <textarea name="body" rows="3" required class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">{{ old('body', $isEdit ? $question->body : '') }}</textarea>
    </div>
    @for($i = 0; $i < $rows; $i++)
        <div>
            <label class="block text-sm text-zinc-400">{{ __('Option') }} {{ $i + 1 }}</label>
            <input type="text" name="options[]" value="{{ $vals[$i] ?? '' }}" class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white" @if($i < 2) required @endif>
        </div>
    @endfor
    <div>
        <label class="block text-sm text-zinc-400">{{ __('Correct option index (0 = first)') }}</label>
        <input type="number" name="correct_index" value="{{ $ci }}" min="0" max="{{ $rows - 1 }}" required class="mt-1 w-32 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
    </div>
    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white hover:bg-emerald-500">{{ $isEdit ? __('Save changes') : __('Save') }}</button>
        <a href="{{ route('admin.questions.index') }}" class="rounded-md border border-zinc-600 px-6 py-2 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Cancel') }}</a>
    </div>
</form>
