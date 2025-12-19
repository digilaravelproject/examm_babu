@extends('layouts.admin')
@section('title', 'Pending Approvals')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Pending Approvals</h1>
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
        <table class="w-full text-left">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Question</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Created By</th>
                    <th class="px-6 py-3 text-xs font-bold text-right text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($questions as $q)
                <tr>
                    <td class="px-6 py-4 text-sm">{!! strip_tags($q->question) !!}</td>
                    <td class="px-6 py-4 text-sm">{{ $q->creator->name ?? 'Unknown' }}</td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.questions.approve', $q->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="px-3 py-1 bg-[#94c940] text-white text-xs rounded hover:bg-green-600">Approve</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
