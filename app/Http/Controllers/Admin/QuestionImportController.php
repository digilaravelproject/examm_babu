<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\QuestionsImport;
use App\Exports\QuestionSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;

class QuestionImportController extends Controller
{
    public function showImportForm() {
        return view('admin.questions.import');
    }

    public function downloadSample() {
        return Excel::download(new QuestionSampleExport, 'question_import_sample.xlsx');
    }

    public function import(Request $request) {
        $request->validate(['excel_file' => 'required|mimes:xlsx,csv,xls']);

        // 1. File ko save karo taaki queue worker use access kar sake
        $file = $request->file('excel_file');
        $fileName = 'import_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('temp', $fileName); // storage/app/temp/..

        // 2. Batch Create karo
        $batch = Bus::batch([])->name('Import Questions')->dispatch();

        // 3. Excel Queue Import ko Batch ke sath link karo
        // Chain: File uthao -> Batch me daalo
        Excel::queueImport(new QuestionsImport(Auth::id()), $filePath)
            ->withBatch($batch); // Yeh magic method hai Maatwebsite ka

        // 4. Batch ID return karo frontend ko
        return response()->json([
            'success' => true,
            'batch_id' => $batch->id
        ]);
    }

    // Polling Endpoint
    public function getProgress($id) {
        $batch = Bus::findBatch($id);
        return response()->json([
            'progress' => $batch->progress(),
            'totalJobs' => $batch->totalJobs,
            'pendingJobs' => $batch->pendingJobs,
            'processedJobs' => $batch->processedJobs(),
            'finishedAt' => $batch->finishedAt
        ]);
    }
}
