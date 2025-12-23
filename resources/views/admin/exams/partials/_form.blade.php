@csrf
@if($exam->id)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-6 p-6 bg-white border md:grid-cols-2 rounded-xl">
    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase">Exam Title *</label>
        <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
               class="w-full border-gray-300 rounded-lg focus:ring-[#0777be]">
    </div>

    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase">Exam Type *</label>
        <select name="exam_type_id" class="w-full border-gray-300 rounded-lg">
            @foreach($examTypes as $type)
                <option value="{{ $type->id }}" {{ old('exam_type_id', $exam->exam_type_id) == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase">Sub Category *</label>
        <select name="sub_category_id" class="w-full border-gray-300 rounded-lg">
            @foreach($subCategories as $sub)
                <option value="{{ $sub->id }}" {{ old('sub_category_id', $exam->sub_category_id) == $sub->id ? 'selected' : '' }}>
                    {{ $sub->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="space-y-1">
        <label class="text-xs font-bold text-gray-600 uppercase">Exam Mode</label>
        <select name="exam_mode" class="w-full border-gray-300 rounded-lg">
            <option value="objective" {{ $exam->exam_mode == 'objective' ? 'selected' : '' }}>Objective</option>
            <option value="subjective" {{ $exam->exam_mode == 'subjective' ? 'selected' : '' }}>Subjective</option>
            <option value="mixed" {{ $exam->exam_mode == 'mixed' ? 'selected' : '' }}>Mixed</option>
        </select>
    </div>

    <div class="flex justify-end col-span-2 mt-4">
        <button type="submit" class="bg-[#0777be] text-white px-10 py-3 rounded-xl font-bold hover:bg-[#0666a3] transition shadow-lg">
            {{ $exam->id ? 'Update & Next: Settings' : 'Create & Next: Settings' }} →
        </button>
    </div>
</div>
