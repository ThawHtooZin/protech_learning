<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamPost;
use App\Models\User;
use App\Services\MentionService;
use App\Services\TeamAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamPostController extends Controller
{
    public function __construct(
        private TeamAccessService $access,
        private MentionService $mentions,
    ) {}

    public function store(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canPostOnGeneral($user, $team), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $post = TeamPost::query()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'body' => $validated['body'],
        ]);

        $this->notifyTeam($user, $team, $post, __('You were mentioned in a team post.'));

        return redirect()
            ->route('teams.general', $team)
            ->with('status', __('Posted.'));
    }

    public function reply(Request $request, Team $team, TeamPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->access->canReplyOnGeneral($user, $team), 403);
        abort_unless($post->team_id === $team->id && $post->isTopLevel(), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $reply = TeamPost::query()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'parent_id' => $post->id,
            'body' => $validated['body'],
        ]);

        $this->notifyTeam($user, $team, $reply, __('You were mentioned in a team reply.'));

        return redirect()
            ->route('teams.general', $team)
            ->with('status', __('Reply posted.'));
    }

    private function notifyTeam(User $user, Team $team, TeamPost $post, string $message): void
    {
        $this->mentions->notifyMentionedUsersInTeam($user, $team, $post->body, [
            'message' => $message,
            'base_url' => route('teams.general', $team),
            'fragment' => 'team-post-'.$post->id,
            'source_type' => 'team_post',
            'source_id' => $post->id,
        ]);
    }
}
