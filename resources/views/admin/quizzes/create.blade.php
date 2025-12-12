{{-- resources/views/admin/quizzes/create.blade.php --}}
@extends('layouts.admin')

@section('header', 'Create Quiz')

@section('content')
<div class="max-w-full">
    {{-- top spacing + breadcrumbs area --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-semibold text-gray-800">Quiz Details</h3>
            <div class="text-sm text-gray-500">New Quiz</div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.quizzes.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 border rounded text-sm bg-white text-gray-700">
               <!-- breadcrumb / helper -->
               Back
            </a>
        </div>
    </div>

    {{-- Step tabs box --}}
    <div class="bg-white p-4 rounded shadow mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">Quiz Details</div>
                <div class="text-sm text-gray-500">New Quiz</div>
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 border rounded text-sm bg-white text-gray-700">1 Details</button>
                <button class="px-4 py-2 border rounded text-sm bg-gray-100 text-gray-700">2 Settings</button>
                <button class="px-4 py-2 border rounded text-sm bg-gray-100 text-gray-700">3 Questions</button>
                <button class="px-4 py-2 border rounded text-sm bg-gray-100 text-gray-700">4 Schedules</button>
            </div>
        </div>
    </div>

    {{-- Main panel --}}
    <div class="bg-white p-6 rounded shadow">
        {{-- DETAILS form UI (left column wide) --}}
        <form action="#" method="POST">
            @csrf {{-- harmless placeholder for blade --}}
            <div class="space-y-6">
                <!-- Row: Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" placeholder="Title"
                           class="mt-2 block w-full border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Row: Category & Type -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sub Category <span class="text-red-500">*</span></label>
                        <select class="mt-2 block w-full border border-gray-200 rounded px-3 py-2">
                            <option value="">Select Category</option>
                            <option>AE SE (Group A) EXAM</option>
                            <option>BMC Exam</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Quiz Type</label>
                        <select class="mt-2 block w-full border border-gray-200 rounded px-3 py-2">
                            <option value="">Select Type</option>
                            <option>Quiz</option>
                            <option>Exam</option>
                        </select>
                    </div>
                </div>

                <!-- Row: Free toggle -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Free</label>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="relative">
                            <input type="checkbox" id="freeToggle" class="sr-only">
                            <div class="w-12 h-6 bg-gray-200 rounded-full shadow-inner"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transform"></div>
                        </div>
                        <div class="text-sm text-gray-500">Paid (Accessible to only paid users). Free (Anyone can access).</div>
                    </div>
                </div>

                <!-- Row: Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea rows="8" class="mt-2 block w-full border border-gray-200 rounded px-3 py-2" placeholder="Write quiz description here..."></textarea>
                </div>

                <!-- Row: Visibility -->
                <div class="flex items-start gap-6">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Visibility - Public</label>
                        <p class="text-xs text-gray-400 mt-1">Private (Only scheduled user groups can access). Public (Anyone can access).</p>
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="visibility" value="public" checked>
                            <span class="ml-2 text-sm text-gray-600">Public</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="visibility" value="private">
                            <span class="ml-2 text-sm text-gray-600">Private</span>
                        </label>
                    </div>
                </div>

                <!-- Save button -->
                <div class="text-right">
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded shadow">SAVE & PROCEED</button>
                </div>
            </div>
        </form>
    </div>

    {{-- SETTINGS / QUESTIONS / SCHEDULES panels (samples - static, for visual design only) --}}
    <div class="mt-8 space-y-6">
        {{-- Settings visual card (example) --}}
        <div class="bg-white p-6 rounded shadow">
            <h4 class="font-semibold text-gray-800 mb-4">Settings (Preview)</h4>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-700">Duration Mode</label>
                    <div class="mt-2 inline-flex rounded-md shadow-sm" role="group">
                        <button class="px-3 py-2 border border-gray-200 bg-white text-sm">Auto</button>
                        <button class="px-3 py-2 border border-gray-200 bg-white text-sm">Manual</button>
                    </div>

                    <label class="block text-sm text-gray-700 mt-4">Marks/Points Mode</label>
                    <div class="mt-2 inline-flex rounded-md shadow-sm" role="group">
                        <button class="px-3 py-2 border border-gray-200 bg-white text-sm">Auto</button>
                        <button class="px-3 py-2 border border-gray-200 bg-white text-sm">Manual</button>
                    </div>

                    <label class="block text-sm text-gray-700 mt-4">Negative Marking</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center mr-4"><input type="radio" name="neg" checked><span class="ml-2">Yes</span></label>
                        <label class="inline-flex items-center"><input type="radio" name="neg"><span class="ml-2">No</span></label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-700">Shuffle Questions</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center mr-4"><input type="radio" name="shuffle" checked><span class="ml-2">Yes</span></label>
                        <label class="inline-flex items-center"><input type="radio" name="shuffle"><span class="ml-2">No</span></label>
                    </div>

                    <label class="block text-sm text-gray-700 mt-4">Restrict Attempts</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center mr-4"><input type="radio" name="restrict" checked><span class="ml-2">Yes</span></label>
                        <label class="inline-flex items-center"><input type="radio" name="restrict"><span class="ml-2">No</span></label>
                    </div>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button class="bg-green-600 text-white px-4 py-2 rounded">UPDATE</button>
            </div>
        </div>

        {{-- Questions visual card (preview) --}}
        <div class="bg-white p-6 rounded shadow">
            <h4 class="font-semibold text-gray-800 mb-4">Questions (Preview)</h4>
            <div class="grid grid-cols-3 gap-6">
                <div class="col-span-2">
                    <div class="border border-dashed border-gray-200 rounded p-6 h-40 flex items-center justify-center text-gray-400">
                        Question list preview
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-3">Filters</div>
                    <input placeholder="Code" class="block w-full border rounded px-3 py-2 mb-3">
                    <label class="text-xs text-gray-500">Type</label>
                    <div class="mt-2 space-y-1">
                        <label><input type="checkbox"> MCQ - Single</label>
                        <label><input type="checkbox"> MCQ - Multiple</label>
                        <label><input type="checkbox"> True/False</label>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">RESET</button>
                        <button class="px-4 py-2 bg-green-600 text-white rounded">SEARCH</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedules visual card (preview) --}}
        <div class="bg-white p-6 rounded shadow">
            <h4 class="font-semibold text-gray-800 mb-4">Schedules (Preview)</h4>
            <div class="border border-dashed border-gray-200 rounded p-6 text-gray-400">
                Schedules UI preview (start date, end date, user groups)
            </div>
        </div>
    </div>
</div>

{{-- small script to visually animate the custom toggle (UI only) --}}
@push('scripts')
<script>
    // purely cosmetic JS — toggles a faux switch background (no server logic)
    document.addEventListener('click', function(e){
        if (e.target.closest('#freeToggle')) {
            const box = e.target.closest('#freeToggle');
            // no logic required — this is placeholder
        }
    });
</script>
@endpush
@endsection
