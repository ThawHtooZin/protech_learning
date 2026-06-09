<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_save_video_progress(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student, 'approved_at' => now()]);
        $course = Course::query()->create(['title' => 'C', 'slug' => 'prog', 'is_published' => true]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $lesson = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'L',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => 100,
        ]);
        Enrollment::query()->create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)
            ->postJson(route('lessons.progress', $lesson), ['position_seconds' => 42])
            ->assertOk()
            ->assertJson(['ok' => true, 'position_seconds' => 42]);

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'last_position_seconds' => 42,
            'started' => true,
        ]);
    }

    public function test_guest_cannot_save_progress(): void
    {
        $course = Course::query()->create(['title' => 'C', 'slug' => 'prog2', 'is_published' => true]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $lesson = Lesson::query()->create(['module_id' => $module->id, 'sort_order' => 1, 'title' => 'L', 'video_driver' => 'youtube', 'video_ref' => 'dQw4w9WgXcQ']);

        $this->postJson(route('lessons.progress', $lesson), ['position_seconds' => 10])->assertUnauthorized();
    }
}
