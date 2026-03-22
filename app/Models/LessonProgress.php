<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'last_position_seconds',
        'started',
        'watched',
        'quiz_passed',
        'last_checkpoint_at',
    ];

    protected function casts(): array
    {
        return [
            'started' => 'boolean',
            'watched' => 'boolean',
            'quiz_passed' => 'boolean',
            'last_checkpoint_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
