{{-- resources/views/admin/quizzes/index.blade.php --}}
@extends('layouts.admin')

@section('header', 'Practice Sets')

@section('content')
<div class="max-w-full">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-semibold text-gray-800">Practice Sets</h3>

        <a href="{{ route('admin.practice-sets.create') }}"
   class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded shadow">
    NEW PRACTICE SETS
</a>


    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-3 px-4">CODE</th>
                        <th class="py-3 px-4">TITLE</th>
                        <th class="py-3 px-4">CATEGORY</th>
                        <th class="py-3 px-4">TYPE</th>
                        <th class="py-3 px-4">Skill</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4 text-right">ACTIONS</th>
                    </tr>

                    {{-- Filter row --}}
                    <tr class="bg-gray-50">
                        <form method="GET" action="{{ route('admin.quizzes.index') ?? route('admin.quizzes.index') }}">
                            <th class="py-3 px-4">
                                <input type="text" name="code" value="{{ request('code') }}" placeholder="Search Code" class="w-full px-3 py-2 border rounded text-sm bg-white">
                            </th>
                            <th class="py-3 px-4">
                                <input type="text" name="title" value="{{ request('title') }}" placeholder="Search" class="w-full px-3 py-2 border rounded text-sm bg-white">
                            </th>
                            <th class="py-3 px-4">
                                <select name="category" class="w-full px-3 py-2 border rounded text-sm bg-white">
                                    <option value="">All</option>
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat->id }}"{{ request('category') == $cat->id ? ' selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="py-3 px-4">
                                <select name="type" class="w-full px-3 py-2 border rounded text-sm bg-white">
                                    <option value="">All</option>
                                    @foreach($types ?? [] as $type)
                                        <option value="{{ $type->id }}"{{ request('type') == $type->id ? ' selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="py-3 px-4">
                                <input type="text" name="skill" value="{{ request('skill') }}" placeholder="Search" class="w-full px-3 py-2 border rounded text-sm bg-white">
                            </th>
                            <th class="py-3 px-4">
                                <select name="status" class="w-full px-3 py-2 border rounded text-sm bg-white">
                                    <option value="">All</option>
                                    <option value="draft"{{ request('status') == 'draft' ? ' selected' : '' }}>Draft</option>
                                    <option value="active"{{ request('status') == 'active' ? ' selected' : '' }}>Active</option>
                                    <option value="inactive"{{ request('status') == 'inactive' ? ' selected' : '' }}>In-active</option>
                                </select>
                            </th>
                            <th class="py-3 px-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Filter</button>
                                    <a href="{{ route('admin.quizzes.index') ?? route('admin.quizzes.index') }}" class="px-3 py-2 border rounded text-sm">Reset</a>
                                </div>
                            </th>
                        </form>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($quizzes as $quiz)
                        <tr class="bg-white">
                            <td class="py-4 px-4">
                                <span class="inline-block bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $quiz->code ?? 'quiz_'.substr(md5($quiz->id),0,8) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-700">{{ $quiz->title }}</td>
                            <td class="py-4 px-4 text-gray-700">{{ optional($quiz->category)->name }}</td>
                            <td class="py-4 px-4 text-gray-700">{{ optional($quiz->quizType)->name ?? ($quiz->type ?? 'Quiz') }}</td>
                            <td class="py-4 px-4 text-gray-700">{{ ucfirst($quiz->visibility ?? 'public') }}</td>
                            <td class="py-4 px-4">
                                @if(($quiz->status ?? 'draft') == 'active')
                                    <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded text-sm">Active</span>
                                @elseif(($quiz->status ?? '') == 'inactive')
                                    <span class="inline-block bg-pink-100 text-pink-700 px-3 py-1 rounded text-sm">In-active</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm">Draft</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <!-- Actions dropdown (simple) -->
                                <div class="inline-block relative">
                                    <button class="px-3 py-2 border rounded inline-flex items-center">
                                        Actions
                                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M5.23 7.21a1 1 0 011.41-.02L10 10.584l3.36-3.4a1 1 0 011.42 1.41l-4.07 4.13a1 1 0 01-1.41.02l-4.07-4.13a1 1 0 01-.02-1.41z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 px-4 text-center text-gray-500">No quizzes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- pagination / rows per page UI --}}
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-600">ROWS PER PAGE:
                <select onchange="location = this.value;" class="ml-2 px-2 py-1 border rounded bg-white text-sm">
                    <option value="{{ route('admin.quizzes.index', array_merge(request()->except('page'), ['per' => 10])) }}">10</option>
                    <option value="{{ route('admin.quizzes.index', array_merge(request()->except('page'), ['per' => 25])) }}">25</option>
                    <option value="{{ route('admin.quizzes.index', array_merge(request()->except('page'), ['per' => 50])) }}">50</option>
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
