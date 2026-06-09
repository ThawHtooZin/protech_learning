<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reorder_modules_and_lessons(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $course = Course::query()->create(['title' => 'C', 'slug' => 'reorder-c', 'is_published' => true]);
        $m1 = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M1']);
        $m2 = Module::query()->create(['course_id' => $course->id, 'sort_order' => 2, 'title' => 'M2']);
        $l1 = Lesson::query()->create(['module_id' => $m1->id, 'sort_order' => 1, 'title' => 'A', 'video_driver' => 'youtube', 'video_ref' => 'abc12345678']);
        $l2 = Lesson::query()->create(['module_id' => $m1->id, 'sort_order' => 2, 'title' => 'B', 'video_driver' => 'youtube', 'video_ref' => 'abc12345678']);

        $this->actingAs($admin)
            ->putJson(route('admin.modules.reorder', $course), ['module_ids' => [$m2->id, $m1->id]])
            ->assertOk();

        $this->assertEquals(1, $m2->fresh()->sort_order);
        $this->assertEquals(2, $m1->fresh()->sort_order);

        $this->actingAs($admin)
            ->putJson(route('admin.lessons.reorder', [$course, $m1]), ['lesson_ids' => [$l2->id, $l1->id]])
            ->assertOk();

        $this->assertEquals(1, $l2->fresh()->sort_order);
        $this->assertEquals(2, $l1->fresh()->sort_order);
    }
}
