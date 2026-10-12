<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    protected $fillable = [
        'user_id',
        'handle',
        'display_name',
        'bio',
        'location',
        'avatar_path',
        'social_links',
    ];

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->avatar_path)) {
            return null;
        }

        // Root-relative so it works on localhost:8000 / 127.0.0.1 / production host.
        return '/storage/'.$this->avatar_path;
    }

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
