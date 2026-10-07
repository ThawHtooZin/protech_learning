<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * Production dump: Learn CSS — Step by Step (slug learn-css-step-by-step-usqf).
 * Advanced module exists in prod with no lessons yet — kept empty on purpose.
 *
 *   php artisan db:seed --class=CssCourseStructureSeeder
 */
class CssCourseStructureSeeder extends Seeder
{
    private const SLUG = 'learn-css-step-by-step-usqf';

    /** @var list<array{title: string, lessons: list<array{title: string, video_id: string, seconds: int}>}> */
    private const MODULES = [
        [
            'title' => 'Basic',
            'lessons' => [
                ['title' => 'CSS #1 where to and syntax', 'video_id' => 'LdpsMZ4gHeo', 'seconds' => 760],
            ],
        ],
        [
            'title' => 'Advanced',
            'lessons' => [],
        ],
    ];

    public function run(): void
    {
        if (Course::query()->where('slug', self::SLUG)->exists()) {
            $this->command?->warn('Course "'.self::SLUG.'" already exists — skipping CssCourseStructureSeeder.');

            return;
        }

        $course = Course::query()->create([
            'title' => 'Learn CSS — Step by Step',
            'slug' => self::SLUG,
            'description' => 'CSS Course. Basic and Advanced',
            'is_published' => true,
            'access_type' => 'private',
            'price_mmk' => null,
            'is_listed' => true,
        ]);

        foreach (self::MODULES as $mi => $modSpec) {
            $module = Module::query()->create([
                'course_id' => $course->id,
                'sort_order' => $mi + 1,
                'title' => $modSpec['title'],
            ]);

            foreach ($modSpec['lessons'] as $li => $spec) {
                Lesson::query()->create([
                    'module_id' => $module->id,
                    'sort_order' => $li + 1,
                    'title' => $spec['title'],
                    'video_driver' => 'youtube',
                    'video_ref' => $spec['video_id'],
                    'duration_seconds' => $spec['seconds'],
                    'documentation_markdown' => null,
                ]);
            }
        }

        $this->command?->info('Seeded Learn CSS ('.self::SLUG.').');
    }
}
