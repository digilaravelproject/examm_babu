<?php

namespace App\Exports\Sheets;

use App\Models\Topic;
use App\Models\QuestionType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReferenceDataSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function collection()
    {
        // Data fetch (Skill hata diya)
        $topics = Topic::select('id', 'name')->get();
        $types  = QuestionType::select('code', 'name')->get();

        $maxCount = max($topics->count(), $types->count());
        $data = [];

        for ($i = 0; $i < $maxCount; $i++) {
            $data[] = [
                // Topic Data
                $topics[$i]->id ?? '',
                $topics[$i]->name ?? '',
                '', // Spacer
                // Type Data
                $types[$i]->code ?? '',
                $types[$i]->name ?? '',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'TOPIC ID', 'TOPIC NAME', '', 
            'TYPE CODE', 'TYPE NAME'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('1:1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string
    {
        return 'REFERENCE_IDS';
    }
}