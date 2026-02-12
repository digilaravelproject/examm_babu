@extends('layouts.admin')
@section('title', 'Configure Lessons - Step 2')

@section('content')
    <div class="max-w-full mx-auto py-6 px-4" x-data="lessonManager()">

        {{-- Header with Step Back --}}
        <div
            class="mb-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Configure Lessons</h1>
                <p class="text-sm text-gray-500">
                    <span class="font-bold text-[#0777be]">{{ $subCategory->name }}</span>
                    <span class="mx-2">/</span>
                    <span class="font-bold text-[#0777be]">{{ $skill->name }}</span>
                </p>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('admin.practice.configure') }}"
                    class="flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50">
                    <span
                        class="w-6 h-6 flex items-center justify-center bg-gray-400 text-white rounded-full text-xs mr-2">1</span>
                    Change Skill
                </a>
                <div
                    class="flex items-center px-4 py-2 bg-white border-2 border-[#0777be] text-[#0777be] rounded-lg font-bold shadow-sm">
                    <span
                        class="w-6 h-6 flex items-center justify-center bg-[#0777be] text-white rounded-full text-xs mr-2">2</span>
                    Add/Remove Lessons
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- LEFT SIDEBAR: FILTERS --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        <h3 class="font-bold text-gray-800">Filters</h3>
                    </div>

                    <form method="GET" action="{{ route('admin.practice.manage') }}">
                        <input type="hidden" name="sub_category_id" value="{{ $subCategory->id }}">
                        <input type="hidden" name="skill_id" value="{{ $skill->id }}">

                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Code</label>
                                <input type="text" name="code" value="{{ request('code') }}"
                                    class="w-full mt-1 border-gray-300 rounded-md text-sm p-2" placeholder="Lesson Code">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Title</label>
                                <input type="text" name="title" value="{{ request('title') }}"
                                    class="w-full mt-1 border-gray-300 rounded-md text-sm p-2" placeholder="Lesson Title">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Difficulty</label>
                                @foreach ($difficultyLevels as $level)
                                    <label class="flex items-center gap-2 mb-1 cursor-pointer">
                                        <input type="checkbox" name="difficulty[]" value="{{ $level->id }}"
                                            {{ in_array($level->id, request('difficulty', [])) ? 'checked' : '' }}
                                            class="rounded text-[#0777be] focus:ring-[#0777be]">
                                        <span class="text-sm text-gray-700">{{ $level->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="pt-4 flex gap-2">
                                <a href="{{ route('admin.practice.manage', ['sub_category_id' => $subCategory->id, 'skill_id' => $skill->id]) }}"
                                    class="flex-1 py-2 text-center border border-gray-300 rounded bg-gray-50 text-sm hover:bg-gray-100">Reset</a>
                                <button type="submit"
                                    class="flex-1 py-2 bg-[#0777be] text-white rounded text-sm font-bold hover:bg-[#0666a3]">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT SIDE: LESSON LIST --}}
            <div class="lg:col-span-3">

                {{-- Info Box --}}
                <div class="bg-green-50 border border-green-200 p-4 rounded-lg mb-4 flex justify-between items-center">
                    <div>
                        <h4 class="font-bold text-green-800 text-sm">Currently Viewing Lessons</h4>
                        <p class="text-xs text-green-600 mt-1">Use the actions below to Add or Remove lessons from this
                            skill.</p>
                    </div>
                </div>

                {{-- Lesson Grid --}}
                <div class="space-y-4">
                    @forelse($lessons as $lesson)
                        @php
                            $isAttached = in_array($lesson->id, $attachedLessonIds);
                        @endphp

                        <div
                            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

                            {{-- Lesson Details --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-mono rounded border">{{ $lesson->code }}</span>
                                    @if ($lesson->is_active)
                                        <span class="w-2 h-2 bg-green-500 rounded-full" title="Active"></span>
                                    @else
                                        <span class="w-2 h-2 bg-red-500 rounded-full" title="Inactive"></span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-gray-900 text-lg">{{ $lesson->title }}</h4>
                                <div class="flex gap-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center gap-1"><i class="far fa-clock"></i>
                                        {{ $lesson->duration }} mins</span>
                                    <span class="flex items-center gap-1"><i class="far fa-star"></i>
                                        {{ $lesson->difficultyLevel->name ?? 'N/A' }}</span>
                                </div>
                            </div>

                            {{-- Action Buttons (Alpine Logic) --}}
                            <div x-data="{ attached: {{ $isAttached ? 'true' : 'false' }}, loading: false }">

                                {{-- ADD BUTTON --}}
                                <button x-show="!attached" @click="toggleLesson({{ $lesson->id }}, 'attach', $data)"
                                    class="px-5 py-2 bg-white border border-green-500 text-green-600 rounded-lg font-bold hover:bg-green-50 transition-colors flex items-center gap-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': loading }" :disabled="loading">
                                    <span x-show="!loading">+ Add</span>
                                    <span x-show="loading">...</span>
                                </button>

                                {{-- REMOVE BUTTON --}}
                                <button x-show="attached" @click="toggleLesson({{ $lesson->id }}, 'detach', $data)"
                                    class="px-5 py-2 bg-red-50 border border-red-200 text-red-600 rounded-lg font-bold hover:bg-red-100 transition-colors flex items-center gap-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': loading }" :disabled="loading">
                                    <span x-show="!loading">× Remove</span>
                                    <span x-show="loading">...</span>
                                </button>

                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
                            <p class="text-gray-500 font-medium">No lessons found matching criteria.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $lessons->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function lessonManager() {
                return {
                    subCategoryId: '{{ $subCategory->id }}',
                    skillId: '{{ $skill->id }}',

                    toggleLesson(lessonId, action, component) {
                        component.loading = true;
                        const url = action === 'attach' ? '{{ route('admin.practice.attach') }}' :
                            '{{ route('admin.practice.detach') }}';

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    sub_category_id: this.subCategoryId,
                                    skill_id: this.skillId,
                                    lesson_id: lessonId
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                component.loading = false;
                                if (data.success) {
                                    component.attached = (action === 'attach'); // Toggle State
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                    Toast.fire({
                                        icon: 'success',
                                        title: data.message
                                    });
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(err => {
                                component.loading = false;
                                console.error(err);
                            });
                    }
                }
            }
        </script>
    @endpush
@endsection
