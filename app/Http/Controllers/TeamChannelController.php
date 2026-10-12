<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamChannel;
use App\Services\TeamAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamChannelController extends Controller
{
    public function __construct(
        private TeamAccessService $access,
    ) {}

    public function store(Request $request, Team $team): RedirectResponse
    {
        abort_unless($this->access->canManageChannels($request->user(), $team), 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('team_channels', 'name')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
        ]);

        $baseSlug = TeamChannel::makeSlug($validated['name']);
        $slug = $this->uniqueSlug($team, $baseSlug);

        $maxOrder = (int) $team->channels()->max('sort_order');

        $channel = $team->channels()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_general' => false,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()
            ->route('teams.manage', ['team' => $team, 'tab' => 'channels'])
            ->with('status', __('Channel created.'));
    }

    public function update(Request $request, Team $team, TeamChannel $channel): RedirectResponse
    {
        abort_unless($this->access->canManageChannels($request->user(), $team), 403);
        $this->assertChannelTeam($team, $channel);
        abort_if($channel->is_general, 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('team_channels', 'name')
                    ->where(fn ($q) => $q->where('team_id', $team->id))
                    ->ignore($channel->id),
            ],
        ]);

        $baseSlug = TeamChannel::makeSlug($validated['name']);
        $slug = $this->uniqueSlug($team, $baseSlug, $channel->id);

        $channel->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('teams.manage', ['team' => $team, 'tab' => 'channels'])
            ->with('status', __('Channel updated.'));
    }

    public function destroy(Request $request, Team $team, TeamChannel $channel): RedirectResponse
    {
        abort_unless($this->access->canManageChannels($request->user(), $team), 403);
        $this->assertChannelTeam($team, $channel);
        abort_if($channel->is_general, 403);

        $channel->delete();

        return redirect()
            ->route('teams.manage', ['team' => $team, 'tab' => 'channels'])
            ->with('status', __('Channel deleted.'));
    }

    private function assertChannelTeam(Team $team, TeamChannel $channel): void
    {
        abort_unless($channel->team_id === $team->id, 404);
    }

    private function uniqueSlug(Team $team, string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        while (
            $team->channels()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = Str::limit($base, 70, '').'-'.$i;
            $i++;
        }

        return $slug;
    }
}
