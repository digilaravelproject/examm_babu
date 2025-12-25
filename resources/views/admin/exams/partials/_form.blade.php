@csrf
@if($exam->id)
    @method('PUT')
@endif

{{-- Error Handling --}}
@if ($errors->any())
    <div class="p-4 mb-6 text-sm text-red-600 rounded-lg bg-red-50">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 p-6 bg-white border border-gray-200 shadow-sm md:grid-cols-2 rounded-xl">

    {{-- 1. Title --}}
    <div class="space-y-1 md:col-span-2">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Title <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
               class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]"
               placeholder="Enter Exam Title">
    </div>

    {{-- 2. Sub Category --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Sub Category <span class="text-red-500">*</span></label>
        <select name="sub_category_id" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="">Select Sub Category</option>
            @foreach($subCategories as $sub)
                <option value="{{ $sub->id }}" {{ old('sub_category_id', $exam->sub_category_id) == $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- 3. Exam Type --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Exam Type <span class="text-red-500">*</span></label>
        <select name="exam_type_id" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="">Select Exam Type</option>
            @foreach($examTypes as $type)
                <option value="{{ $type->id }}" {{ old('exam_type_id', $exam->exam_type_id) == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2 border-t border-gray-100 my-2"></div>

    {{-- 4. Paid / Free --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Paid <span class="text-red-500">*</span></label>
        <select name="pricing_type" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="paid" {{ old('pricing_type', $exam->is_paid ? 'paid' : 'free') == 'paid' ? 'selected' : '' }}>
                Paid (Accessible to only paid users)
            </option>
            <option value="free" {{ old('pricing_type', $exam->is_paid ? 'paid' : 'free') == 'free' ? 'selected' : '' }}>
                Free (Anyone can access)
            </option>
        </select>
    </div>

    {{-- 5. Can Access with Points --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Can access with Points</label>
        <select name="can_redeem" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="0" {{ old('can_redeem', $exam->can_redeem) == 0 ? 'selected' : '' }}>
                No (Anyone can access)
            </option>
            <option value="1" {{ old('can_redeem', $exam->can_redeem) == 1 ? 'selected' : '' }}>
                Yes (User should redeem with points)
            </option>
        </select>
    </div>

    {{-- 6. Visibility --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Visibility <span class="text-red-500">*</span></label>
        <select name="visibility" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="public" {{ old('visibility', $exam->is_private ? 'private' : 'public') == 'public' ? 'selected' : '' }}>
                Public (Anyone can access)
            </option>
            <option value="private" {{ old('visibility', $exam->is_private ? 'private' : 'public') == 'private' ? 'selected' : '' }}>
                Private (Only scheduled user groups)
            </option>
        </select>
    </div>

    {{-- 7. Status --}}
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Status <span class="text-red-500">*</span></label>
        <select name="status" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">
            <option value="published" {{ old('status', $exam->is_active ? 'published' : 'draft') == 'published' ? 'selected' : '' }}>
                Published (Shown Everywhere)
            </option>
            <option value="draft" {{ old('status', $exam->is_active ? 'published' : 'draft') == 'draft' ? 'selected' : '' }}>
                Draft (Not Shown)
            </option>
        </select>
    </div>

    {{-- 8. Description --}}
    <div class="space-y-1 md:col-span-2">
        <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Description</label>
        <textarea name="description" rows="4" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#0777be] focus:border-[#0777be]">{{ old('description', $exam->description) }}</textarea>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end mt-4 md:col-span-2">
        <button type="submit" class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all bg-[#0777be] rounded-lg shadow-md hover:bg-[#0666a3] hover:shadow-lg">
            <span>{{ $exam->id ? 'Update Exam' : 'Save & Next' }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </div>
</div>
