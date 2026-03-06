<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasColumn('exam_sections', 'enable_negative_marking')) {
    DB::statement('ALTER TABLE exam_sections ADD COLUMN enable_negative_marking TINYINT(1) DEFAULT 0 AFTER negative_marks');
    echo "Column added successfully.";
} else {
    echo "Column already exists.";
}
