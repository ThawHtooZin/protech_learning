<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['team_post_id', 'user_id', 'emoji'])]
class TeamPostReaction extends Model
{
    /** @var list<string> */
    public const ALLOWED = ['👍', '❤️', '😂', '😮', '😢', '🎉', '👏', '🔥'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TeamPost::class, 'team_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
