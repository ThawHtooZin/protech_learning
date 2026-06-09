<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_lesson_and_renumber_sort_order(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $course = Course::query()->create(['title' => 'C', 'slug' => 'c', 'is_published' => true]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $l1 = Lesson::query()->create(['module_id' => $module->id, 'sort_order' => 1, 'title' => 'L1', 'video_driver' => 'youtube', 'video_ref' => 'abc12345678']);
        $l2 = Lesson::query()->create(['module_id' => $module->id, 'sort_order' => 2, 'title' => 'L2', 'video_driver' => 'youtube', 'video_ref' => 'abc12345678']);

        $q = Question::query()->create(['technology' => 'T', 'topic' => 'T', 'body' => 'Q?', 'type' => 'mcq']);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'A', 'is_correct' => true, 'sort_order' => 0]);
        $quiz = Quiz::query()->create(['lesson_id' => $l1->id, 'title' => 'Q', 'pass_threshold_percent' => 70]);
        $quiz->questions()->attach($q->id, ['sort_order' => 0]);

        $student = User::factory()->create(['role' => UserRole::Student, 'approved_at' => now()]);
        LessonProgress::query()->create(['user_id' => $student->id, 'lesson_id' => $l1->id, 'quiz_passed' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.lessons.destroy', [$course, $module, $l1]))
            ->assertRedirect(route('admin.courses.edit', $course));

        $this->assertDatabaseMissing('lessons', ['id' => $l1->id]);
        $this->assertDatabaseMissing('lesson_progress', ['lesson_id' => $l1->id]);
        $this->assertEquals(1, $l2->fresh()->sort_order);
    }

    public function test_student_cannot_delete_lesson(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student, 'approved_at' => now()]);
        $course = Course::query()->create(['title' => 'C', 'slug' => 'c2', 'is_published' => true]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $lesson = Lesson::query()->create(['module_id' => $module->id, 'sort_order' => 1, 'title' => 'L', 'video_driver' => 'youtube', 'video_ref' => 'abc12345678']);

        $this->actingAs($user)
            ->delete(route('admin.lessons.destroy', [$course, $module, $lesson]))
            ->assertForbidden();
    }
}
