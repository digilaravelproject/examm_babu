@extends('layouts.admin')

@section('title', 'Manage Advertisements')
@section('header', 'Manage Advertisements')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Advertisements</h1>
            <p class="mt-1 text-sm text-gray-500">Create and manage banner advertisements for exam reports.</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.advertisements.create') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all rounded-lg shadow-md bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Advertisement
            </a>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Preview</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Location</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($advertisements as $ad)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->title }}" class="object-cover w-24 h-12 rounded border">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $ad->title }}</div>
                                @if($ad->link_url)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $ad->link_url }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $ad->location)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="toggleStatus({{ $ad->id }})" id="status-btn-{{ $ad->id }}"
                                    class="px-3 py-1 text-xs font-bold rounded-full transition-colors {{ $ad->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $ad->status ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.advertisements.edit', $ad->id) }}" class="p-2 text-indigo-600 border border-indigo-100 rounded-lg hover:bg-indigo-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.advertisements.destroy', $ad->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ad?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 border border-red-100 rounded-lg hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No advertisements found. <a href="{{ route('admin.advertisements.create') }}" class="text-indigo-600 hover:underline">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($advertisements->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $advertisements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(id) {
        fetch(`{{ url('admin/advertisements') }}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById(`status-btn-${id}`);
                if (data.status) {
                    btn.classList.remove('bg-red-100', 'text-red-700');
                    btn.classList.add('bg-green-100', 'text-green-700');
                    btn.innerText = 'Active';
                } else {
                    btn.classList.remove('bg-green-100', 'text-green-700');
                    btn.classList.add('bg-red-100', 'text-red-700');
                    btn.innerText = 'Inactive';
                }
            }
        });
    }
</script>
@endpush
