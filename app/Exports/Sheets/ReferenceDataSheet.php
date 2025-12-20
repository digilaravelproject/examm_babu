<?php

namespace App\Exports\Sheets;

use App\Models\Skill;
use App\Models\Topic;
use App\Models\QuestionType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReferenceDataSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // Data fetch karo
        $skills = Skill::select('id', 'name')->get();
        $topics = Topic::select('id', 'name', 'skill_id')->get();
        $types = QuestionType::select('code', 'name')->get();

        // Maximum rows count nikalo taaki loop chala sakein
        $maxCount = max($skills->count(), $topics->count(), $types->count());

        $data = [];

        for ($i = 0; $i < $maxCount; $i++) {
            $data[] = [
                // Skill Columns
                $skills[$i]->id ?? '',
                $skills[$i]->name ?? '',
                '', // Spacer
                // Topic Columns
                $topics[$i]->id ?? '',
                $topics[$i]->name ?? '',
                $topics[$i]->skill_id ?? '',
                '', // Spacer
                // Type Columns
                $types[$i]->code ?? '',
                $types[$i]->name ?? '',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'SKILL ID', 'SKILL NAME', '',
            'TOPIC ID', 'TOPIC NAME', 'PARENT SKILL ID', '',
            'TYPE CODE', 'TYPE NAME'
        ];
    }

    public function title(): string
    {
        return 'REFERENCE_IDS_DO_NOT_EDIT';
    }
}
