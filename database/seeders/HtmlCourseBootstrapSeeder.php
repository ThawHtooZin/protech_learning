<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs HTML course structure (if missing) then lesson quizzes.
 *
 *   php artisan db:seed --class=HtmlCourseBootstrapSeeder
 */
class HtmlCourseBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(HtmlCourseStructureSeeder::class);
        $this->call(HtmlLessonQuizzesSeeder::class);
    }
}
