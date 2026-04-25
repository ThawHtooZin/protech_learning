{{--
  Expects: $formAction, $httpMethod, $submitLabel, $course, $module, $lesson, $quiz (nullable),
  $questionBank, $technologies, $topics, $initialQuestionIds
--}}
@php
    $titleValue = old('title', $quiz->title ?? '');
    $thresholdValue = old('pass_threshold_percent', $quiz->pass_threshold_percent ?? 70);
@endphp
<script>
    document.addEventListener('alpine:init', () => {
        if (window.__lessonQuizPickerRegistered) {
            return;
        }
        window.__lessonQuizPickerRegistered = true;
        Alpine.data('lessonQuizPicker', (bank, initialSelectedIds) => ({
            bank,
            filterText: '',
            filterTech: '',
            filterTopic: '',
            selectedIds: initialSelectedIds && initialSelectedIds.length ? [...initialSelectedIds] : [],
            questionById(id) {
                return this.bank.find((q) => q.id === id);
            },
            get filteredPool() {
                const t = this.filterText.trim().toLowerCase();
                return this.bank.filter((q) => {
                    if (this.selectedIds.includes(q.id)) {
                        return false;
                    }
                    if (this.filterTech && q.technology !== this.filterTech) {
                        return false;
                    }
                    if (this.filterTopic && q.topic !== this.filterTopic) {
                        return false;
                    }
                    if (!t) {
                        return true;
                    }
                    return (String(q.body) + q.technology + q.topic + String(q.type)).toLowerCase().includes(t);
                });
            },
            add(id) {
                if (!this.selectedIds.includes(id)) {
                    this.selectedIds.push(id);
                }
            },
            remove(id) {
                this.selectedIds = this.selectedIds.filter((x) => x !== id);
            },
            moveUp(i) {
                if (i < 1) {
                    return;
                }
                const next = [...this.selectedIds];
                const tmp = next[i - 1];
                next[i - 1] = next[i];
                next[i] = tmp;
                this.selectedIds = next;
            },
            moveDown(i) {
                if (i >= this.selectedIds.length - 1) {
                    return;
                }
                const next = [...this.selectedIds];
                const tmp = next[i + 1];
                next[i + 1] = next[i];
                next[i] = tmp;
                this.selectedIds = next;
            },
            clearFilters() {
                this.filterText = '';
                this.filterTech = '';
                this.filterTopic = '';
            },
        }));
    });
</script>

