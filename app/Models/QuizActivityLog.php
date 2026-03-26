<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'quiz_id',
        'event_type',
        'occurred_at',
        'started_at',
        'ended_at',
        'attempt_id',
        'score_percent',
        'passed',
        'duration_seconds',
        'meta',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'passed' => 'bool',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }
}

