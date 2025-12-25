@extends('layouts.admin')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8"
     x-data="scheduleManager()"
     @keydown.escape.window="closeModal()">

    {{-- Steps Navigation --}}
    @if(view()->exists('admin.exams.partials._steps'))
        @include('admin.exams.partials._steps', ['activeStep' => 'schedules'])
    @endif

    <div class="mt-8 space-y-6">
        {{-- Header Section --}}
        <div class="flex flex-col justify-between gap-6 p-6 bg-white border border-gray-100 shadow-sm md:flex-row md:items-center rounded-2xl">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900">Exam Schedules</h1>
                <p class="flex items-center gap-2 mt-1 text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-[#0777be]"></span>
                    Timing & Access Management for <span class="font-bold text-gray-800">{{ $exam->title }}</span>
                </p>
            </div>
            <button @click="openAddModal()"
                class="inline-flex items-center gap-2 px-6 py-3 font-bold text-white transition-all bg-[#0777be] rounded-xl shadow-lg shadow-blue-100 hover:bg-[#0666a3] hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Create New Schedule
            </button>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 border-l-4 bg-emerald-50 border-emerald-500 rounded-r-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    <span class="font-medium text-emerald-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 border-l-4 bg-rose-50 border-rose-500 rounded-r-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>
                    <span class="font-medium text-rose-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Data Table --}}
        <div class="overflow-hidden bg-white border border-gray-100 shadow-xl rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80">
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Access Code</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Start Schedule</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">End Schedule</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-right text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($schedules as $schedule)
                    <tr class="transition-colors hover:bg-blue-50/30 group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-50 rounded-lg text-[#0777be]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <span class="font-mono font-bold tracking-tighter text-gray-700">{{ $schedule->code ?? 'SCH-'.$schedule->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span @class([
                                'px-3 py-1 text-[10px] font-black uppercase rounded-full border',
                                'bg-indigo-50 text-indigo-600 border-indigo-100' => $schedule->schedule_type == 'fixed',
                                'bg-orange-50 text-orange-600 border-orange-100' => $schedule->schedule_type == 'flexible'
                            ])>
                                {{ $schedule->schedule_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($schedule->start_date)->format('l') }}</span>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }} • {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($schedule->end_date)->format('l') }}</span>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }} • {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'expired' => 'bg-gray-100 text-gray-600',
                                    'cancelled' => 'bg-rose-100 text-rose-700'
                                ];
                                $color = $statusColors[$schedule->status] ?? 'bg-blue-100 text-blue-700';
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $color }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Analytics --}}
                                <a href="{{ Route::has('admin.exams.schedules.analytics') ? route('admin.exams.schedules.analytics', [$exam->id, $schedule->id]) : '#' }}"
                                   class="p-2 text-gray-400 transition-all rounded-lg hover:text-amber-500 hover:bg-amber-50" title="Analytics">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </a>

                                <button @click="editSchedule({{ $exam->id }}, {{ $schedule->id }})"
                                    class="p-2 text-gray-400 hover:text-[#0777be] hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                <form action="{{ route('admin.exams.schedules.destroy', [$exam->id, $schedule->id]) }}" method="POST" onsubmit="return confirm('Delete this schedule?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 text-gray-400 transition-all rounded-lg hover:text-rose-600 hover:bg-rose-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <div class="flex flex-col items-center opacity-40">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="font-bold">No schedules found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div x-show="isModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-3xl sm:my-8 sm:align-middle">

                <form :action="formAction" method="POST" id="scheduleForm">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <h3 class="text-xl font-black text-gray-900" x-text="isEdit ? 'Update Schedule' : 'Create Schedule'"></h3>
                            <p class="text-sm text-gray-500">Configure access parameters for this exam</p>
                        </div>
                        <button type="button" @click="closeModal()" class="p-2 text-gray-400 bg-white border border-gray-100 shadow-sm hover:text-gray-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-8 space-y-6">
                        {{-- Schedule Type --}}
                        <div class="p-4 bg-gray-50 rounded-2xl">
                            <label class="block mb-3 text-xs font-black tracking-widest text-gray-400 uppercase">Schedule Strategy</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative flex flex-col p-4 transition-all bg-white border-2 cursor-pointer rounded-xl"
                                       :class="scheduleType === 'fixed' ? 'border-[#0777be] ring-4 ring-blue-50' : 'border-gray-100 hover:border-gray-200'">
                                    <input type="radio" name="schedule_type" value="fixed" x-model="scheduleType" class="sr-only">
                                    <span class="font-bold text-gray-800">Fixed</span>
                                    <span class="text-[10px] text-gray-400">Ends automatically after duration</span>
                                </label>
                                <label class="relative flex flex-col p-4 transition-all bg-white border-2 cursor-pointer rounded-xl"
                                       :class="scheduleType === 'flexible' ? 'border-[#0777be] ring-4 ring-blue-50' : 'border-gray-100 hover:border-gray-200'">
                                    <input type="radio" name="schedule_type" value="flexible" x-model="scheduleType" class="sr-only">
                                    <span class="font-bold text-gray-800">Flexible</span>
                                    <span class="text-[10px] text-gray-400">Set a specific window</span>
                                </label>
                            </div>
                        </div>

                        {{-- Multi-Select User Groups --}}
                        <div class="relative" x-data="{ open: false, search: '' }">
                            <label class="block mb-2 text-xs font-black tracking-widest text-gray-400 uppercase">Assign to Groups</label>
                            <div class="min-h-[50px] w-full p-2 bg-white border-2 border-gray-100 rounded-xl flex flex-wrap gap-2 cursor-pointer focus-within:border-[#0777be]" @click="open = !open">
                                <template x-for="groupId in selectedGroups" :key="groupId">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-[#0777be] text-xs font-bold rounded-lg border border-blue-100">
                                        <span x-text="groupNames[groupId]"></span>
                                        <button type="button" @click.stop="toggleGroup(groupId)" class="hover:text-blue-800">&times;</button>
                                    </span>
                                </template>
                                <span x-show="selectedGroups.length === 0" class="px-2 py-1 text-sm text-gray-400">Select user groups...</span>

                                {{-- HIDDEN INPUTS FOR FORM SUBMISSION --}}
                                <template x-for="groupId in selectedGroups" :key="'input-'+groupId">
                                    <input type="hidden" name="user_group_ids[]" :value="groupId">
                                </template>
                            </div>

                            {{-- Dropdown Panel --}}
                            <div x-show="open" @click.away="open = false" class="absolute z-[60] w-full mt-2 bg-white border border-gray-100 shadow-2xl rounded-2xl max-h-60 overflow-y-auto p-2">
                                <input type="text" x-model="search" placeholder="Search groups..." class="w-full p-2 mb-2 text-sm border-gray-100 rounded-lg bg-gray-50 focus:ring-0">
                                <div class="space-y-1">
                                    @foreach($userGroups as $group)
                                    <div x-show="'{{ strtolower($group->name) }}'.includes(search.toLowerCase())"
                                         @click="toggleGroup({{ $group->id }})"
                                         class="flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg cursor-pointer"
                                         :class="selectedGroups.includes({{ $group->id }}) ? 'bg-blue-50 text-[#0777be]' : 'hover:bg-gray-50'">
                                        <span>{{ $group->name }}</span>
                                        <template x-if="selectedGroups.includes({{ $group->id }})">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                        </template>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Timing Grid --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="block text-xs font-black tracking-widest text-gray-400 uppercase">Start Date</label>
                                <input type="date" name="start_date" x-model="formData.start_date" required class="w-full p-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0777be]">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-black tracking-widest text-gray-400 uppercase">Start Time</label>
                                <input type="time" name="start_time" x-model="formData.start_time" required class="w-full p-3 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#0777be]">
                            </div>

                            <template x-if="scheduleType === 'flexible'">
                                <div class="grid grid-cols-1 gap-6 p-4 border border-orange-100 md:col-span-2 md:grid-cols-2 bg-orange-50/30 rounded-2xl animate-fadeIn">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-black tracking-widest text-orange-400 uppercase">End Date</label>
                                        <input type="date" name="end_date" x-model="formData.end_date" required class="w-full p-3 bg-white border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-400">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-xs font-black tracking-widest text-orange-400 uppercase">End Time</label>
                                        <input type="time" name="end_time" x-model="formData.end_time" required class="w-full p-3 bg-white border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-400">
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Grace Period --}}
                        <div class="flex items-center gap-6 p-4 border border-blue-100 bg-blue-50/30 rounded-2xl">
                            <div class="flex-shrink-0 w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-[#0777be]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="flex-grow">
                                <label class="block text-xs font-black tracking-widest text-gray-400 uppercase">Grace Period (Minutes)</label>
                                <input type="number" name="grace_period" x-model="formData.grace_period" min="0" class="w-32 mt-1 p-2 bg-white border-blue-100 rounded-lg focus:ring-2 focus:ring-[#0777be]">
                                <p class="text-[10px] text-gray-400 mt-1 italic">Default is 5 minutes if left empty.</p>
                            </div>
                        </div>

                        <input type="hidden" name="status" x-model="formData.status">
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-between px-8 py-6 border-t border-gray-100 bg-gray-50 rounded-b-3xl">
                        <button type="button" @click="closeModal()" class="font-bold text-gray-500 hover:text-gray-700">Discard Changes</button>
                        <button type="submit"
                                class="px-8 py-3 bg-[#0777be] text-white font-black rounded-xl shadow-lg shadow-blue-100 hover:bg-[#0666a3] transition-all">
                            <span x-text="isEdit ? 'Update Schedule' : 'Launch Schedule'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
