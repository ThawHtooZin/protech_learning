<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleQuizAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_and_delete_module_quiz(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'approved_at' => now()]);
        $course = Course::query()->create(['title' => 'C', 'slug' => 'mq', 'is_published' => true]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $q1 = Question::query()->create(['technology' => 'T', 'topic' => 'T', 'body' => 'Q1', 'type' => 'mcq']);
        $q2 = Question::query()->create(['technology' => 'T', 'topic' => 'T', 'body' => 'Q2', 'type' => 'mcq']);
        QuestionOption::query()->create(['question_id' => $q1->id, 'body' => 'A', 'is_correct' => true, 'sort_order' => 0]);
        QuestionOption::query()->create(['question_id' => $q2->id, 'body' => 'B', 'is_correct' => true, 'sort_order' => 0]);
        $quiz = Quiz::query()->create(['module_id' => $module->id, 'title' => 'Recap', 'pass_threshold_percent' => 70]);
        $quiz->questions()->attach($q1->id, ['sort_order' => 0]);

        $this->actingAs($admin)
            ->put(route('admin.quizzes.module.update', [$course, $module]), [
                'title' => 'Updated recap',
                'pass_threshold_percent' => 80,
                'question_ids' => [$q2->id],
            ])
            ->assertRedirect(route('admin.quizzes.module.edit', [$course, $module]));

        $quiz->refresh();
        $this->assertEquals('Updated recap', $quiz->title);
        $this->assertEquals(80, $quiz->pass_threshold_percent);
        $this->assertTrue($quiz->questions->contains('id', $q2->id));

        $this->actingAs($admin)
            ->delete(route('admin.quizzes.module.destroy', [$course, $module]))
            ->assertRedirect(route('admin.courses.edit', $course));

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }
}
