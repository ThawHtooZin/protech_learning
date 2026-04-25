<?php

namespace Tests\Feature;

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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLessonQuizManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAdmin(): User
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-quiz-mgmt@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);
        $admin->forceFill(['approved_at' => now()])->save();
        $this->actingAs($admin);

        return $admin;
    }

    public function test_store_then_update_reorders_questions(): void
    {
        $this->actingAdmin();

        $course = Course::query()->create([
            'title' => 'Quiz UX Course',
            'slug' => 'quiz-ux-course',
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
            'title' => 'L1',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        $q1 = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'A',
            'body' => 'Q1',
            'type' => 'mcq',
        ]);
        $q2 = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'B',
            'body' => 'Q2',
            'type' => 'mcq',
        ]);
        foreach ([$q1, $q2] as $q) {
            QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'Yes', 'is_correct' => true, 'sort_order' => 0]);
        }

        $store = $this->post(route('admin.quizzes.lesson.store', [$course, $module, $lesson]), [
            'title' => 'First title',
            'pass_threshold_percent' => 70,
            'question_ids' => [$q1->id, $q2->id],
        ]);
        $store->assertRedirect(route('admin.quizzes.lesson.edit', [$course, $module, $lesson]));

        $quiz = Quiz::query()->where('lesson_id', $lesson->id)->with('questions')->first();
        $this->assertNotNull($quiz);
        $this->assertSame([$q1->id, $q2->id], $quiz->questions->pluck('id')->all());

        $update = $this->put(route('admin.quizzes.lesson.update', [$course, $module, $lesson]), [
            'title' => 'Updated title',
            'pass_threshold_percent' => 80,
            'question_ids' => [$q2->id, $q1->id],
        ]);
        $update->assertRedirect(route('admin.quizzes.lesson.edit', [$course, $module, $lesson]));

        $quiz->refresh();
        $quiz->load('questions');
        $this->assertSame('Updated title', $quiz->title);
        $this->assertSame(80, $quiz->pass_threshold_percent);
        $this->assertSame([$q2->id, $q1->id], $quiz->questions->pluck('id')->all());
    }

    public function test_cannot_store_second_quiz_on_same_lesson(): void
    {
        $this->actingAdmin();

        $course = Course::query()->create([
            'title' => 'Dup Quiz',
            'slug' => 'dup-quiz',
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
            'title' => 'L1',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        $q = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'A',
            'body' => 'Only',
            'type' => 'mcq',
        ]);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'Yes', 'is_correct' => true, 'sort_order' => 0]);

        $this->post(route('admin.quizzes.lesson.store', [$course, $module, $lesson]), [
            'title' => 'One',
            'pass_threshold_percent' => 70,
            'question_ids' => [$q->id],
        ])->assertRedirect(route('admin.quizzes.lesson.edit', [$course, $module, $lesson]));

        $dup = $this->post(route('admin.quizzes.lesson.store', [$course, $module, $lesson]), [
            'title' => 'Two',
            'pass_threshold_percent' => 70,
            'question_ids' => [$q->id],
        ]);
        $dup->assertRedirect(route('admin.quizzes.lesson.edit', [$course, $module, $lesson]));
        $this->assertSame(1, Quiz::query()->where('lesson_id', $lesson->id)->count());
    }

    public function test_destroy_removes_quiz_and_resets_lesson_progress_quiz_passed(): void
    {
        $admin = $this->actingAdmin();

        $course = Course::query()->create([
            'title' => 'Del Quiz',
            'slug' => 'del-quiz',
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
            'title' => 'L1',
            'video_driver' => 'youtube',
            'video_ref' => 'dQw4w9WgXcQ',
            'duration_seconds' => null,
            'documentation_markdown' => null,
        ]);

        $q = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'A',
            'body' => 'Only',
            'type' => 'mcq',
        ]);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => 'Yes', 'is_correct' => true, 'sort_order' => 0]);

        $this->post(route('admin.quizzes.lesson.store', [$course, $module, $lesson]), [
            'title' => 'T',
            'pass_threshold_percent' => 70,
            'question_ids' => [$q->id],
        ]);

        LessonProgress::query()->create([
            'user_id' => $admin->id,
            'lesson_id' => $lesson->id,
            'last_position_seconds' => 0,
            'started' => true,
            'watched' => true,
            'quiz_passed' => true,
        ]);

        $this->delete(route('admin.quizzes.lesson.destroy', [$course, $module, $lesson]))
            ->assertRedirect(route('admin.courses.edit', $course));

        $this->assertNull(Quiz::query()->where('lesson_id', $lesson->id)->first());
        $this->assertFalse((bool) LessonProgress::query()->where('lesson_id', $lesson->id)->value('quiz_passed'));
    }
}
