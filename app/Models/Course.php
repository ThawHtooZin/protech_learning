<?php

namespace App\Models;

use App\Enums\CourseAccessType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_path',
        'is_published',
        'access_type',
        'price_mmk',
        'is_listed',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_listed' => 'boolean',
            'access_type' => CourseAccessType::class,
            'price_mmk' => 'integer',
        ];
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_course')->withTimestamps();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CoursePurchase::class);
    }

    public function isPublic(): bool
    {
        return $this->access_type === CourseAccessType::Public;
    }

    public function isPrivate(): bool
    {
        return $this->access_type === CourseAccessType::Private;
    }

    public function isVisibleInCatalog(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if ($this->isPublic()) {
            return true;
        }

        return $this->is_listed;
    }

    public function coverUrl(): ?string
    {
        if (! $this->cover_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->cover_path)) {
            return null;
        }

        // Root-relative so it works on localhost:8000 / 127.0.0.1 / production host.
        return '/storage/'.$this->cover_path;
    }

    /** Plain-text excerpt for cards (rich HTML descriptions strip tags). */
    public function excerpt(int $limit = 140): ?string
    {
        if (! $this->description) {
            return null;
        }

        $plain = trim(html_entity_decode(strip_tags($this->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($plain === '') {
            return null;
        }

        return \Illuminate\Support\Str::limit($plain, $limit);
    }

    public function lessonCount(): int
    {
        return $this->orderedLessons()->count();
    }

    public function totalDurationSeconds(): int
    {
        return (int) $this->orderedLessons()->sum('duration_seconds');
    }

    /** @return Collection<int, Lesson> */
    public function orderedLessons(): Collection
    {
        $lessons = new Collection;
        foreach ($this->modules()->orderBy('sort_order')->get() as $module) {
            foreach ($module->lessons()->orderBy('sort_order')->get() as $lesson) {
                $lessons->push($lesson);
            }
        }

        return $lessons;
    }
}
