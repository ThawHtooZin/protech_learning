<?php

namespace App\Services;

use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuizGradingService
{
    /**
     * @param  array<int|string, int|string>  $answers question_id => selected_option_id
     */
    public function grade(User $user, Quiz $quiz, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($user, $quiz, $answers) {
            $quiz->load('questions.options');

            $total = $quiz->questions->count();
            $correct = 0;

            $attempt = QuizAttempt::query()->create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score_percent' => 0,
                'passed' => false,
            ]);

            foreach ($quiz->questions as $question) {
                $selectedId = isset($answers[$question->id]) ? (int) $answers[$question->id] : null;
                $selected = $selectedId
                    ? $question->options->firstWhere('id', $selectedId)
                    : null;
                $isCorrect = $selected && $selected->is_correct;
                if ($isCorrect) {
                    $correct++;
                }

                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'selected_option_id' => $selected?->id,
                    'is_correct' => (bool) $isCorrect,
                ]);
            }

            $scorePercent = $total > 0 ? (int) round(100 * $correct / $total) : 0;
            // Lesson quizzes: any submission unlocks progress (no pass/fail gate). Module recaps: threshold gates next module.
            $isLessonQuiz = (bool) $quiz->lesson_id;
            $passed = $isLessonQuiz
                ? true
                : ($scorePercent >= (int) $quiz->pass_threshold_percent);

            $attempt->update([
                'score_percent' => $scorePercent,
                'passed' => $passed,
            ]);

            if ($quiz->lesson_id) {
                $progress = LessonProgress::query()->firstOrCreate(
                    ['user_id' => $user->id, 'lesson_id' => $quiz->lesson_id],
                    ['last_position_seconds' => 0]
                );
                $progress->started = true;
                $progress->watched = true;
                $progress->quiz_passed = true;
                $progress->last_checkpoint_at = now();
                $progress->save();
            }

            return $attempt->fresh(['answers']);
        });
    }
}
