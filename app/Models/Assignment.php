<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'created_by', 'title', 'instructions', 'due_at', 'status'])]
class Assignment extends Model
{
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'status' => AssignmentStatus::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isOpen(): bool
    {
        return $this->status === AssignmentStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === AssignmentStatus::Closed;
    }
}
