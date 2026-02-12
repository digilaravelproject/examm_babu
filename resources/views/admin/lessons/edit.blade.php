@extends('layouts.admin')
@section('title', 'Edit Lesson')

@section('content')
    <div class="py-6 mx-auto max-w-5xl">
        <div class="mb-6 flex justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Edit Lesson</h1>
            <a href="{{ route('admin.lessons.index') }}" class="text-gray-600 hover:text-gray-900">&larr; Back</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    {{-- Same fields as create --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" class="w-full border-gray-300 rounded-lg p-2.5" required
                            value="{{ old('title', $lesson->title) }}">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Skill</label>
                            <select name="skill_id" class="w-full border-gray-300 rounded-lg p-2.5" required>
                                @foreach ($skills as $skill)
                                    <option value="{{ $skill->id }}"
                                        {{ $lesson->skill_id == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Topic</label>
                            <select name="topic_id" class="w-full border-gray-300 rounded-lg p-2.5">
                                <option value="">Select Topic</option>
                                @foreach ($topics as $topic)
                                    <option value="{{ $topic->id }}"
                                        {{ $lesson->topic_id == $topic->id ? 'selected' : '' }}>{{ $topic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Difficulty</label>
                            <select name="difficulty_level_id" class="w-full border-gray-300 rounded-lg p-2.5" required>
                                @foreach ($difficultyLevels as $level)
                                    <option value="{{ $level->id }}"
                                        {{ $lesson->difficulty_level_id == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Read Time (Minutes)</label>
                            <input type="number" name="duration" class="w-full border-gray-300 rounded-lg p-2.5" required
                                min="1" value="{{ old('duration', $lesson->duration) }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Content</label>
                        <textarea name="body" id="editor_body" class="w-full border-gray-300 rounded-lg p-2.5">{{ old('body', $lesson->body) }}</textarea>
                    </div>

                    <div class="flex gap-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_paid" value="1" class="h-5 w-5 text-[#0777be] rounded"
                                {{ $lesson->is_paid ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700 font-bold">Paid Content</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 text-[#0777be] rounded"
                                {{ $lesson->is_active ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700 font-bold">Active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit"
                        class="px-6 py-3 bg-[#0777be] text-white font-bold rounded-lg hover:bg-[#0666a3] shadow-md">
                        Update Lesson
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('editor_body');
    </script>
@endsection
