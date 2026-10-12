<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_course_catalog_page_loads(): void
    {
        $response = $this->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee(__('All courses'), false);
    }
}
