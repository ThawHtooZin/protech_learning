<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['assignment_id', 'user_id', 'status', 'feedback', 'submitted_at', 'returned_at', 'viewed_at'])]
class AssignmentSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
            'submitted_at' => 'datetime',
            'returned_at' => 'datetime',
            'viewed_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(AssignmentSubmissionFile::class);
    }

    /**
     * Teams-style roster status key for staff student-work table.
     *
     * @return 'returned'|'turned_in'|'turned_in_late'|'late'|'viewed'|'not_turned_in'
     */
    public static function workStatusKey(?self $submission, ?CarbonInterface $dueAt): string
    {
        $status = $submission?->status;
        $isPastDue = $dueAt !== null && $dueAt->isPast();

        if ($status === SubmissionStatus::Returned) {
            return 'returned';
        }

        if ($status === SubmissionStatus::Submitted) {
            $lateTurnIn = $dueAt !== null
                && $submission?->submitted_at !== null
                && $submission->submitted_at->gt($dueAt);

            return $lateTurnIn ? 'turned_in_late' : 'turned_in';
        }

        if ($isPastDue) {
            return 'late';
        }

        if ($submission?->viewed_at !== null) {
            return 'viewed';
        }

        return 'not_turned_in';
    }
}
