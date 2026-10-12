<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Team extends Model
{
    protected static function booted(): void
    {
        static::created(function (Team $team): void {
            $team->channels()->create([
                'name' => 'General',
                'slug' => 'general',
                'is_general' => true,
                'sort_order' => 0,
            ]);
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(TeamPost::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(TeamChannel::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'team_course')->withTimestamps();
    }

    public function generalChannel(): ?TeamChannel
    {
        if ($this->relationLoaded('channels')) {
            return $this->channels->firstWhere('is_general', true);
        }

        return $this->channels()->where('is_general', true)->first();
    }
}
