<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Tag;
use App\Services\ForumPostRateLimiter;
use App\Services\MentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function __construct(
        private ForumPostRateLimiter $rateLimiter,
        private MentionService $mentions,
    ) {}

    public function index(): View
    {
        $categories = ForumCategory::query()
            ->orderBy('sort_order')
            ->withCount('threads')
            ->get();

        return view('forums.index', compact('categories'));
    }

    public function category(ForumCategory $forumCategory): View
    {
        $threads = $forumCategory->threads()
            ->with(['author.profile', 'tags'])
            ->withCount('posts')
            ->latest()
            ->paginate(20);

        return view('forums.category', compact('forumCategory', 'threads'));
    }

    public function createThread(ForumCategory $forumCategory): View
    {
        $tags = Tag::query()->orderBy('name')->get();

        return view('forums.create-thread', compact('forumCategory', 'tags'));
    }

    public function storeThread(Request $request, ForumCategory $forumCategory): RedirectResponse
    {
        $this->rateLimiter->assertWithinLimit($request->user());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        $slug = Str::slug($data['title']).'-'.Str::random(6);

        $thread = ForumThread::query()->create([
            'forum_category_id' => $forumCategory->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => $slug,
        ]);

        if (! empty($data['tags'])) {
            $thread->tags()->sync($data['tags']);
        }

        $post = ForumPost::query()->create([
            'forum_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $this->mentions->notifyMentionedUsers($request->user(), $data['body'], [
            'message' => __('You were mentioned in a forum thread.'),
            'base_url' => route('forums.thread', [$forumCategory, $thread]),
            'source_type' => 'forum_post',
            'source_id' => $post->id,
        ]);

        return redirect()->route('forums.thread', [$forumCategory, $thread]);
    }

    public function thread(ForumCategory $forumCategory, ForumThread $forumThread): View
    {
        abort_unless($forumThread->forum_category_id === $forumCategory->id, 404);

        $forumThread->load(['tags', 'posts.user.profile']);
        $posts = $forumThread->posts()->with('user.profile')->orderBy('created_at')->get();

        return view('forums.thread', compact('forumCategory', 'forumThread', 'posts'));
    }

    public function storePost(Request $request, ForumCategory $forumCategory, ForumThread $forumThread): RedirectResponse
    {
        abort_unless($forumThread->forum_category_id === $forumCategory->id, 404);

        $this->rateLimiter->assertWithinLimit($request->user());

        $data = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $post = ForumPost::query()->create([
            'forum_thread_id' => $forumThread->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $this->mentions->notifyMentionedUsers($request->user(), $data['body'], [
            'message' => __('You were mentioned in a forum reply.'),
            'base_url' => route('forums.thread', [$forumCategory, $forumThread]),
            'source_type' => 'forum_post',
            'source_id' => $post->id,
        ]);

        return redirect()->route('forums.thread', [$forumCategory, $forumThread]);
    }
}
