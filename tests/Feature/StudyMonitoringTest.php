<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonActivityLog;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudyMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_lesson_open_is_recorded(): void
    {
        $user = User::query()->create([
            'email' => 'student@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);
        $user->forceFill(['approved_at' => now()])->save();

        $course = Course::query()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => null,
            'is_published' => true,
        ]);
        $module = Module::query()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
            'title' => 'Module 1',
        ]);
        $lesson = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'Lesson 1',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        Enrollment::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($user)->get(route('lessons.show', $lesson))->assertOk();

        $this->assertTrue(LessonActivityLog::query()->where('user_id', $user->id)->where('event_type', 'lesson_opened')->exists());
    }
}
