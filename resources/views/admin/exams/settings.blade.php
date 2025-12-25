@extends('layouts.admin')

@section('content')
<div class="max-w-5xl py-6 mx-auto">
    {{-- Steps Navigation --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'settings'])

    <div class="mt-6">
        <h2 class="mb-4 text-xl font-bold text-gray-800">Exam Configuration</h2>

        <form action="{{ route('admin.exams.settings.update', $exam->id) }}" method="POST" x-data="examSettings()">
            @csrf

            {{-- GRID LAYOUT --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- CARD 1: SCORING & TIMING --}}
                <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl h-fit">
                    <h3 class="mb-4 text-sm font-bold text-gray-400 uppercase tracking-wider">Scoring & Timing</h3>

                    <div class="space-y-5">
                        {{-- Duration Mode --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Duration Mode</label>
                            <select name="duration_mode" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                                <option value="auto" {{ $settings['duration_mode'] == 'auto' ? 'selected' : '' }}>Auto (Sum of all questions duration)</option>
                                <option value="manual" {{ $settings['duration_mode'] == 'manual' ? 'selected' : '' }}>Manual (Fixed duration for exam)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">If Manual, set time in exam details.</p>
                        </div>

                        {{-- Marks Mode --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Marks/Points Mode</label>
                            <select name="marks_mode" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                                <option value="auto" {{ $settings['marks_mode'] == 'auto' ? 'selected' : '' }}>Auto (Sum of questions marks)</option>
                                <option value="manual" {{ $settings['marks_mode'] == 'manual' ? 'selected' : '' }}>Manual (Fixed total marks)</option>
                            </select>
                        </div>

                        {{-- Overall Pass Percentage --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Overall Pass Percentage <span class="text-red-500">*</span></label>
                            <div class="relative mt-1">
                                <input type="number" name="cutoff" value="{{ $settings['cutoff'] }}" min="0" max="100" class="w-full border-gray-300 rounded-lg pr-8 focus:ring-[#0777be] focus:border-[#0777be]">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section Cutoff Toggle --}}
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-sm font-medium text-gray-700">Enable Section Cutoff</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_section_cutoff" value="1" {{ $settings['enable_section_cutoff'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>


                        {{-- Negative Marking Toggle --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Negative Marking</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_negative_marking" value="1" {{ $settings['enable_negative_marking'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: ACCESS & SECURITY --}}
                <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl h-fit">
                    <h3 class="mb-4 text-sm font-bold text-gray-400 uppercase tracking-wider">Access & Restrictions</h3>

                    <div class="space-y-5">

                        {{-- Restrict Attempts Logic --}}
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-bold text-gray-700">Restrict Attempts</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="restrict_attempts" value="1" x-model="restrictAttempts" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                                </label>
                            </div>

                            <div x-show="restrictAttempts" x-transition class="mt-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Number of Attempts <span class="text-red-500">*</span></label>
                                <input type="number" name="no_of_attempts" value="{{ $settings['no_of_attempts'] }}" min="1" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
                            </div>
                        </div>

                        {{-- Disable Section Navigation --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Disable Section Navigation</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="disable_section_navigation" value="1" {{ $settings['disable_section_navigation'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                        {{-- Disable Finish Button --}}
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-700">Disable Finish Button</span>
                                <span class="text-[10px] text-gray-400">User cannot finish exam before time</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="disable_finish_button" value="1" {{ $settings['disable_finish_button'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                        {{-- Shuffle Questions --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Shuffle Questions</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="shuffle_questions" value="1" {{ $settings['shuffle_questions'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                    </div>
                </div>

                {{-- CARD 3: REPORT & VISIBILITY --}}
                <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl h-fit md:col-span-2">
                    <h3 class="mb-4 text-sm font-bold text-gray-400 uppercase tracking-wider">Report & Visibility</h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                        {{-- Enable Question List --}}
                        <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50 border-gray-100">
                            <span class="text-sm font-medium text-gray-700">Enable Question List View</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="list_questions" value="1" {{ $settings['list_questions'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                        {{-- Hide Solutions --}}
                        <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50 border-gray-100">
                            <span class="text-sm font-medium text-gray-700">Hide Solutions</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_solutions" value="1" {{ $settings['hide_solutions'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                        {{-- Show Leaderboard --}}
                        <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50 border-gray-100">
                            <span class="text-sm font-medium text-gray-700">Show Leaderboard</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_leaderboard" value="1" {{ $settings['show_leaderboard'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                            </label>
                        </div>

                    </div>
                </div>

            </div>

            {{-- FOOTER BUTTON --}}
            <div class="flex justify-end mt-8">
                <button type="submit" class="bg-[#0777be] text-white px-10 py-3 rounded-xl font-bold hover:bg-[#0666a3] transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Update Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function examSettings() {
        return {
            restrictAttempts: {{ $settings['restrict_attempts'] ? 'true' : 'false' }},
        }
    }
</script>
@endsection