<form method="POST" action="{{ $formAction }}" class="space-y-6" x-data="lessonQuizPicker(@js($questionBank), @js($initialQuestionIds))">
    @csrf
    @if(strtoupper($httpMethod) === 'PUT')
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Quiz title') }}</label>
                <input type="text" name="title" value="{{ $titleValue }}" required maxlength="255"
                    class="mt-1 w-full rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white"
                    placeholder="{{ __('e.g. Check your understanding') }}">
            </div>
            <div>
                <label class="block text-sm text-zinc-400">{{ __('Pass threshold %') }}</label>
                <p class="mt-0.5 text-xs text-zinc-600">{{ __('Learners need at least this score to pass (lesson quizzes also unlock the next lesson when submitted).') }}</p>
                <input type="number" name="pass_threshold_percent" value="{{ $thresholdValue }}" min="1" max="100" required
                    class="mt-1 w-32 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-white">
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-white">{{ __('Questions in this quiz') }}</h3>
                    <span class="text-xs text-zinc-500"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
                </div>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Order matches how learners see questions. Use the arrows to reorder.') }}</p>
                <ul class="mt-3 space-y-2">
                    <template x-if="selectedIds.length === 0">
                        <li class="rounded-md border border-dashed border-zinc-700 px-3 py-4 text-center text-sm text-zinc-500">
                            {{ __('Add questions from the bank on the right.') }}
                        </li>
                    </template>
                    <template x-for="(qid, index) in selectedIds" :key="qid">
                        <li class="flex gap-2 rounded-md border border-zinc-800 bg-zinc-950/80 p-2 text-sm">
                            <div class="flex shrink-0 flex-col gap-0.5">
                                <button type="button" class="rounded border border-zinc-700 px-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white disabled:opacity-30" @click="moveUp(index)" :disabled="index === 0">↑</button>
                                <button type="button" class="rounded border border-zinc-700 px-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white disabled:opacity-30" @click="moveDown(index)" :disabled="index === selectedIds.length - 1">↓</button>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-zinc-200" x-text="questionById(qid)?.body"></p>
                                <p class="mt-0.5 text-xs text-zinc-500">
                                    <span x-text="questionById(qid)?.technology"></span>
                                    <span class="text-zinc-600">·</span>
                                    <span x-text="questionById(qid)?.topic"></span>
                                    <span class="text-zinc-600">·</span>
                                    <span x-text="questionById(qid)?.type"></span>
                                </p>
                            </div>
                            <button type="button" class="shrink-0 self-start rounded px-2 py-1 text-xs text-red-400 hover:bg-red-950/40" @click="remove(qid)">{{ __('Remove') }}</button>
                            <input type="hidden" name="question_ids[]" :value="qid">
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-white">{{ __('Question bank') }}</h3>
            <div class="flex flex-wrap gap-2">
                <input type="search" x-model="filterText" placeholder="{{ __('Search text…') }}"
                    class="min-w-[8rem] flex-1 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white">
                <select x-model="filterTech" class="rounded-md border border-zinc-700 bg-zinc-900 px-2 py-2 text-sm text-white">
                    <option value="">{{ __('All technologies') }}</option>
                    @foreach($technologies as $tech)
                        <option value="{{ $tech }}">{{ $tech }}</option>
                    @endforeach
                </select>
                <select x-model="filterTopic" class="rounded-md border border-zinc-700 bg-zinc-900 px-2 py-2 text-sm text-white">
                    <option value="">{{ __('All topics') }}</option>
                    @foreach($topics as $topic)
                        <option value="{{ $topic }}">{{ $topic }}</option>
                    @endforeach
                </select>
                <button type="button" @click="clearFilters()" class="rounded-md border border-zinc-700 px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-800">{{ __('Clear filters') }}</button>
            </div>
            <div class="max-h-[min(28rem,55vh)] space-y-1 overflow-y-auto rounded-md border border-zinc-800 p-2">
                <template x-if="filteredPool.length === 0">
                    <p class="px-2 py-6 text-center text-sm text-zinc-500">{{ __('No matching questions, or all matches are already selected.') }}</p>
                </template>
                <template x-for="q in filteredPool" :key="q.id">
                    <div class="flex items-start gap-2 rounded-md border border-transparent px-2 py-2 hover:border-zinc-700 hover:bg-zinc-900/60">
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="text-zinc-200" x-text="q.body"></p>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                <span x-text="q.technology"></span>
                                <span class="text-zinc-600">·</span>
                                <span x-text="q.topic"></span>
                            </p>
                        </div>
                        <button type="button" @click="add(q.id)" class="shrink-0 rounded-md bg-zinc-700 px-2 py-1 text-xs text-white hover:bg-emerald-700">{{ __('Add') }}</button>
                    </div>
                </template>
            </div>
            <p class="text-xs text-zinc-600">
                <a href="{{ route('admin.questions.index') }}" class="text-emerald-400 hover:underline">{{ __('Manage question bank') }}</a>
            </p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-zinc-800 pt-6">
        <button type="submit" class="rounded-md bg-emerald-600 px-6 py-2 text-white hover:bg-emerald-500">{{ $submitLabel }}</button>
        <a href="{{ route('admin.courses.edit', $course) }}" class="inline-flex items-center rounded-md border border-zinc-700 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-900">{{ __('Back to course') }}</a>
    </div>
</form>
