<?php

namespace App\Services;

use App\Models\LmsChapter;
use App\Models\LmsChapterCompletion;
use App\Models\LmsEnrollment;
use App\Models\LmsQuiz;

class LmsProgressService
{
    public function scoreQuiz(LmsQuiz $quiz, array $answers): array
    {
        $quiz->loadMissing('questions.options');
        $totalQuestions = $quiz->questions->count();
        if ($totalQuestions === 0) {
            return ['score' => 100, 'passed' => true];
        }

        $correctCount = 0;

        foreach ($quiz->questions as $question) {
            $selectedOptionId = $answers[$question->id] ?? null;
            if ($selectedOptionId) {
                $option = $question->options->firstWhere('id', $selectedOptionId);
                if ($option && $option->is_correct) {
                    $correctCount++;
                }
            }
        }

        $score = (int) round(($correctCount / $totalQuestions) * 100);
        $passed = $score >= $quiz->passing_score;

        return [
            'score' => $score,
            'passed' => $passed,
        ];
    }

    public function refreshChapterCompletion(LmsEnrollment $enrollment, LmsChapter $chapter): bool
    {
        $chapter->loadMissing(['lessons', 'quiz', 'assignments']);

        $allLessonsCompleted = true;
        foreach ($chapter->lessons as $lesson) {
            if (! $enrollment->lessonCompletions()->where('lesson_id', $lesson->id)->exists()) {
                $allLessonsCompleted = false;
                break;
            }
        }

        $allAssignmentsSubmitted = true;
        foreach ($chapter->assignments as $assignment) {
            if (! $enrollment->assignmentSubmissions()->where('assignment_id', $assignment->id)->exists()) {
                $allAssignmentsSubmitted = false;
                break;
            }
        }

        $quizPassed = true;
        if ($chapter->quiz) {
            $quizPassed = $enrollment->quizAttempts()
                ->where('quiz_id', $chapter->quiz->id)
                ->where('passed', true)
                ->exists();
        }

        if ($allLessonsCompleted && $allAssignmentsSubmitted && $quizPassed) {
            LmsChapterCompletion::query()->firstOrCreate([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id,
            ], [
                'completed_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    public function courseProgress(LmsEnrollment $enrollment): int
    {
        $enrollment->loadMissing('course.chapters.lessons', 'course.chapters.quiz', 'course.chapters.assignments');
        
        $totalItems = 0;
        
        foreach ($enrollment->course->chapters as $chapter) {
            $totalItems += $chapter->lessons->count();
            $totalItems += $chapter->assignments->count();
            if ($chapter->quiz) {
                $totalItems += 1;
            }
        }

        if ($totalItems === 0) {
            return 0;
        }

        $completedLessons = $enrollment->lessonCompletions()->count();
        $submittedAssignments = $enrollment->assignmentSubmissions()->count();
        $passedQuizzes = $enrollment->quizAttempts()->where('passed', true)->distinct('quiz_id')->count();
        
        $completedItems = $completedLessons + $submittedAssignments + $passedQuizzes;

        return (int) round(($completedItems / $totalItems) * 100);
    }

    public function calculateFinalGrade(LmsEnrollment $enrollment): int
    {
        $enrollment->loadMissing(['course.chapters.quiz', 'course.chapters.assignments', 'assignmentSubmissions', 'quizAttempts']);
        
        $scores = [];

        // Collect Quiz Scores (Highest score per quiz)
        foreach ($enrollment->course->chapters as $chapter) {
            if ($chapter->quiz) {
                $maxQuizScore = $enrollment->quizAttempts()
                    ->where('quiz_id', $chapter->quiz->id)
                    ->max('score');
                
                if ($maxQuizScore !== null) {
                    $scores[] = (int) $maxQuizScore;
                }
            }
        }

        // Collect Assignment Scores
        foreach ($enrollment->assignmentSubmissions as $submission) {
            if ($submission->score !== null) {
                $scores[] = (int) $submission->score;
            }
        }
        
        if (empty($scores)) {
            return 0;
        }

        return (int) round(array_sum($scores) / count($scores));
    }
}
