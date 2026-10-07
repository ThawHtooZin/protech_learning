<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'course_id',
    'slip_path',
    'slip_disk',
    'status',
    'reviewed_by',
    'reviewed_at',
    'note',
])]
class CoursePurchase extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PurchaseStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === PurchaseStatus::Pending;
    }

    public function slipExists(): bool
    {
        return $this->slip_path !== ''
            && Storage::disk($this->slip_disk)->exists($this->slip_path);
    }
}
