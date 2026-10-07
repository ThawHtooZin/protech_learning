<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds all courses present in the production dump (HTML, Learn2Success, CSS).
 *
 *   php artisan db:seed --class=ProductionCoursesBootstrapSeeder
 */
class ProductionCoursesBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HtmlCourseStructureSeeder::class,
            Learn2SuccessCourseSeeder::class,
            CssCourseStructureSeeder::class,
        ]);
    }
}
