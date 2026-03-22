<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForumSetupController extends Controller
{
    public function categories(): View
    {
        $categories = ForumCategory::query()->orderBy('sort_order')->get();

        return view('admin.forums.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $max = (int) ForumCategory::query()->max('sort_order');

        ForumCategory::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'sort_order' => $max + 1,
        ]);

        return redirect()->route('admin.forums.categories')->with('status', __('Category created.'));
    }

    public function tags(): View
    {
        $tags = Tag::query()->orderBy('name')->paginate(40);

        return view('admin.forums.tags', compact('tags'));
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        Tag::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
        ]);

        return redirect()->route('admin.forums.tags')->with('status', __('Tag created.'));
    }
}
