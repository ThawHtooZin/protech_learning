<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'user_id', 'parent_id', 'body'])]
class TeamPost extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
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

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }
}
