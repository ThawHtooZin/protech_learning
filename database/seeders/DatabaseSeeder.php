<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\ForumCategory;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Profile;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run multiple times: skips if demo admin already exists.
     * For a clean reset: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        if (User::query()->where('email', 'admin@example.com')->exists()) {
            $this->command?->warn('Demo users already present (admin@example.com). Skipping seed. Use migrate:fresh --seed to reset.');

            return;
        }

        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        Profile::query()->create([
            'user_id' => $admin->id,
            'handle' => 'admin',
            'display_name' => 'Admin',
        ]);

        $instructor = User::query()->create([
            'name' => 'Instructor',
            'email' => 'instructor@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Instructor,
        ]);
        Profile::query()->create([
            'user_id' => $instructor->id,
            'handle' => 'mentor',
            'display_name' => 'Instructor',
            'bio' => 'Community mentor badge.',
        ]);

        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);
        Profile::query()->create([
            'user_id' => $student->id,
            'handle' => 'learner',
            'display_name' => 'Student',
        ]);

        ForumCategory::query()->create([
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 1,
        ]);

        Tag::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        Tag::query()->create(['name' => 'Help', 'slug' => 'help']);

        $course = Course::query()->create([
            'title' => 'Laravel Foundations',
            'slug' => 'laravel-foundations',
            'description' => 'Sample course for local testing.',
            'is_published' => true,
        ]);

        $m1 = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
            'title' => 'Module 1 — Basics',
        ]);

        $m2 = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 2,
            'title' => 'Module 2 — Next steps',
        ]);

        $lesson1 = Lesson::query()->create([
            'module_id' => $m1->id,
            'sort_order' => 1,
            'title' => 'Welcome & setup',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => 600,
            'documentation_markdown' => "## Welcome\n\n```php\n<?php\necho 'Hello';\n```\n\nTry this **bold** text.",
        ]);

        Lesson::query()->create([
            'module_id' => $m1->id,
            'sort_order' => 2,
            'title' => 'Routes and controllers',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => 600,
            'documentation_markdown' => "## Routes\n\nDefine routes in `routes/web.php`.",
        ]);

        Lesson::query()->create([
            'module_id' => $m2->id,
            'sort_order' => 1,
            'title' => 'Going deeper',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => 600,
            'documentation_markdown' => "## Next\n\nUnlocks after module 1 quiz.",
        ]);

        $q1 = Question::query()->create([
            'technology' => 'Laravel',
            'topic' => 'Routing',
            'body' => 'Where do you typically define web routes?',
            'type' => 'mcq',
        ]);
        foreach ([
            ['routes/web.php', true],
            ['public/index.php', false],
            ['config/app.php', false],
        ] as $i => $row) {
            QuestionOption::query()->create([
                'question_id' => $q1->id,
                'body' => $row[0],
                'is_correct' => $row[1],
                'sort_order' => $i,
            ]);
        }

        $q2 = Question::query()->create([
            'technology' => 'Laravel',
            'topic' => 'Config',
            'body' => 'Which PHP version does this project target?',
            'type' => 'mcq',
        ]);
        foreach ([
            ['PHP 8.3+', true],
            ['PHP 5.6', false],
            ['PHP 7.0', false],
        ] as $i => $row) {
            QuestionOption::query()->create([
                'question_id' => $q2->id,
                'body' => $row[0],
                'is_correct' => $row[1],
                'sort_order' => $i,
            ]);
        }

        $lq = Quiz::query()->create([
            'lesson_id' => $lesson1->id,
            'module_id' => null,
            'title' => 'After video check',
            'pass_threshold_percent' => 70,
        ]);
        $lq->questions()->attach($q1->id, ['sort_order' => 0]);

        $mq = Quiz::query()->create([
            'lesson_id' => null,
            'module_id' => $m1->id,
            'title' => 'Module 1 assessment',
            'pass_threshold_percent' => 70,
        ]);
        $mq->questions()->attach($q2->id, ['sort_order' => 0]);

        $student->enrollments()->create(['course_id' => $course->id]);

        $this->call(HtmlCourseSeeder::class);
    }
}
