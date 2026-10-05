<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LessonSequenceGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_user_can_open_any_lesson(): void
    {
        $user = User::query()->create([
            'email' => 'strict@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        $course = Course::query()->create([
            'title' => 'Seq Course',
            'slug' => 'seq-course',
            'description' => null,
            'is_published' => true,
        ]);
        $module = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
            'title' => 'M1',
        ]);
        $lesson1 = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'Lesson 1',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);
        $lesson2 = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 2,
            'title' => 'Lesson 2',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        Enrollment::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($user)->get(route('lessons.show', $lesson1))->assertOk();
        $this->actingAs($user)->get(route('lessons.show', $lesson2))->assertOk();
    }

    public function test_lesson_page_requires_course_enrollment(): void
    {
        $user = User::query()->create([
            'email' => 'noenroll@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        $course = Course::query()->create([
            'title' => 'Gated',
            'slug' => 'gated',
            'description' => null,
            'is_published' => true,
        ]);
        $module = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
            'title' => 'M1',
        ]);
        $lesson = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'Only with enrollment',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        $this->actingAs($user)->get(route('lessons.show', $lesson))->assertForbidden();
    }
}
