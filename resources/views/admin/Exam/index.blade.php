{{-- resources/views/admin/quizzes/index.blade.php --}}
@extends('layouts.admin')

@section('header', 'Exams')

@section('content')
<div class="max-w-full">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-semibold text-gray-800">Exams</h3>

        <a href="{{ route('admin.exam.create') }}"
   class="inline-flex items-center px-4 py-2 font-semibold text-white bg-green-500 rounded shadow hover:bg-green-600">
    NEW EXAM
</a>


    </div>

    <div class="p-6 bg-white rounded shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-sm text-left text-gray-600">
                        <th class="px-4 py-3">CODE</th>
                        <th class="px-4 py-3">TITLE</th>
                        <th class="px-4 py-3">CATEGORY</th>
                        <th class="px-4 py-3">TYPE</th>
                        <th class="px-4 py-3">SECTIONS</th>
                        <th class="px-4 py-3">VISIBILITY</th>
                        <th class="px-4 py-3">STATUS</th>
                        <th class="px-4 py-3 text-right">ACTIONS</th>
                    </tr>

                    {{-- Filter row --}}
                    <tr class="bg-gray-50">
                        <form method="GET" action="{{ route('admin.exam.index') ?? route('admin.quizzes.index') }}">
                            <th class="px-4 py-3">
                                <input type="text" name="code" value="{{ request('code') }}" placeholder="Search Code" class="w-full px-3 py-2 text-sm bg-white border rounded">
                            </th>
                            <th class="px-4 py-3">
                                <input type="text" name="title" value="{{ request('title') }}" placeholder="Search" class="w-full px-3 py-2 text-sm bg-white border rounded">
                            </th>
                            <th class="px-4 py-3">
                                <select name="category" class="w-full px-3 py-2 text-sm bg-white border rounded">
                                    <option value="">All</option>
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat->id }}"{{ request('category') == $cat->id ? ' selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-4 py-3">
                                <select name="type" class="w-full px-3 py-2 text-sm bg-white border rounded">
                                    <option value="">All</option>
                                    @foreach($types ?? [] as $type)
                                        <option value="{{ $type->id }}"{{ request('type') == $type->id ? ' selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-4 py-3">
                            </th>
                            <th class="px-4 py-3">
                                <select name="visibility" class="w-full px-3 py-2 text-sm bg-white border rounded">
                                    <option value="">All</option>
                                    <option value="public"{{ request('visibility') == 'public' ? ' selected' : '' }}>Public</option>
                                    <option value="private"{{ request('visibility') == 'private' ? ' selected' : '' }}>Private</option>
                                </select>
                            </th>
                            <th class="px-4 py-3">
                                <select name="status" class="w-full px-3 py-2 text-sm bg-white border rounded">
                                    <option value="">All</option>
                                    <option value="draft"{{ request('status') == 'draft' ? ' selected' : '' }}>Draft</option>
                                    <option value="active"{{ request('status') == 'active' ? ' selected' : '' }}>Active</option>
                                    <option value="inactive"{{ request('status') == 'inactive' ? ' selected' : '' }}>In-active</option>
                                </select>
                            </th>
                            <th class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="submit" class="px-3 py-2 text-sm text-white bg-blue-600 rounded">Filter</button>
                                    <a href="{{ route('admin.exam.index') ?? route('admin.quizzes.index') }}" class="px-3 py-2 text-sm border rounded">Reset</a>
                                </div>
                            </th>
                        </form>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($quizzes as $quiz)
                        <tr class="bg-white">
                            <td class="px-4 py-4">
                                <span class="inline-block px-3 py-1 text-xs font-medium text-white bg-blue-500 rounded-full">
                                    {{ $quiz->code ?? 'quiz_'.substr(md5($quiz->id),0,8) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $quiz->title }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ optional($quiz->category)->name }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ optional($quiz->quizType)->name ?? ($quiz->type ?? 'Quiz') }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ ucfirst($quiz->visibility ?? 'public') }}</td>
                            <td class="px-4 py-4">
                                @if(($quiz->status ?? 'draft') == 'active')
                                    <span class="inline-block px-3 py-1 text-sm text-green-700 bg-green-100 rounded">Active</span>
                                @elseif(($quiz->status ?? '') == 'inactive')
                                    <span class="inline-block px-3 py-1 text-sm text-pink-700 bg-pink-100 rounded">In-active</span>
                                @else
                                    <span class="inline-block px-3 py-1 text-sm text-gray-700 bg-gray-100 rounded">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <!-- Actions dropdown (simple) -->
                                <div class="relative inline-block">
                                    <button class="inline-flex items-center px-3 py-2 border rounded">
                                        Actions
                                        <svg class="w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M5.23 7.21a1 1 0 011.41-.02L10 10.584l3.36-3.4a1 1 0 011.42 1.41l-4.07 4.13a1 1 0 01-1.41.02l-4.07-4.13a1 1 0 01-.02-1.41z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No exams found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- pagination / rows per page UI --}}
        <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-600">ROWS PER PAGE:
                <select onchange="location = this.value;" class="px-2 py-1 ml-2 text-sm bg-white border rounded">
                    <option value="{{ route('admin.exam.index', array_merge(request()->except('page'), ['per' => 10])) }}">10</option>
                    <option value="{{ route('admin.exam.index', array_merge(request()->except('page'), ['per' => 25])) }}">25</option>
                    <option value="{{ route('admin.exam.index', array_merge(request()->except('page'), ['per' => 50])) }}">50</option>
                </select>
            </div>

            <?php /*<div>
                <div class="text-sm text-gray-600">
                    PAGE {{ $quizzes->currentPage() }} OF {{ $quizzes->lastPage() }}
                </div>
            </div>*/?>
        </div>
    </div>
</div>
@endsection
