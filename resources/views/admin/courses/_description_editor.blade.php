@php
    $descValue = old('description', isset($course) ? $course->description : '');
@endphp
<div>
    <label class="block text-sm text-zinc-600">{{ __('Description') }}</label>
    <input type="hidden" name="description" id="course-description-input" value="{{ $descValue }}">
    <div id="course-description-editor" class="mt-1 min-h-[180px] rounded-md border border-zinc-300 bg-white"></div>
    <p class="mt-1 text-xs text-zinc-500">{{ __('Bold, lists, and headings are supported.') }}</p>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('course-description-input');
            var form = input && input.closest('form');
            if (!input || !form || typeof Quill === 'undefined') return;

            var quill = new Quill('#course-description-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            if (input.value) {
                quill.root.innerHTML = input.value;
            }

            form.addEventListener('submit', function () {
                var html = quill.root.innerHTML;
                if (html === '<p><br></p>') html = '';
                input.value = html;
            });
        });
    </script>
@endpush
