@extends('layouts.admin')

@section('title', __('Edit question'))

@section('heading', __('Edit question'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.questions.index') }}" class="text-sm text-emerald-400 hover:underline">← {{ __('Back to question bank') }}</a>
    </div>
    <div class="mx-auto max-w-2xl">
        @include('admin.questions._form', ['question' => $question])
    </div>
@endsection
