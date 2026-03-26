<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\LessonActivityLog;
use App\Models\QuizActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudyMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_lesson_open_and_quiz_submit_are_recorded(): void
    {
        $user = User::query()->create([
            'name' => 'Student',
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

        $q = Question::query()->create([
            'technology' => 'HTML',
            'topic' => 'Basics',
            'body' => '1+1?',
            'type' => 'mcq',
        ]);
        $optA = QuestionOption::query()->create(['question_id' => $q->id, 'body' => '2', 'is_correct' => true, 'sort_order' => 0]);
        QuestionOption::query()->create(['question_id' => $q->id, 'body' => '3', 'is_correct' => false, 'sort_order' => 1]);

        $quiz = Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'module_id' => null,
            'title' => 'Check',
            'pass_threshold_percent' => 70,
        ]);
        $quiz->questions()->attach($q->id, ['sort_order' => 0]);

        $this->actingAs($user)->get(route('lessons.show', $lesson))->assertOk();
        $this->actingAs($user)->get(route('quizzes.show', $quiz))->assertOk();
        $this->actingAs($user)->post(route('quizzes.store', $quiz), [
            'answers' => [$q->id => $optA->id],
        ])->assertRedirect();

        $this->assertTrue(LessonActivityLog::query()->where('user_id', $user->id)->where('event_type', 'lesson_opened')->exists());
        $this->assertTrue(QuizActivityLog::query()->where('user_id', $user->id)->where('event_type', 'quiz_started')->exists());
    }
}

