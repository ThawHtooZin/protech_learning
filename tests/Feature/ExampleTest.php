<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_course_catalog(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('courses.index'));
    }

    public function test_course_catalog_page_loads(): void
    {
        $response = $this->get(route('courses.index'));

        $response->assertOk();
    }
}
