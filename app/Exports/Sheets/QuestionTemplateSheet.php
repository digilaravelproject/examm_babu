<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color; // Yeh import zaroori hai color ke liye

class QuestionTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function array(): array
    {
        return [
            // Row 2: Instruction Row (Red Color me dikhega)
            [
                'MSA',                                 // question_type
                1,                                     // topic_id
                'Write Question Here (REQUIRED)',      // question
                'Option 1 (REQUIRED)',                 // option1
                'Option 2 (REQUIRED)',                 // option2
                'Option 3 (REQUIRED)',                 // option3
                'Option 4 (REQUIRED)',                 // option4
                '',                                    // option5 (Optional)
                '1',                                   // correct_answer (Index)
                'Solution Text (REQUIRED)',            // solution
                1,                                     // default_marks
                60,                                    // default_time_to_solve
                'EASY',                                // difficulty_level
                'Hint (Optional)',                     // hint
            ],
            // Row 3: Sample 1
            [
                'MSA',
                1,
                'What is the capital of India?',
                'Mumbai', 'Delhi', 'Kolkata', 'Chennai', '',
                '2',
                'New Delhi is the capital of India.',
                1, 60, 'EASY',
                'It starts with D.',
            ],
            // Row 4: Sample 2
            [
                'MSA',
                1,
                'What is the chemical formula of Water?',
                'CO2', 'H2O', 'O2', 'NaCl', '',
                '2',
                'H2O stands for Water (2 Hydrogen, 1 Oxygen).',
                1, 60, 'MEDIUM',
                'Universal solvent.',
            ],
            // Row 5: Sample 3
            [
                'MSA',
                1,
                'What is 15 + 5?',
                '10', '15', '20', '25', '',
                '3',
                '15 plus 5 equals 20.',
                1, 45, 'EASY',
                'Simple addition.',
            ],
            // Row 6: Sample 4 (MMA)
            [
                'MMA',
                1,
                'Select all prime numbers below 5.',
                '2', '3', '4', '6', '',
                '1,2',
                '2 and 3 are prime numbers.',
                2, 90, 'HARD',
                'Numbers divisible only by 1 and themselves.',
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'question_type',
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
        // 1. Text Format for all columns (A to N)
        $sheet->getStyle('A:N')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        
        // 2. Header Row Bold (Black)
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // 3. Highlight Mandatory Fields in RED for Row 2 only
        // Columns C to G (Question + Options 1-4)
        $sheet->getStyle('C2:G2')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        
        // Column J (Solution)
        $sheet->getStyle('J2')->getFont()->getColor()->setARGB(Color::COLOR_RED);

        return [];
    }
}