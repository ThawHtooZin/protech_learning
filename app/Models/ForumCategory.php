<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumCategory extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class)->latest('updated_at');
    }
}
