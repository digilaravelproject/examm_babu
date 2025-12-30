<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            [
                'MSA', // question_type
                1,     // skill_id (Manually entered)
                1,     // topic_id (Manually entered)
                'What is the capital of India?',
                'Mumbai', 'Delhi', 'Kolkata', 'Chennai', '',
                '2', // Correct Answer (Option Index)
                'Solution text here.',
                1, 60, 'EASY',
                'Capital is New Delhi.',

            ]
        ];
    }

    public function headings(): array
    {
        return [
            'question_type',
            'skill_id',   // ADDED THIS
            'topic_id',
            'question',
            'option1', 'option2', 'option3', 'option4', 'option5',
            'correct_answer',
            'solution',
            'default_marks',
            'default_time_to_solve',
            'difficulty_level',
            'hint',
        ];
    }

    public function title(): string
    {
        return 'Questions_Upload_Here';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
