@php
    $accessType = old('access_type', isset($course) ? $course->access_type->value : 'private');
    $isListed = old('is_listed', isset($course) ? $course->is_listed : true);
@endphp

<div>
    <label class="block text-sm text-zinc-600">{{ __('Access type') }}</label>
    <select name="access_type" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900">
        <option value="private" @selected($accessType === 'private')>{{ __('Private') }}</option>
        <option value="public" @selected($accessType === 'public')>{{ __('Public') }}</option>
    </select>
</div>

<div>
    <label class="block text-sm text-zinc-600">{{ __('Price (MMK)') }}</label>
    <input type="number" name="price_mmk" min="0" step="1" value="{{ old('price_mmk', isset($course) ? $course->price_mmk : '') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900" placeholder="{{ __('Required for public') }}">
    <p class="mt-1 text-xs text-zinc-500">{{ __('Required when access type is public.') }}</p>
</div>

<label class="flex items-center gap-2 text-sm text-zinc-700">
    <input type="checkbox" name="is_listed" value="1" class="rounded border-zinc-300" @checked($isListed)>
    {{ __('Listed on catalog') }}
</label>
