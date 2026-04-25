<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuestionBankAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::query()->create([
            'name' => 'Admin',
            'email' => 'qbank-admin@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $u->forceFill(['approved_at' => now()])->save();
        $this->actingAs($u);

        return $u;
    }

    public function test_index_search_and_pagination(): void
    {
        $this->admin();

        Question::query()->create([
            'technology' => 'AlphaTech',
            'topic' => 'Intro',
            'body' => 'Unique zebra phrase for search',
            'type' => 'mcq',
        ]);
        Question::query()->create([
            'technology' => 'BetaTech',
            'topic' => 'Other',
            'body' => 'Something else entirely',
            'type' => 'mcq',
        ]);

        $this->get(route('admin.questions.index', ['q' => 'zebra']))
            ->assertOk()
            ->assertSee('Unique zebra phrase', false)
            ->assertDontSee('Something else entirely', false);

        $this->get(route('admin.questions.index', ['technology' => 'BetaTech']))
            ->assertOk()
            ->assertSee('Something else entirely', false)
            ->assertDontSee('Unique zebra phrase', false);
    }

    public function test_update_with_lesson_quiz_return_redirects_to_quiz_edit(): void
    {
        $this->admin();

        $course = Course::query()->create([
            'title' => 'C',
            'slug' => 'c-q',
            'description' => null,
            'is_published' => true,
        ]);
        $module = Module::query()->create(['course_id' => $course->id, 'sort_order' => 1, 'title' => 'M']);
        $lesson = Lesson::query()->create([
            'module_id' => $module->id,
            'sort_order' => 1,
            'title' => 'L',
            'video_driver' => 'youtube',
            'video_ref' => 'x',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        $q = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'T',
            'body' => 'Stem',
            'type' => 'mcq',
        ]);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'A', 'is_correct' => true, 'sort_order' => 0]);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'B', 'is_correct' => false, 'sort_order' => 1]);

        $payload = [
            'technology' => 'HTML',
            'topic' => 'T',
            'body' => 'Stem updated',
            'options' => ['A', 'B', '', '', '', ''],
            'correct_index' => 0,
            'return_context' => 'lesson_quiz',
            'return_course_id' => (string) $course->id,
            'return_module_id' => (string) $module->id,
            'return_lesson_id' => (string) $lesson->id,
        ];

        $this->put(route('admin.questions.update', $q), $payload)
            ->assertRedirect(route('admin.quizzes.lesson.edit', [$course, $module, $lesson]));

        $this->assertSame('Stem updated', $q->fresh()->body);
    }
}
