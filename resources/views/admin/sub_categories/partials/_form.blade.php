{{-- Error Handling (Optional: Agar parent view me nahi hai to yahan dikhega) --}}
@if ($errors->any())
    <div class="mb-6">
        <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
            <ul class="pl-5 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">

    {{-- 1. Parent Category --}}
    <div class="space-y-1.5">
        <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
            Parent Category <span class="text-red-500">*</span>
        </label>
        <select name="category_id" required
            class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium bg-white">
            <option value="">Select Parent Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $subCategory->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- 2. Sub-Category Type --}}
    <div class="space-y-1.5">
        <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
            Sub-Category Type <span class="text-red-500">*</span>
        </label>
        <select name="sub_category_type_id" required
            class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium bg-white">
            <option value="">Select Type</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" {{ old('sub_category_type_id', $subCategory->sub_category_type_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- 3. Name --}}
    <div class="space-y-1.5 md:col-span-2">
        <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
            Sub-Category Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $subCategory->name ?? '') }}" required
            placeholder="e.g. MPSC Rajyaseva"
            class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium">
    </div>

    {{-- 4. Code (Visible only on Edit) --}}
    @if (isset($subCategory) && $subCategory->exists)
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-xs font-bold tracking-wide text-gray-400 uppercase">
                Code (Read Only)
            </label>
            <div class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-sm font-mono text-gray-500">
                {{ $subCategory->code }}
            </div>
        </div>
    @endif
</div>

{{-- Middle Grid: Image & Status --}}
<div class="grid items-start grid-cols-1 gap-8 mt-6 md:grid-cols-2">

    {{-- 5. Icon / Image --}}
    <div class="space-y-3">
        <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Icon / Image</label>
        <div class="flex items-center gap-4">
            {{-- Preview Box (AlpineJS logic parent form se lega) --}}
            <div class="flex items-center justify-center w-20 h-20 overflow-hidden border-2 border-gray-300 border-dashed rounded-xl bg-gray-50">
                <template x-if="imageUrl">
                    <img :src="imageUrl" class="object-cover w-full h-full">
                </template>
                <template x-if="!imageUrl">
                    <span class="text-[10px] text-gray-400">No Image</span>
                </template>
            </div>

            {{-- File Input --}}
            <div class="flex-1">
                <input type="file" name="image_path" @change="fileChosen" accept="image/*"
                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0777be] hover:file:bg-blue-100 transition">
                <p class="mt-1 text-[10px] text-gray-400">SVG, PNG, JPG (Max 2MB)</p>
            </div>
        </div>
    </div>

    {{-- 6. Status --}}
    <div class="space-y-1.5">
        <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Status</label>
        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:bg-gray-50 transition">
            <span class="text-sm font-bold text-gray-700">Active?</span>
            <div class="relative">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $subCategory->is_active ?? 1) ? 'checked' : '' }}
                    class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#94c940] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </div>
        </label>
    </div>
</div>

{{-- 7. Short Description --}}
<div class="mt-6 space-y-1.5">
    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Short Description</label>
    <textarea name="short_description" rows="2"
        placeholder="Brief overview..."
        class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm">{{ old('short_description', $subCategory->short_description ?? '') }}</textarea>
</div>
