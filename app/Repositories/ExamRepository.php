<?php

namespace App\Repositories;

class ExamRepository
{
    public function getSteps($eId = null, $active = 'details')
    {
        return [
            ['step' => 1, 'key' => 'details', 'title' => 'Details', 'status' => $active == 'details' ? 'active' : 'inactive', 'url' => $eId ? route('admin.exams.edit', $eId) : ''],
            ['step' => 2, 'key' => 'settings', 'title' => 'Settings', 'status' => $active == 'settings' ? 'active' : 'inactive', 'url' => $eId ? route('admin.exams.settings', $eId) : ''],
            ['step' => 3, 'key' => 'sections', 'title' => 'Sections', 'status' => $active == 'sections' ? 'active' : 'inactive', 'url' => $eId ? route('admin.exams.sections.index', $eId) : ''],
            ['step' => 4, 'key' => 'questions', 'title' => 'Questions', 'status' => $active == 'questions' ? 'active' : 'inactive', 'url' => $eId ? route('admin.exams.index', $eId) : ''], // Question controller route placeholder
            ['step' => 5, 'key' => 'schedules', 'title' => 'Schedules', 'status' => $active == 'schedules' ? 'active' : 'inactive', 'url' => $eId ? route('admin.exams.schedules.index', $eId) : ''],
        ];
    }
}
