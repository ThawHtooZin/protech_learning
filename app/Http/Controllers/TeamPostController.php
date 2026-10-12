<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamChannel;
use App\Models\TeamPost;
use App\Models\TeamPostReaction;
use App\Models\User;
use App\Services\MentionService;
use App\Services\TeamAccessService;
use App\Support\HtmlSanitizer;
use App\Support\TeamPostBannerThemes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamPostController extends Controller
{
    public function __construct(
        private TeamAccessService $access,
        private MentionService $mentions,
    ) {}

    public function store(Request $request, Team $team, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canPostOnChannel($user, $team), 403);
        abort_unless($channel->team_id === $team->id, 404);

        $themeKeys = array_merge(['auto', 'none'], TeamPostBannerThemes::keys());

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:200'],
            'banner_theme' => ['nullable', 'string', Rule::in($themeKeys)],
            'body' => ['required', 'string', 'max:50000'],
        ]);

        $bodyHtml = HtmlSanitizer::clean($validated['body']);
        if ($bodyHtml === null) {
            return back()
                ->withErrors(['body' => __('Type a message')])
                ->withInput();
        }

        $subject = filled($validated['subject'] ?? null) ? trim($validated['subject']) : null;
        $themeKey = $validated['banner_theme'] ?? 'auto';
        $colors = null;
        if ($subject && $themeKey !== 'none') {
            $colors = TeamPostBannerThemes::resolve(
                in_array($themeKey, ['auto', ''], true) ? 'auto' : $themeKey,
                $subject.'|'.$team->id
            );
        }

        $post = TeamPost::query()->create([
            'team_id' => $team->id,
            'team_channel_id' => $channel->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'subject' => $subject,
            'banner_bg' => $colors['bg'] ?? null,
            'banner_text' => $colors['text'] ?? null,
            'body' => $bodyHtml,
            'body_is_html' => true,
        ]);

        $this->notifyTeam($user, $team, $channel, $post, __('You were mentioned in a team post.'));

        return redirect()
            ->route('teams.channels.show', [$team, $channel])
            ->withFragment('team-post-'.$post->id)
            ->with('status', __('Posted.'));
    }

    public function reply(Request $request, Team $team, TeamChannel $channel, TeamPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canReplyOnChannel($user, $team), 403);
        abort_unless($channel->team_id === $team->id, 404);
        abort_unless($post->team_id === $team->id && $post->team_channel_id === $channel->id && $post->isTopLevel(), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $reply = TeamPost::query()->create([
            'team_id' => $team->id,
            'team_channel_id' => $channel->id,
            'user_id' => $user->id,
            'parent_id' => $post->id,
            'subject' => null,
            'banner_bg' => null,
            'banner_text' => null,
            'body' => $validated['body'],
            'body_is_html' => false,
        ]);

        $this->notifyTeam($user, $team, $channel, $reply, __('You were mentioned in a team reply.'));

        return redirect()
            ->route('teams.channels.show', [$team, $channel])
            ->withFragment('team-post-'.$reply->id)
            ->with('status', __('Reply posted.'));
    }

    public function react(Request $request, Team $team, TeamChannel $channel, TeamPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canReplyOnChannel($user, $team), 403);
        abort_unless($channel->team_id === $team->id, 404);
        abort_unless(
            $post->team_id === $team->id
            && $post->team_channel_id === $channel->id,
            404
        );

        $validated = $request->validate([
            'emoji' => ['required', 'string', Rule::in(TeamPostReaction::ALLOWED)],
        ]);

        $existing = TeamPostReaction::query()
            ->where('team_post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            TeamPostReaction::query()->create([
                'team_post_id' => $post->id,
                'user_id' => $user->id,
                'emoji' => $validated['emoji'],
            ]);
        }

        $anchor = $post->isTopLevel() ? $post->id : ($post->parent_id ?? $post->id);

        return redirect()
            ->route('teams.channels.show', [$team, $channel])
            ->withFragment('team-post-'.$anchor);
    }

    private function notifyTeam(User $user, Team $team, TeamChannel $channel, TeamPost $post, string $message): void
    {
        $this->mentions->notifyMentionedUsersInTeam($user, $team, $post->plainBody(), [
            'message' => $message,
            'base_url' => route('teams.channels.show', [$team, $channel]),
            'fragment' => 'team-post-'.$post->id,
            'source_type' => 'team_post',
            'source_id' => $post->id,
        ]);
    }
}
