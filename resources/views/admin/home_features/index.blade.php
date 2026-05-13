@extends('layouts.admin')
@section('header', 'Manage Features')

@section('content')
    <div class="container-fluid px-2">

        {{-- Header & Add Button --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Why Choose Us</h1>
                <p class="mt-1 text-sm text-gray-600">Manage the features section on home page.</p>
            </div>
            <div>
                <a href="{{ route('admin.home-features.create') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all">
                    + Add Feature
                </a>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sort
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Icon
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Feature
                                Details</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($features as $feature)
                            <tr class="hover:bg-gray-50 transition-colors">

                                {{-- Sort Order --}}
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-700 font-bold text-sm border border-gray-300">
                                        {{ $feature->sort_order }}
                                    </span>
                                </td>

                                {{-- Icon Preview --}}
                                <td class="px-6 py-4">
                                    <div
                                        class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shadow-sm {{ $feature->bg_class }}">
                                        {{ $feature->icon }}
                                    </div>
                                </td>

                                {{-- Details --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col max-w-sm">
                                        <span class="text-base font-bold text-gray-900">{{ $feature->title }}</span>
                                        <span
                                            class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $feature->description }}</span>
                                        <div class="mt-1 text-[10px] text-gray-400">
                                            BG Class: <span
                                                class="font-mono bg-gray-100 px-1 rounded">{{ $feature->bg_class }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Status Toggle (Updated UI) --}}
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.home-features.toggle', $feature->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $feature->is_active ? 'bg-green-500' : 'bg-gray-200' }}">
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $feature->is_active ? 'translate-x-5' : 'translate-x-0' }}">
                                            </span>
                                        </button>
                                        <div
                                            class="text-[10px] font-bold mt-1 uppercase {{ $feature->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $feature->is_active ? 'Active' : 'Hidden' }}
                                        </div>
                                    </form>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit Icon --}}
                                        <a href="{{ route('admin.home-features.edit', $feature->id) }}"
                                            class="p-2 text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>

                                        {{-- Delete Icon --}}
                                        <form action="{{ route('admin.home-features.destroy', $feature->id) }}"
                                            method="POST" onsubmit="return confirm('Delete this feature permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                                title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No features found. Click "+ Add Feature" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $features->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            @endif
            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: "{{ session('error') }}"
                });
            @endif
        });
    </script>
@endpush
