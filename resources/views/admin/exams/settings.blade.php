@extends('layouts.admin')
@section('content')
<div class="max-w-4xl py-8 mx-auto">
    <form action="{{ route('admin.exams.settings.update', $exam->id) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @php
                $settingsList = [
                    'auto_duration' => 'Auto Duration',
                    'auto_grading' => 'Auto Grading',
                    'shuffle_questions' => 'Shuffle Questions',
                    'show_leaderboard' => 'Show Leaderboard'
                ];
            @endphp

            @foreach($settingsList as $key => $label)
            <div class="flex items-center justify-between p-4 bg-white border shadow-sm rounded-xl">
                <span class="font-bold text-gray-700">{{ $label }}</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="{{ $key }}" value="1"
                        {{ (isset($exam->settings[$key]) && $exam->settings[$key]) ? 'checked' : '' }}
                        class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>
            @endforeach
        </div>

        <div class="p-4 mt-6 bg-white border shadow-sm rounded-xl">
            <label class="block font-bold text-gray-700">Passing Cutoff (%)</label>
            <input type="number" name="cutoff" value="{{ $exam->settings['cutoff'] ?? 40 }}" class="w-full mt-2 border-gray-300 rounded-lg">
        </div>

        <button type="submit" class="w-full py-4 mt-8 font-bold text-white bg-blue-600 shadow-lg rounded-xl">
            Final Save Settings
        </button>
    </form>
</div>
@endsection
