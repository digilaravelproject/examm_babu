@extends('layouts.admin')

@section('header', 'Hero Slider Management')

@section('content')
    <div class="container-fluid px-2">

        {{-- Top Header & Add Button --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hero Sliders</h1>
                <p class="mt-1 text-sm text-gray-600">Manage home page banners, visibility, and ordering.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.hero-slides.create') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Slide
                </a>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Sort
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Slide Details
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Visual Preview
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($slides as $slide)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">

                                {{-- 1. Sort Order --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-700 font-bold text-sm border border-gray-300 shadow-sm">
                                        {{ $slide->sort_order }}
                                    </span>
                                </td>

                                {{-- 2. Slide Info --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col max-w-xs">
                                        <span class="text-sm font-bold text-gray-900 truncate" title="{{ $slide->title }}">
                                            {{ $slide->title }}
                                        </span>
                                        <span class="text-xs text-gray-500 mt-1 truncate" title="{{ $slide->description }}">
                                            {{ Str::limit($slide->description, 40) }}
                                        </span>
                                        <span
                                            class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 w-fit uppercase tracking-wide border border-blue-100">
                                            {{ $slide->badge_text }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 3. Visual Preview --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        {{-- Icon Box with Gradient --}}
                                        <div class="h-10 w-10 rounded-lg shadow-sm flex items-center justify-center text-lg border border-gray-100 relative overflow-hidden"
                                            style="background: linear-gradient(to bottom right, {{ $slide->bg_gradient_start }}, {{ $slide->bg_gradient_end }}); color: white;">
                                            <span class="relative z-10">{{ $slide->icon_top }}</span>
                                        </div>

                                        {{-- Color Hex Info --}}
                                        <div class="text-xs text-gray-500 space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-3 h-3 rounded-full border border-gray-200 shadow-sm"
                                                    style="background: {{ $slide->bg_gradient_start }}"></span>
                                                <span class="font-mono text-[10px]">{{ $slide->bg_gradient_start }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-3 h-3 rounded-full border border-gray-200 shadow-sm"
                                                    style="background: {{ $slide->bg_gradient_end }}"></span>
                                                <span class="font-mono text-[10px]">{{ $slide->bg_gradient_end }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 4. Status Toggle --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="{{ route('admin.hero-slides.toggle', $slide->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $slide->is_active ? 'bg-green-500' : 'bg-gray-200' }}"
                                            role="switch" aria-checked="{{ $slide->is_active }}">
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $slide->is_active ? 'translate-x-5' : 'translate-x-0' }}">
                                            </span>
                                        </button>
                                        <div
                                            class="text-[10px] font-bold mt-1 uppercase {{ $slide->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $slide->is_active ? 'Active' : 'Hidden' }}
                                        </div>
                                    </form>
                                </td>

                                {{-- 5. Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.hero-slides.edit', $slide->id) }}"
                                            class="p-2 text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            title="Edit Slide">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.hero-slides.destroy', $slide->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this slide permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                title="Delete Slide">
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
                                <td colspan="5" class="px-6 py-12 text-center bg-gray-50">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-900 text-sm font-semibold">No slides found</p>
                                        <p class="text-gray-500 text-xs mt-1 mb-4">Get started by creating a new hero
                                            banner.</p>
                                        <a href="{{ route('admin.hero-slides.create') }}"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold hover:underline">
                                            Create your first slide &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination Links --}}
        <div class="mt-6">
            {{ $slides->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- SWEETALERT TOAST CONFIGURATION ---
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // 1. Success Message
            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            @endif

            // 2. Error Message
            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: "{{ session('error') }}"
                });
            @endif

            // 3. Validation Errors (e.g. form submission failed)
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    Toast.fire({
                        icon: 'warning',
                        title: "{{ $error }}"
                    });
                @endforeach
            @endif
        });
    </script>
@endpush
