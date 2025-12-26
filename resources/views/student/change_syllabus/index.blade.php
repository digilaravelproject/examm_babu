@extends('layouts.student')

@section('title', 'Select Syllabus')

@section('content')
<div class="min-h-screen py-8 bg-slate-50/50"
     x-data="{
        search: '',
        loading: false,
        fetchResults() {
            this.loading = true;
            fetch('{{ route('student.change_syllabus') }}?search=' + this.search, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('syllabus-container').innerHTML = html;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
        }
     }">

    <div class="container px-4 mx-auto max-w-7xl">

        <div class="flex flex-col gap-6 mb-10 md:flex-row md:items-end md:justify-between">
            <div class="md:w-1/2">
                <h1 class="text-3xl font-extrabold tracking-tight" style="color: var(--sidebar-bg)">
                    Select Your Goal
                </h1>
                <p class="mt-2 text-lg text-slate-500">
                    Choose the syllabus you want to focus on today.
                </p>
            </div>

            <div class="relative w-full md:w-[400px]">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas"
                       :class="loading ? 'fa-spinner fa-spin' : 'fa-search'"
                       :style="loading ? 'color: var(--brand-pink)' : 'color: #94a3b8'"></i>
                </div>

                <input type="text"
                       x-model="search"
                       @input.debounce.400ms="fetchResults()"
                       placeholder="Search exams (e.g. MPSC, SSC)..."
                       class="w-full py-3 pr-4 text-base transition-shadow bg-white border-0 shadow-sm outline-none pl-11 ring-1 ring-slate-200 rounded-xl focus:ring-2 placeholder:text-slate-400"
                       style="--tw-ring-color: var(--brand-sky); caret-color: var(--brand-pink);">
            </div>
        </div>

        <div id="syllabus-container">
            @include('student.change_syllabus.partials.card_list')
        </div>

    </div>
</div>
@endsection

@push('styles')
{{-- <style>
    /* Confirming Root Variables are present */
    :root {
        --brand-blue: #0777be;
        --brand-pink: #f062a4;
        --brand-green: #94c940;
        --brand-sky: #7fd2ea;
        --sidebar-bg: #0f172a;
    }
</style> --}}
@endpush
