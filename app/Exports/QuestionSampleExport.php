<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\QuestionTemplateSheet;
use App\Exports\Sheets\ReferenceDataSheet;

class QuestionSampleExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new QuestionTemplateSheet(), // Sheet 1: Form
            new ReferenceDataSheet(),    // Sheet 2: IDs List
        ];
    }
}
