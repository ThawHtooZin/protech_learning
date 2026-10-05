<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs HTML course structure (if missing).
 *
 *   php artisan db:seed --class=HtmlCourseBootstrapSeeder
 */
class HtmlCourseBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(HtmlCourseStructureSeeder::class);
    }
}
