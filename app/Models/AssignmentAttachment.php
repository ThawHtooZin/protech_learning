<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assignment_id', 'original_name', 'disk', 'path', 'mime_type', 'size_bytes'])]
class AssignmentAttachment extends Model
{
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
