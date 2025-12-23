@extends('layouts.admin')
@section('title', 'Edit Topic')
@section('content')
<div class="max-w-4xl py-6 mx-auto space-y-6">
    <div class="flex items-center justify-between px-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Topic</h1>
            <p class="font-mono text-xs text-gray-500">{{ $topic->code }}</p>
        </div>
        <a href="{{ route('admin.topics.index') }}" class="text-sm font-medium text-gray-500">Back</a>
    </div>

    <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl">
        <form action="{{ route('admin.topics.update', $topic->id) }}" method="POST" class="p-6 space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="block mb-2 text-xs font-bold text-gray-600 uppercase">Skill *</label>
                    <select name="skill_id" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-[#0777be]">
                        @foreach($skills as $sk)
                            <option value="{{ $sk->id }}" {{ $topic->skill_id == $sk->id ? 'selected' : '' }}>{{ $sk->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-xs font-bold text-gray-600 uppercase">Topic Name *</label>
                    <input type="text" name="name" value="{{ $topic->name }}" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-[#0777be]">
                </div>
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-600 uppercase">Short Description</label>
                <textarea name="short_description" rows="2" class="w-full border-gray-300 rounded-lg text-sm focus:ring-[#0777be]">{{ $topic->short_description }}</textarea>
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                <span class="text-sm font-bold text-gray-700">Set as Active</span>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $topic->is_active ? 'checked' : '' }} class="w-5 h-5 text-[#94c940] rounded border-gray-300">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow hover:bg-[#0666a3] transition-all">Update Topic</button>
            </div>
        </form>
    </div>
</div>
@endsection
