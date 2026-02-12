<?php

namespace App\Repositories;

use App\Models\Question;

class QuestionRepository
{
    /**
     * Question Configuration Steps (Wizard)
     * Handles dynamic route prefixes (admin vs instructor) to fix routing errors.
     *
     * @param null $qId
     * @param string $active
     * @return array[]
     */
    public function getSteps($qId = null, $active = 'details')
    {
        // Dynamically detect if we are in admin or instructor panel
        $prefix = request()->routeIs('instructor.*') ? 'instructor.' : 'admin.';

        return [
            [
                'step' => 1,
                'key' => 'details',
                'title' => __('Details'),
                'status' => $active == 'details' ? 'active' : 'inactive',
                'url' => $qId != null ? route($prefix . 'questions.edit', ['question' => $qId]) : ''
            ],
            [
                'step' => 2,
                'key' => 'settings',
                'title' => __('Settings'),
                'status' => $active == 'settings' ? 'active' : 'inactive',
                'url' => $qId != null ? route($prefix . 'questions.edit', ['question' => $qId, 'tab' => 'settings']) : ''
            ],
            [
                'step' => 3,
                'key' => 'solution',
                'title' => __('Solution'),
                'status' => $active == 'solution' ? 'active' : 'inactive',
                'url' => $qId != null ? route($prefix . 'questions.edit', ['question' => $qId, 'tab' => 'solution']) : ''
            ],
            [
                'step' => 4,
                'key' => 'attachment',
                'title' => __('Attachment'),
                'status' => $active == 'attachment' ? 'active' : 'inactive',
                'url' => $qId != null ? route($prefix . 'questions.edit', ['question' => $qId, 'tab' => 'attachment']) : ''
            ]
        ];
    }

    /**
     * Get Default Options Structure
     * Preserves 'partial_weightage' and 'pair' keys for backward compatibility.
     *
     * @param string $code
     * @return array
     */
    public function setDefaultOptions($code)
    {
        return match ($code) {
            'MSA', 'MMA', 'SAQ', 'ORD' => [
                ['option' => '', 'partial_weightage' => 0],
                ['option' => '', 'partial_weightage' => 0]
            ],
            'TOF' => [
                ['option' => 'True', 'partial_weightage' => 0],
                ['option' => 'False', 'partial_weightage' => 0]
            ],
            'MTF' => [
                ['option' => '', 'pair' => '', 'partial_weightage' => 0],
                ['option' => '', 'pair' => '', 'partial_weightage' => 0],
                ['option' => '', 'pair' => '', 'partial_weightage' => 0]
            ],
            // FIB options are generated dynamically from text, returning empty prevents errors
            'FIB' => [],
            default => [['option' => '']]
        };
    }

    /**
     * Get Default Preferences
     * Includes 'shuffle_options' logic as per your request.
     *
     * @param string $code
     * @return array
     */
    public function setDefaultPreferences($code)
    {
        return match ($code) {
            'FIB', 'SAQ' => [
                'case_sensitive' => false,
                'is_numeric' => false
            ],
            'LAQ' => [
                'word_limit' => false,
                'min_words' => 0,
                'max_words' => 100
            ],
            // Shuffle is standard for these types in your system
            'MSA', 'MMA', 'ORD', 'MTF' => [
                'shuffle_options' => true
            ],
            default => []
        };
    }

    /**
     * Get Default Answer Format
     *
     * @param string $code
     * @return array|string
     */
    public function setDefaultAnswers($code)
    {
        return match ($code) {
            'MMA' => [],
            default => ''
        };
    }

    /**
     * Check if Auto Evaluation is possible
     *
     * @param $questionType
     * @return bool
     */
    public function checkAutoEvaluationEligibility($questionType)
    {
        return $questionType !== 'LAQ';
    }

    /**
     * Evaluate User Answer
     * Relies on the Global Helper Functions (QuestionHelper.php)
     *
     * @param Question $question
     * @param $userAnswer
     * @return bool
     */
    public function evaluateAnswer(Question $question, $userAnswer)
    {
        // Ensure Helpers are loaded before calling
        if (!function_exists('validateMSA')) {
            return false; // Fail safe if helpers missing
        }

        return match ($question->questionType->code) {
            'MSA', 'TOF' => validateMSA($question->correct_answer, $userAnswer),
            'MMA' => validateMMA($question->correct_answer, $userAnswer),
            'FIB' => validateFIB($question->correct_answer, $userAnswer),
            'SAQ' => validateSAQ($question->options, $userAnswer),
            'ORD' => validateORD($question->options, $userAnswer),
            'MTF' => validateMTF($question->options, $userAnswer),
            default => false,
        };
    }

    /**
     * Format Correct Answer for Result Display
     *
     * @param Question $question
     * @param $userAnswer
     * @return mixed
     */
    public function formatCorrectAnswer(Question $question, $userAnswer)
    {
        $code = $question->questionType->code;

        if ($code === 'MTF' && function_exists('validateMTF')) {
            return validateMTF($question->options, $userAnswer, true);
        } elseif ($code === 'ORD' && function_exists('validateORD')) {
            return validateORD($question->options, $userAnswer, true);
        } elseif ($code === 'SAQ') {
            $options = [];
            foreach ($question->options as $option) {
                // Handle both Array and Object structure safely
                $val = is_array($option) ? ($option['option'] ?? '') : ($option->option ?? '');
                if ($val) array_push($options, $val);
            }
            return $options;
        } else {
            return $question->correct_answer;
        }
    }
}
