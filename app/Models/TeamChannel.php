<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['team_id', 'name', 'slug', 'is_general', 'sort_order'])]
class TeamChannel extends Model
{
    protected function casts(): array
    {
        return [
            'is_general' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(TeamPost::class);
    }

    public static function makeSlug(string $name): string
    {
        $slug = Str::slug($name);

        return $slug !== '' ? $slug : 'channel';
    }
}
