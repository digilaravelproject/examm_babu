<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse($categories as $category)
        <div class="h-full group">

            <div class="relative flex flex-col h-full bg-white border border-slate-200 shadow-sm rounded-2xl hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group-hover:border-[var(--brand-sky)]">

                <div class="flex flex-col flex-grow p-6">

                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center justify-center flex-shrink-0 text-2xl font-bold text-white transition-transform duration-300 shadow-md w-14 h-14 rounded-xl group-hover:scale-110"
                             style="background: linear-gradient(135deg, var(--brand-blue), var(--brand-sky));">
                            {{ mb_substr($category->name, 0, 1) }}
                        </div>

                        <span class="px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full"
                              style="background-color: rgba(127, 210, 234, 0.15); color: var(--brand-blue); border: 1px solid rgba(127, 210, 234, 0.3);">
                            {{ $category->category->name ?? 'Exam' }}
                        </span>
                    </div>

                    <h3 class="mb-1 text-lg font-bold leading-tight transition-colors line-clamp-2"
                        style="color: var(--sidebar-bg);"
                        onmouseover="this.style.color='var(--brand-blue)'"
                        onmouseout="this.style.color='var(--sidebar-bg)'"
                        title="{{ $category->name }}">
                        {{ $category->name }}
                    </h3>

                    <p class="text-sm font-medium text-slate-500">
                        {{ $category->subCategoryType->name ?? 'General' }}
                    </p>
                </div>

                <div class="p-6 pt-0 mt-auto">
                    <form action="{{ route('student.update_syllabus') }}" method="POST">
                        @csrf
                        <input type="hidden" name="category" value="{{ $category->code }}">

                        <button type="submit"
                                class="flex items-center justify-center w-full px-4 py-3 text-sm font-bold text-white transition-all duration-300 rounded-xl group-hover:shadow-md"
                                style="background-color: var(--sidebar-bg);"
                                onmouseover="this.style.backgroundColor='var(--brand-blue)'"
                                onmouseout="this.style.backgroundColor='var(--sidebar-bg)'">
                            <span>Select Syllabus</span>
                            <i class="ml-2 fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    @empty
        <div class="py-12 text-center col-span-full">
            <div class="inline-block p-4 mb-3 rounded-full bg-slate-50" style="color: var(--brand-blue)">
                <i class="fas fa-search fa-2x"></i>
            </div>
            <h3 class="text-lg font-semibold" style="color: var(--sidebar-bg)">No syllabus found</h3>
            <p class="text-slate-500">Try adjusting your search terms.</p>
        </div>
    @endforelse
</div>
