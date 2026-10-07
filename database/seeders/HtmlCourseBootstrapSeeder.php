<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs HTML course structure (if missing).
 *
 *   php artisan db:seed --class=HtmlCourseBootstrapSeeder
 *
 * For all production courses (HTML + Learn2Success + CSS), use:
 *   php artisan db:seed --class=ProductionCoursesBootstrapSeeder
 */
class HtmlCourseBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(HtmlCourseStructureSeeder::class);
    }
}
