@extends('layouts.admin')
@section('title', 'Exam Configuration')

@section('content')
<div class="max-w-5xl py-8 mx-auto">
    {{-- Steps Navigation --}}
    @include('admin.exams.partials._steps', ['activeStep' => 'settings'])

    <div class="mt-8">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">Exam Configuration</h2>
                <p class="text-sm text-gray-500">Fine-tune how your exam behaves for the students.</p>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="text-sm font-semibold transition-colors text-gray-400 hover:text-[var(--brand-blue)] flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to List
            </a>
        </div>

        <form action="{{ route('admin.exams.settings.update', $exam->id) }}" method="POST" x-data="examSettings()">
            @csrf

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

                {{-- CARD 1: SCORING & TIMING --}}
                <div class="p-8 bg-white border border-gray-200 shadow-sm rounded-2xl h-fit border-t-4" style="border-t-color: var(--brand-blue);">
                    <h3 class="mb-6 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Scoring & Timing</h3>

                    <div class="space-y-6">
                        {{-- Duration Mode - Custom Dropdown --}}
                        <div class="space-y-2" x-data="{ open: false, selected: '{{ $settings['duration_mode'] == 'auto' ? 'Auto (Sum of Questions)' : 'Manual (Fixed Duration)' }}', value: '{{ $settings['duration_mode'] }}' }">
                            <label class="text-[11px] font-black text-gray-600 uppercase tracking-wider">Duration Mode</label>
                            <div class="relative">
                                <input type="hidden" name="duration_mode" :value="value">
                                <button type="button" @click="open = !open" @click.away="open = false"
                                    class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 focus:ring-4 focus:ring-[var(--brand-blue)]/10 transition-all flex justify-between items-center">
                                    <span x-text="selected"></span>
                                    <svg class="w-4 h-4 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl">
                                    <div @click="selected='Auto (Sum of Questions)'; value='auto'; open=false" class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white">Auto (Sum of all questions)</div>
                                    <div @click="selected='Manual (Fixed Duration)'; value='manual'; open=false" class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white">Manual (Fixed duration)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Overall Pass Percentage --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-600 uppercase tracking-wider">Pass Percentage (%)</label>
                            <div class="relative">
                                <input type="number" name="cutoff" value="{{ $settings['cutoff'] }}" min="0" max="100"
                                       class="w-full px-4 py-3 text-sm border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)]">
                                <span class="absolute right-4 top-3.5 text-gray-400 font-bold">%</span>
                            </div>
                        </div>

                        {{-- Toggles for Section Cutoff & Negative Marking --}}
                        <div class="pt-2 space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50/50 border border-gray-100 rounded-xl">
                                <span class="text-sm font-bold text-gray-700">Sectional Cutoff</span>
                                <button type="button" @click="sectionCutoff = !sectionCutoff" :class="sectionCutoff ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 rounded-full transition-colors">
                                    <input type="hidden" name="enable_section_cutoff" :value="sectionCutoff ? '1' : '0'">
                                    <span :class="sectionCutoff ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 mt-0.5 ml-0.5 rounded-full bg-white transition shadow-sm"></span>
                                </button>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50/50 border border-gray-100 rounded-xl">
                                <span class="text-sm font-bold text-gray-700">Negative Marking</span>
                                <button type="button" @click="negativeMarking = !negativeMarking" :class="negativeMarking ? 'bg-[var(--brand-pink)]' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 rounded-full transition-colors">
                                    <input type="hidden" name="enable_negative_marking" :value="negativeMarking ? '1' : '0'">
                                    <span :class="negativeMarking ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 mt-0.5 ml-0.5 rounded-full bg-white transition shadow-sm"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: ACCESS & SECURITY --}}
                <div class="p-8 bg-white border border-gray-200 shadow-sm rounded-2xl h-fit border-t-4" style="border-t-color: var(--brand-sky);">
                    <h3 class="mb-6 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Access & Restrictions</h3>

                    <div class="space-y-4">
                        {{-- Restrict Attempts Card --}}
                        <div class="p-5 border border-gray-100 bg-gray-50/50 rounded-2xl transition-all" :class="restrictAttempts ? 'ring-2 ring-[var(--brand-blue)]/20 bg-white' : ''">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-bold text-gray-700">Limit Attempts</span>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tighter" x-text="restrictAttempts ? 'Active' : 'Unlimited'"></p>
                                </div>
                                <button type="button" @click="restrictAttempts = !restrictAttempts" :class="restrictAttempts ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 rounded-full transition-colors">
                                    <input type="hidden" name="restrict_attempts" :value="restrictAttempts ? '1' : '0'">
                                    <span :class="restrictAttempts ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 mt-0.5 ml-0.5 rounded-full bg-white transition shadow-sm"></span>
                                </button>
                            </div>
                            <div x-show="restrictAttempts" x-transition class="mt-4 pt-4 border-t border-gray-100">
                                <label class="text-[10px] font-black text-gray-500 uppercase">Max Attempts Allowed</label>
                                <input type="number" name="no_of_attempts" value="{{ $settings['no_of_attempts'] }}" min="1"
                                       class="w-full mt-1 px-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-[var(--brand-blue)]">
                            </div>
                        </div>

                        {{-- Other Toggles --}}
                        @php
                            $securitySettings = [
                                ['name' => 'disable_section_navigation', 'label' => 'Lock Section Nav', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                                ['name' => 'disable_finish_button', 'label' => 'Strict Timing (No early finish)', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['name' => 'shuffle_questions', 'label' => 'Shuffle Questions', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15']
                            ];
                        @endphp

                        @foreach($securitySettings as $set)
                        <div x-data="{ active: {{ $settings[$set['name']] ? 'true' : 'false' }} }"
                             class="flex items-center justify-between p-4 bg-gray-50/50 border border-gray-100 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $set['icon'] }}"></path></svg></div>
                                <span class="text-sm font-bold text-gray-700">{{ $set['label'] }}</span>
                            </div>
                            <button type="button" @click="active = !active" :class="active ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 rounded-full transition-colors">
                                <input type="hidden" name="{{ $set['name'] }}" :value="active ? '1' : '0'">
                                <span :class="active ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 mt-0.5 ml-0.5 rounded-full bg-white transition shadow-sm"></span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- CARD 3: REPORT & VISIBILITY --}}
                <div class="p-8 bg-white border border-gray-200 shadow-sm rounded-2xl md:col-span-2 border-t-4" style="border-t-color: var(--brand-green);">
                    <h3 class="mb-6 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Report & Transparency</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            ['name' => 'list_questions', 'label' => 'Question List View'],
                            ['name' => 'hide_solutions', 'label' => 'Hide Solutions'],
                            ['name' => 'show_leaderboard', 'label' => 'Show Leaderboard']
                        ] as $report)
                            <div x-data="{ active: {{ $settings[$report['name']] ? 'true' : 'false' }} }"
                                 class="flex items-center justify-between p-5 bg-gray-50/30 border border-gray-100 rounded-2xl hover:bg-white hover:shadow-sm transition-all">
                                <span class="text-sm font-bold text-gray-700">{{ $report['label'] }}</span>
                                <button type="button" @click="active = !active" :class="active ? 'bg-[var(--brand-green)]' : 'bg-gray-300'" class="relative inline-flex h-6 w-11 rounded-full transition-colors">
                                    <input type="hidden" name="{{ $report['name'] }}" :value="active ? '1' : '0'">
                                    <span :class="active ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 mt-0.5 ml-0.5 rounded-full bg-white transition shadow-sm"></span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- FOOTER BUTTON --}}
            <div class="flex items-center justify-end mt-10 pb-10">
                <button type="submit" class="flex items-center gap-3 px-12 py-4 text-sm font-black text-white transition-all rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-1 active:scale-95" style="background-color: var(--brand-blue);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    SAVE CONFIGURATION
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function examSettings() {
        return {
            restrictAttempts: {{ $settings['restrict_attempts'] ? 'true' : 'false' }},
            sectionCutoff: {{ $settings['enable_section_cutoff'] ? 'true' : 'false' }},
            negativeMarking: {{ $settings['enable_negative_marking'] ? 'true' : 'false' }},
        }
    }
</script>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
