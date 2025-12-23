<div class="p-6 space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Section Selection --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-gray-600 uppercase">Parent Section *</label>
            <select name="section_id" required class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:ring-[#0777be]">
                <option value="">Select Section</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ old('section_id', $skill->section_id ?? '') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Skill Name --}}
        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-gray-600 uppercase">Skill Name *</label>
            <input type="text" name="name" value="{{ old('name', $skill->name ?? '') }}" required class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:ring-[#0777be]">
        </div>
    </div>

    <div>
        <label class="block mb-2 text-xs font-bold text-gray-600 uppercase">Short Description</label>
        <textarea name="short_description" rows="2" class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:ring-[#0777be]">{{ old('short_description', $skill->short_description ?? '') }}</textarea>
    </div>

    <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
        <span class="text-sm font-bold text-gray-700">Set as Active</span>
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $skill->is_active ?? 1) ? 'checked' : '' }} class="w-5 h-5 text-[#94c940] rounded border-gray-300">
    </div>
</div>
