<?php

namespace App\Repositories;

class QuestionRepository
{
    /**
     * Get Default Options Structure based on Type
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
                ['option' => '', 'pair' => '', 'partial_weightage' => 0]
            ],
            default => ['']
        };
    }

    /**
     * Get Default Preferences
     */
    public function setDefaultPreferences($code)
    {
        return match ($code) {
            'FIB', 'SAQ' => ['case_sensitive' => false, 'is_numeric' => false],
            'LAQ'        => ['word_limit' => false, 'min_words' => 0, 'max_words' => 100],
            default      => []
        };
    }
}
