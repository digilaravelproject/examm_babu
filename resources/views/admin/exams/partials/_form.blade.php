@csrf
@if ($exam->id)
    @method('PUT')
@endif

{{-- Error Handling --}}
@if ($errors->any())
    <div class="p-4 mb-6 border-l-4 rounded-lg bg-red-50 border-red-500/50">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm font-bold text-red-800 uppercase tracking-tight">Please Fix Errors:</span>
        </div>
        <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5 ml-7">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- MAIN WRAPPER with x-data for Dependent Dropdowns --}}
<div x-data="examForm()" class="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">

    {{-- 1. Exam Title --}}
    <div class="space-y-2 md:col-span-2">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Exam Title <span
                class="text-[var(--brand-pink)]">*</span></label>
        <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
            class="w-full px-4 py-3 text-sm transition-all border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-opacity-10 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]"
            placeholder="Enter Exam Title">
    </div>

    {{-- 2. Category - Custom Dropdown (NEW) --}}
    <div class="space-y-2" x-data="{ open: false }">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Category <span
                class="text-[var(--brand-pink)]">*</span></label>
        <div class="relative">
            <input type="hidden" name="category_id" :value="categoryId">
            <button type="button" @click="open = !open" @click.away="open = false"
                class="relative w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 focus:outline-none focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all flex justify-between items-center">
                <span x-text="categoryName || 'Select Category'"
                    :class="categoryId ? 'text-gray-900' : 'text-gray-400'"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition
                class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-60 overflow-y-auto no-scrollbar"
                style="display: none;">
                @foreach ($categories as $cat)
                    <div @click="setCategory('{{ $cat->id }}', '{{ $cat->name }}'); open = false"
                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white flex justify-between items-center group">
                        <span>{{ $cat->name }}</span>
                        <svg x-show="categoryId == '{{ $cat->id }}'"
                            class="w-4 h-4 text-[var(--brand-blue)] group-hover:text-white" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. Sub Category - Custom Dropdown (DEPENDENT on Category) --}}
    <div class="space-y-2" x-data="{ open: false }">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Sub Category <span
                class="text-[var(--brand-pink)]">*</span></label>
        <div class="relative">
            <input type="hidden" name="sub_category_id" :value="subCategoryId">
            <button type="button" @click="if(categoryId) open = !open" @click.away="open = false"
                :disabled="!categoryId" :class="!categoryId ? 'cursor-not-allowed opacity-60' : ''"
                class="relative w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 focus:outline-none focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all flex justify-between items-center">
                <span x-text="subCategoryName || (categoryId ? 'Select Sub Category' : 'Select Category First')"
                    :class="subCategoryId ? 'text-gray-900' : 'text-gray-400'"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition
                class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-60 overflow-y-auto no-scrollbar"
                style="display: none;">
                <template x-for="sub in filteredSubCats" :key="sub.id">
                    <div @click="setSubCategory(sub.id, sub.name); open = false"
                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white flex justify-between items-center group">
                        <span x-text="sub.name"></span>
                        <svg x-show="subCategoryId == sub.id"
                            class="w-4 h-4 text-[var(--brand-blue)] group-hover:text-white" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                    </div>
                </template>
                <div x-show="filteredSubCats.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                    No Sub Categories found.
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Micro Category - Custom Dropdown (DEPENDENT on Sub Category) --}}
    <div class="space-y-2" x-data="{ open: false }">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Micro Category <span
                class="text-xs text-gray-400 normal-case">(Optional)</span></label>
        <div class="relative">
            <input type="hidden" name="micro_category_id" :value="microCategoryId">
            <button type="button" @click="if(subCategoryId) open = !open" @click.away="open = false"
                :disabled="!subCategoryId" :class="!subCategoryId ? 'cursor-not-allowed opacity-60' : ''"
                class="relative w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 focus:outline-none focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all flex justify-between items-center">
                <span
                    x-text="microCategoryName || (subCategoryId ? 'Select Micro Category (Optional)' : 'Select Sub Category First')"
                    :class="microCategoryId ? 'text-gray-900' : 'text-gray-400'"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition
                class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-60 overflow-y-auto no-scrollbar"
                style="display: none;">
                <div @click="setMicroCategory('', ''); open = false"
                    class="px-4 py-2.5 text-sm cursor-pointer text-gray-500 hover:bg-gray-100 flex justify-between items-center border-b border-gray-50">
                    <span>-- None --</span>
                </div>
                <template x-for="micro in filteredMicros" :key="micro.id">
                    <div @click="setMicroCategory(micro.id, micro.name); open = false"
                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white flex justify-between items-center group">
                        <span x-text="micro.name"></span>
                        <svg x-show="microCategoryId == micro.id"
                            class="w-4 h-4 text-[var(--brand-blue)] group-hover:text-white" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                    </div>
                </template>
                <div x-show="filteredMicros.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                    No Micro Categories found.
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Exam Type - Custom Dropdown --}}
    <div class="space-y-2 md:col-span-2" x-data="{ open: false, selected: '{{ $exam->exam_type_id ? $examTypes->firstWhere('id', $exam->exam_type_id)->name : 'Select Exam Type' }}', value: '{{ old('exam_type_id', $exam->exam_type_id) }}' }">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Exam Type</label>
        <div class="relative">
            <input type="hidden" name="exam_type_id" :value="value">
            <button type="button" @click="open = !open" @click.away="open = false"
                class="relative w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-xl bg-gray-50/50 focus:outline-none focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all flex justify-between items-center">
                <span x-text="selected" :class="value ? 'text-gray-900' : 'text-gray-400'"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7">
                    </path>
                </svg>
            </button>
            <div x-show="open" x-transition
                class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-60 overflow-y-auto no-scrollbar"
                style="display: none;">
                @foreach ($examTypes as $type)
                    <div @click="selected = '{{ $type->name }}'; value = '{{ $type->id }}'; open = false"
                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[var(--brand-blue)] hover:text-white flex justify-between items-center group">
                        <span>{{ $type->name }}</span>
                        <svg x-show="value == '{{ $type->id }}'"
                            class="w-4 h-4 text-[var(--brand-blue)] group-hover:text-white" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Divider --}}
    <div class="md:col-span-2 py-2">
        <div class="w-full border-t border-dashed border-gray-200"></div>
    </div>

    {{-- Toggle Buttons Section --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:col-span-2">

        {{-- Toggle 1: Pricing Model --}}
        <div x-data="{ isPaid: {{ old('pricing_type', $exam->is_paid ? 'true' : 'false') }} }"
            class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 transition-all hover:bg-gray-50">
            <div>
                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Pricing Model</label>
                <p class="text-[10px] font-bold" :class="isPaid ? 'text-[var(--brand-blue)]' : 'text-green-600'"
                    x-text="isPaid ? 'PAID EXAM' : 'FREE EXAM'"></p>
                <input type="hidden" name="pricing_type" :value="isPaid ? 'paid' : 'free'">
            </div>
            <button type="button" @click="isPaid = !isPaid"
                :class="isPaid ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'"
                class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                <span :class="isPaid ? 'translate-x-5' : 'translate-x-0'"
                    class="inline-block h-5 w-5 mt-0.5 ml-0.5 transform rounded-full bg-white transition duration-200 shadow-sm"></span>
            </button>
        </div>

        {{-- Toggle 2: Points Access --}}
        <div x-data="{ canRedeem: {{ old('can_redeem', $exam->can_redeem ? 'true' : 'false') }} }"
            class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 transition-all hover:bg-gray-50">
            <div>
                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Points Redeem</label>
                <p class="text-[10px] font-bold" :class="canRedeem ? 'text-[var(--brand-blue)]' : 'text-gray-400'"
                    x-text="canRedeem ? 'POINTS REQUIRED' : 'NO POINTS NEEDED'"></p>
                <input type="hidden" name="can_redeem" :value="canRedeem ? '1' : '0'">
            </div>
            <button type="button" @click="canRedeem = !canRedeem"
                :class="canRedeem ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'"
                class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                <span :class="canRedeem ? 'translate-x-5' : 'translate-x-0'"
                    class="inline-block h-5 w-5 mt-0.5 ml-0.5 transform rounded-full bg-white transition duration-200 shadow-sm"></span>
            </button>
        </div>

        {{-- Toggle 3: Visibility --}}
        <div x-data="{ isPrivate: {{ old('visibility', $exam->is_private ? 'true' : 'false') }} }"
            class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 transition-all hover:bg-gray-50">
            <div>
                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Visibility</label>
                <p class="text-[10px] font-bold"
                    :class="isPrivate ? 'text-[var(--brand-pink)]' : 'text-[var(--brand-blue)]'"
                    x-text="isPrivate ? 'PRIVATE (LINK ONLY)' : 'PUBLIC (GLOBAL)'"></p>
                <input type="hidden" name="visibility" :value="isPrivate ? 'private' : 'public'">
            </div>
            <button type="button" @click="isPrivate = !isPrivate"
                :class="isPrivate ? 'bg-[var(--brand-pink)]' : 'bg-[var(--brand-blue)]'"
                class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                <span :class="isPrivate ? 'translate-x-5' : 'translate-x-0'"
                    class="inline-block h-5 w-5 mt-0.5 ml-0.5 transform rounded-full bg-white transition duration-200 shadow-sm"></span>
            </button>
        </div>

        {{-- Toggle 4: Publishing Status --}}
        <div x-data="{ isPublished: {{ old('status', $exam->is_active ? 'true' : 'false') }} }"
            class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 transition-all hover:bg-gray-50">
            <div>
                <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Status</label>
                <p class="text-[10px] font-bold" :class="isPublished ? 'text-[var(--brand-green)]' : 'text-gray-400'"
                    x-text="isPublished ? 'LIVE' : 'DRAFT'"></p>
                <input type="hidden" name="status" :value="isPublished ? 'published' : 'draft'">
            </div>
            <button type="button" @click="isPublished = !isPublished"
                :class="isPublished ? 'bg-[var(--brand-green)]' : 'bg-gray-300'"
                class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                <span :class="isPublished ? 'translate-x-5' : 'translate-x-0'"
                    class="inline-block h-5 w-5 mt-0.5 ml-0.5 transform rounded-full bg-white transition duration-200 shadow-sm"></span>
            </button>
        </div>

        {{-- Topic Toggle --}}
        <div x-data="{ enableTopic: {{ old('topic_id', $exam->topic_id ?? false) ? 'true' : 'false' }}, topicId: '{{ old('topic_id', $exam->topic_id) }}' }" class="grid grid-cols-12 gap-4 mt-6 col-span-2">
            {{-- LEFT: Topic Toggle --}}
            <div class="col-span-12">
                <div
                    class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 transition-all hover:bg-gray-50 h-full">
                    <div>
                        <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Topic</label>
                        <p class="text-[10px] font-bold"
                            :class="enableTopic ? 'text-[var(--brand-blue)]' : 'text-gray-400'"
                            x-text="enableTopic ? 'TOPIC ENABLED' : 'NO TOPIC'"></p>
                    </div>
                    <button type="button" @click="enableTopic = !enableTopic"
                        :class="enableTopic ? 'bg-[var(--brand-blue)]' : 'bg-gray-300'"
                        class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                        <span :class="enableTopic ? 'translate-x-5' : 'translate-x-0'"
                            class="inline-block h-5 w-5 mt-0.5 ml-0.5 transform rounded-full bg-white transition duration-200 shadow-sm"></span>
                    </button>
                </div>
            </div>

            {{-- RIGHT: Topic Dropdown --}}
            <div class="col-span-12" x-show="enableTopic" x-transition>
                <template x-if="!enableTopic">
                    <input type="hidden" name="topic_id" value="">
                </template>
                <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-100 h-full">
                    <label class="text-[11px] font-black text-gray-500 uppercase tracking-widest block mb-2">Select
                        Topic</label>
                    <select x-model="topicId" x-bind:disabled="!enableTopic" name="topic_id"
                        class="w-full px-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)] bg-white">
                        <option value="">-- Select Topic --</option>
                        @foreach ($topics as $topic)
                            <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="space-y-2 md:col-span-2">
        <label class="text-[11px] font-black text-gray-500 uppercase tracking-[0.1em]">Description</label>
        <textarea name="description" rows="3"
            class="w-full px-4 py-3 text-sm border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:ring-4 focus:ring-opacity-10 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]"
            placeholder="Brief exam instructions...">{{ old('description', $exam->description) }}</textarea>
    </div>

    {{-- Action Button --}}
    <div class="flex items-center justify-end pt-4 mt-2 md:col-span-2">
        <button type="submit"
            class="flex items-center gap-3 px-10 py-4 text-sm font-bold text-white transition-all rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 active:scale-95"
            style="background-color: var(--brand-blue);">
            <span>{{ $exam->id ? 'Update Changes' : 'Save & Continue' }}</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6">
                </path>
            </svg>
        </button>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

{{-- JS Logic for Dependent Dropdown --}}
<script>
    function examForm() {
        return {
            categoryId: '{{ old('category_id', $exam->category_id) }}',
            categoryName: '{{ $exam->category_id ? $categories->firstWhere('id', $exam->category_id)->name ?? 'Selected' : '' }}',

            subCategoryId: '{{ old('sub_category_id', $exam->sub_category_id) }}',
            subCategoryName: '{{ $exam->sub_category_id ? $subCategories->firstWhere('id', $exam->sub_category_id)->name ?? 'Selected' : '' }}',

            microCategoryId: '{{ old('micro_category_id', $exam->micro_category_id) }}',
            microCategoryName: '{{ $exam->micro_category_id ? $microCategories->firstWhere('id', $exam->micro_category_id)->name ?? 'Selected' : '' }}',

            // Pass PHP Array to JS
            allSubCats: @json($subCategories),
            allMicros: @json($microCategories),

            // Computed property simulation for Alpine
            get filteredSubCats() {
                if (!this.categoryId) return [];
                return this.allSubCats.filter(s => s.category_id == this.categoryId);
            },

            get filteredMicros() {
                if (!this.subCategoryId) return [];
                return this.allMicros.filter(m => m.sub_category_id == this.subCategoryId);
            },

            setCategory(id, name) {
                this.categoryId = id;
                this.categoryName = name;
                // Reset dependent fields
                this.subCategoryId = '';
                this.subCategoryName = '';
                this.microCategoryId = '';
                this.microCategoryName = '';
            },

            setSubCategory(id, name) {
                this.subCategoryId = id;
                this.subCategoryName = name;
                // Reset dependent fields
                this.microCategoryId = '';
                this.microCategoryName = '';
            },

            setMicroCategory(id, name) {
                this.microCategoryId = id;
                this.microCategoryName = name;
            }
        }
    }
</script>