</style>

<script>
function scheduleManager() {
    return {
        isModalOpen: false,
        isEdit: false,
        formAction: '',
        scheduleType: 'fixed',
        selectedGroups: [],
        groupNames: {
            @foreach($userGroups as $group) {{ $group->id }}: '{{ $group->name }}', @endforeach
        },
        formData: {
            start_date: '',
            start_time: '',
            end_date: '',
            end_time: '',
            grace_period: 5,
            status: 'active'
        },

        openAddModal() {
            this.isEdit = false;
            this.formAction = `/admin/exams/{{ $exam->id }}/schedules`;
            this.resetForm();
            this.isModalOpen = true;
        },

        toggleGroup(id) {
            if (this.selectedGroups.includes(id)) {
                this.selectedGroups = this.selectedGroups.filter(i => i !== id);
            } else {
                this.selectedGroups.push(id);
            }
        },

        resetForm() {
            this.scheduleType = 'fixed';
            this.selectedGroups = [];
            this.formData = {
                start_date: '',
                start_time: '',
                end_date: '',
                end_time: '',
                grace_period: 5,
                status: 'active'
            };
        },

        closeModal() {
            this.isModalOpen = false;
        },

        async editSchedule(examId, scheduleId) {
            try {
                const res = await fetch(`/admin/exams/${examId}/schedules/${scheduleId}/edit`);
                const data = await res.json();

                this.isEdit = true;
                this.formAction = `/admin/exams/${examId}/schedules/${scheduleId}`;
                this.scheduleType = data.schedule.schedule_type;
                this.selectedGroups = data.user_group_ids;

                this.formData = {
                    start_date: data.schedule.start_date,
                    start_time: data.schedule.start_time,
                    end_date: data.schedule.end_date,
                    end_time: data.schedule.end_time,
                    grace_period: data.schedule.grace_period || 5,
                    status: data.schedule.status
                };

                this.isModalOpen = true;
            } catch (e) {
                alert('Error fetching data.');
            }
        }
    }
}
</script>
@endsection
