@extends(auth()->user()->hasRole('instructor') ? 'layouts.instructor' : 'layouts.admin')

@section('title', 'Edit Sub-Category')

@php
    $isAdmin = request()->routeIs('admin.*');
    $routePrefix = $isAdmin ? 'admin.' : 'panel.';
    $currentRole = request()->route('role') ?? request()->segment(1);
    $params = (!$isAdmin && $currentRole) ? ['role' => $currentRole] : [];

    // Merge ID for Update URL
    $updateParams = array_merge($params, ['sub_category' => $subCategory->id]);
@endphp

@section('content')
    <div class="py-6 mx-auto space-y-6 max-w-7xl">
        <div class="flex items-center justify-between px-4 sm:px-0">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
                    Edit Sub-Category
                    @if ($subCategory->is_active)
                        <span class="px-2 py-0.5 text-[10px] bg-[#94c940] text-white rounded-full uppercase tracking-wider font-bold shadow-sm">Active</span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] bg-orange-500 text-white rounded-full uppercase tracking-wider font-bold shadow-sm">Inactive</span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500">System Code: <span class="font-mono text-[#f062a4] font-bold">{{ $subCategory->code }}</span></p>
            </div>
            <a href="{{ route($routePrefix . 'sub-categories.index', $params) }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                Back to List
            </a>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl" x-data="imagePreview()">
            <form action="{{ route($routePrefix . 'sub-categories.update', $updateParams) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf @method('PUT')
                @include('admin.sub_categories.partials._form')

                <div class="flex items-center justify-end gap-3 px-6 py-4 pt-6 mt-4 -mx-6 -mb-6 border-t bg-gray-50">
                    <a href="{{ route($routePrefix . 'sub-categories.index', $params) }}" class="px-4 py-2 text-xs font-bold text-gray-500 uppercase transition hover:text-gray-700">Cancel</a>
                    <button type="submit" class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-[#0666a3] transition-all">Update Sub-Category</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function imagePreview() {
            return {
                imageUrl: '{{ $subCategory->image_path ? asset($subCategory->image_path) : '' }}',
                fileChosen(e) { let f = e.target.files[0]; if(f){ let r = new FileReader(); r.onload = (ex) => this.imageUrl = ex.target.result; r.readAsDataURL(f); } }
            }
        }
    </script>
@endsection
