<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * One-off: creates the Learn HTML course with 14 lessons (HTML #1–#14) in teaching order.
 * Slug: html-complete (stable for seeds).
 *
 *   php artisan db:seed --class=HtmlCourseStructureSeeder
 *
 * Skips if a course with slug "html-complete" already exists.
 */
class HtmlCourseStructureSeeder extends Seeder
{
    private const SLUG = 'html-complete';

    /** @var list<array{title: string, lessons: list<array{title: string, video_id: string, seconds: int}>}> */
    private const MODULES = [
        [
            'title' => 'Module 1 — Introduction & document shape',
            'lessons' => [
                ['title' => 'HTML #1 – Intro & Environment Setup', 'video_id' => '-zUNKAdENMw', 'seconds' => 109],
                ['title' => 'HTML #2 – Basic Frame & Names', 'video_id' => '-9VeILb9z1I', 'seconds' => 529],
                ['title' => 'HTML #3 - Header Tag', 'video_id' => 'UO0Dzc-8Bvc', 'seconds' => 264],
            ],
        ],
        [
            'title' => 'Module 2 — Text & classic presentation',
            'lessons' => [
                ['title' => 'HTML #4 - Bold, Italic, Underline', 'video_id' => 'qpYMJ-Rz874', 'seconds' => 279],
                ['title' => 'HTML #5 - Center, font size and color', 'video_id' => 'R-qPtE3b07E', 'seconds' => 724],
                ['title' => 'HTML #6 - bgcolor, hr color and size, p and pre', 'video_id' => 'sqthG6ZmPOM', 'seconds' => 930],
            ],
        ],
        [
            'title' => 'Module 3 — Lists, alignment & marquee',
            'lessons' => [
                ['title' => 'HTML #7 List', 'video_id' => 'fSeR-RO9p90', 'seconds' => 332],
                ['title' => 'HTML #8 Alignment Attribute', 'video_id' => 'Pb5y0Ncj9LM', 'seconds' => 196],
                ['title' => 'HTML #9 Marquee', 'video_id' => '48tFuPiXmXI', 'seconds' => 685],
            ],
        ],
        [
            'title' => 'Module 4 — Images, links & tables',
            'lessons' => [
                ['title' => 'HTML #10 Image', 'video_id' => 'Ne-ELosUWnQ', 'seconds' => 516],
                ['title' => 'HTML #11 A Link', 'video_id' => 'OcXllp35riM', 'seconds' => 527],
                ['title' => 'HTML #12 Table', 'video_id' => 'dws6m7WQzVI', 'seconds' => 538],
            ],
        ],
        [
            'title' => 'Module 5 — Forms & embeds',
            'lessons' => [
                ['title' => 'HTML #13 Form', 'video_id' => 'TKbixMH598I', 'seconds' => 1228],
                ['title' => 'HTML #14 IFrame', 'video_id' => 'hYUXfyj8aRQ', 'seconds' => 718],
            ],
        ],
    ];

    public function run(): void
    {
        if (Course::query()->where('slug', self::SLUG)->exists()) {
            $this->command?->warn('Course "'.self::SLUG.'" already exists — skipping HtmlCourseStructureSeeder.');

            return;
        }

        $course = Course::query()->create([
            'title' => 'Learn HTML — Step by Step',
            'slug' => self::SLUG,
            'description' => 'HTML #1–#14 in order; lesson quizzes unlock progress. Playlist videos are unlisted-friendly (embed by ID).',
            'is_published' => true,
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

        $this->command?->info('Created course "'.self::SLUG.'" — 5 modules, 14 lessons (HTML #1–#14).');
    }
}
