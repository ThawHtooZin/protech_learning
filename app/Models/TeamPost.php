<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'team_id',
    'team_channel_id',
    'user_id',
    'parent_id',
    'subject',
    'banner_bg',
    'banner_text',
    'body',
    'body_is_html',
])]
class TeamPost extends Model
{
    protected function casts(): array
    {
        return [
            'body_is_html' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TeamChannel::class, 'team_channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TeamPost::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TeamPost::class, 'parent_id')->orderBy('created_at');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(TeamPostReaction::class);
    }

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function hasBanner(): bool
    {
        return filled($this->subject) && filled($this->banner_bg);
    }

    public function plainBody(): string
    {
        if ($this->body_is_html) {
            return trim(html_entity_decode(strip_tags($this->body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $this->body;
    }

    /**
     * @return Collection<int, array{emoji: string, count: int, reacted: bool}>
     */
    public function reactionSummary(?int $viewerId = null): Collection
    {
        $viewerId ??= auth()->id();

        return $this->reactions
            ->groupBy('emoji')
            ->map(fn (Collection $group, string $emoji) => [
                'emoji' => $emoji,
                'count' => $group->count(),
                'reacted' => $viewerId !== null && $group->contains('user_id', $viewerId),
            ])
            ->sortByDesc('count')
            ->values();
    }
}
