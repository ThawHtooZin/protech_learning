<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(ForumThread::class, 'forum_thread_tag');
    }
}
