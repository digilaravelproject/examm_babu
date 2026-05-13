@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Create Sub-Category')
@section('header', 'Create Sub-Category')

@php
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);
    $params = (!$isAdmin && $currentRole) ? ['role' => $currentRole] : [];
@endphp

@section('content')
    <div class="py-6 mx-auto space-y-6 max-w-7xl">
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Sub-Category</h1>
                <p class="text-sm text-gray-500">Add a new topic under a primary category.</p>
            </div>
            <a href="{{ route($routePrefix . 'sub-categories.index', $params) }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                Back to List
            </a>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl" x-data="imagePreview()">
            <form action="{{ route($routePrefix . 'sub-categories.store', $params) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @include('admin.sub_categories.partials._form')

                <div class="flex justify-end gap-3 pt-6 border-t">
                    <a href="{{ route($routePrefix . 'sub-categories.index', $params) }}" class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-[#0666a3]">Save Sub-Category</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function imagePreview() {
        return {
            imageUrl: null,
            fileChosen(e) { let f = e.target.files[0]; if(f){ let r = new FileReader(); r.onload = (ex) => this.imageUrl = ex.target.result; r.readAsDataURL(f); } }
        }
    }
    </script>
@endsection
