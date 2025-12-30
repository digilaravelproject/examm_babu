@extends('layouts.student')

@section('title', $category['title'] ?? 'Select Category')

@section('content')
<div class="min-h-screen pb-10">

    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-slate-900 md:text-3xl">
            {{ $category['title'] ?? 'Exam Categories' }}
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ $category['subtitle'] ?? 'Select your goal to continue.' }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @forelse($categories as $cat)

            <div class="flex flex-col h-full group">

                <div class="relative flex flex-col flex-grow h-full overflow-hidden transition-all duration-300 bg-white border shadow-sm border-slate-200 rounded-xl hover:shadow-lg hover:-translate-y-1 hover:border-blue-300">

                    <div class="absolute top-0 left-0 w-full h-1 transition-opacity duration-300 opacity-0 bg-gradient-to-r from-blue-600 to-sky-400 group-hover:opacity-100"></div>

                    <div class="flex flex-col flex-grow p-4 text-center">

                        <div class="flex items-center justify-center w-10 h-10 mx-auto mb-3 text-sm font-bold text-white transition-transform duration-300 rounded-lg shadow-sm group-hover:scale-110"
                             style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-sky));">
                            {{ mb_substr($cat['name'], 0, 1) }}
                        </div>

                        <div class="flex items-center justify-center flex-grow min-h-[3rem] mb-2">
                            <h3 class="text-sm font-bold leading-tight break-words text-slate-800"
                                title="{{ $cat['name'] }}">
                                {{ $cat['name'] }}
                            </h3>
                        </div>

                        <p class="hidden mb-3 text-xs leading-snug text-slate-400 md:block line-clamp-2">
                            {{ $cat['short_description'] ?? 'Select to view details.' }}
                        </p>

                        <div class="mt-auto"></div>

                        <div class="mt-3">
                            <a href="{{ route('store.categories.show', $cat['slug']) }}"
                               class="flex items-center justify-center w-full py-2 text-xs font-bold transition-all duration-200 rounded-lg group-hover:text-white"
                               style="background-color: #f8fafc; color: var(--sidebar-bg); border: 1px solid #e2e8f0;"
                               onmouseover="this.style.backgroundColor='var(--brand-blue)'; this.style.color='white'; this.style.borderColor='var(--brand-blue)';"
                               onmouseout="this.style.backgroundColor='#f8fafc'; this.style.color='var(--sidebar-bg)'; this.style.borderColor='#e2e8f0';">
                                View Exams
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="py-12 text-center col-span-full">
                <div class="inline-block p-3 mb-2 rounded-full bg-slate-50">
                    <i class="text-2xl text-slate-400 fas fa-folder-open"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">No Categories Found</h3>
            </div>
        @endforelse
    </div>

</div>
@endsection
