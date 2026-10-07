<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * Production dump: Learn2Success course (slug learn2success-48lU).
 *
 *   php artisan db:seed --class=Learn2SuccessCourseSeeder
 */
class Learn2SuccessCourseSeeder extends Seeder
{
    private const SLUG = 'learn2success-48lU';

    /** @var list<array{title: string, lessons: list<array{title: string, video_id: string, seconds: int}>}> */
    private const MODULES = [
        [
            'title' => 'All Features Usage',
            'lessons' => [
                ['title' => 'Learn2Success Video1', 'video_id' => 'vHx1Q1_f3fY', 'seconds' => 126],
                ['title' => 'Learn2Success Video2', 'video_id' => 'lCB498RuNyI', 'seconds' => 149],
                ['title' => 'Learn2Success Video3', 'video_id' => 'EqGoK0fIlwc', 'seconds' => 194],
                ['title' => 'Learn2Success Video4', 'video_id' => 'iUp5HNRNO8w', 'seconds' => 186],
                ['title' => 'Learn2Success Video5', 'video_id' => 'UgNqSS4_hiQ', 'seconds' => 183],
                ['title' => 'Learn2Success Video6+7', 'video_id' => 'T8BvZRJt3Pk', 'seconds' => 390],
                ['title' => 'Learn2Success Video8', 'video_id' => 'QwKP9rUbfO4', 'seconds' => 274],
                ['title' => 'Learn2Success Video9', 'video_id' => 'A1PhzxvcNf0', 'seconds' => 335],
                ['title' => 'Learn2Success Video10', 'video_id' => 'z7c-8igepE0', 'seconds' => 441],
            ],
        ],
    ];

    public function run(): void
    {
        if (Course::query()->where('slug', self::SLUG)->exists()) {
            $this->command?->warn('Course "'.self::SLUG.'" already exists — skipping Learn2SuccessCourseSeeder.');

            return;
        }

        $course = Course::query()->create([
            'title' => 'Learn2Success',
            'slug' => self::SLUG,
            'description' => 'How to use Learn2Success',
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

        $this->command?->info('Seeded Learn2Success ('.self::SLUG.').');
    }
}
