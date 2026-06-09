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
        $categories = ForumCategory::query()->withCount('threads')->orderBy('sort_order')->get();

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

    public function updateCategory(Request $request, ForumCategory $forumCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $forumCategory->update(['name' => $data['name']]);

        return redirect()->route('admin.forums.categories')->with('status', __('Category updated.'));
    }

    public function destroyCategory(ForumCategory $forumCategory): RedirectResponse
    {
        if ($forumCategory->threads()->exists()) {
            return redirect()
                ->route('admin.forums.categories')
                ->withErrors(['category' => __('Cannot delete a category that has threads.')]);
        }

        $forumCategory->delete();

        return redirect()->route('admin.forums.categories')->with('status', __('Category deleted.'));
    }

    public function reorderCategories(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'distinct', 'exists:forum_categories,id'],
        ]);

        foreach ($data['category_ids'] as $index => $id) {
            ForumCategory::query()->whereKey($id)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.forums.categories')->with('status', __('Category order saved.'));
    }

    public function tags(): View
    {
        $tags = Tag::query()->withCount('threads')->orderBy('name')->paginate(40);

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

    public function updateTag(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $tag->update(['name' => $data['name']]);

        return redirect()->route('admin.forums.tags')->with('status', __('Tag updated.'));
    }

    public function destroyTag(Tag $tag): RedirectResponse
    {
        $tag->threads()->detach();
        $tag->delete();

        return redirect()->route('admin.forums.tags')->with('status', __('Tag deleted.'));
    }
}
