<?php

namespace App\Repositories;

use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

class ExamRepository
{
    public function getSteps($eId = null, $active = 'details')
    {
        // 1. Determine Prefix (Admin vs Panel)
        $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
        $prefix = $isAdmin ? 'admin.' : 'panel.';

        // 2. Prepare Base Parameters (Instructor needs 'role' param)
        $baseParams = [];
        if (!$isAdmin) {
            $baseParams['role'] = request()->route('role') ?? 'instructor';
        }

        // 3. Helper Closure to generate URL
        $makeUrl = function ($routeName) use ($prefix, $baseParams, $eId) {
            if (!$eId) return '';

            // Merge Exam ID with base params (role)
            $params = array_merge($baseParams, ['exam' => $eId]);

            return route($prefix . 'exams.' . $routeName, $params);
        };
        $isPublished = false;
        if ($eId) {
            $exam = Exam::find($eId);
            // Check if status is published
            $isPublished = $exam && $exam->status === 'published';
        }
        return [
            [
                'step' => 1,
                'key' => 'details',
                'title' => 'Details',
                'status' => $active == 'details' ? 'active' : 'inactive',
                'url' => $makeUrl('edit'),
                'locked' => false
            ],
            [
                'step' => 2,
                'key' => 'settings',
                'title' => 'Settings',
                'status' => $active == 'settings' ? 'active' : 'inactive',
                'url' => $makeUrl('settings'),
                'locked' => false
            ],
            [
                'step' => 3,
                'key' => 'sections',
                'title' => 'Sections',
                'status' => $active == 'sections' ? 'active' : 'inactive',
                'url' => $makeUrl('sections.index'),
                'locked' => false
            ],
            [
                'step' => 4,
                'key' => 'questions',
                'title' => 'Questions',
                'status' => $active == 'questions' ? 'active' : 'inactive',
                'url' => $makeUrl('questions.index'),
                'locked' => false
            ],
            [
                'step' => 5,
                'key' => 'schedules',
                'title' => 'Schedules',
                'status' => $active == 'schedules' ? 'active' : 'inactive',
                'url' => $makeUrl('schedules.index'),
                'locked' => ($eId && !$isPublished)
            ],
        ];
    }
}
