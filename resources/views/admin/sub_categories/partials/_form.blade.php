@php
    $isEdit = $subCategory->exists;
    $action = $isEdit ? route('admin.sub-categories.update', $subCategory->id) : route('admin.sub-categories.store');
@endphp

<div class="max-w-5xl mx-auto overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl" x-data="imagePreview()">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf @if($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-600 uppercase">Parent Category *</label>
                <select name="category_id" required class="w-full text-sm border-gray-300 rounded-lg">
                    <option value="">Select Parent</option>
                    @foreach($categories as $cat) <option value="{{ $cat->id }}" {{ old('category_id', $subCategory->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option> @endforeach
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-600 uppercase">Sub-Category Type *</label>
                <select name="sub_category_type_id" required class="w-full text-sm border-gray-300 rounded-lg">
                    @foreach($types as $t) <option value="{{ $t->id }}" {{ old('sub_category_type_id', $subCategory->sub_category_type_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option> @endforeach
                </select>
            </div>
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-bold text-gray-600 uppercase">Name *</label>
                <input type="text" name="name" value="{{ old('name', $subCategory->name) }}" required class="w-full border-gray-300 rounded-lg p-2.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-3">
                <label class="text-xs font-bold text-gray-600 uppercase">Icon/Image</label>
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-16 h-16 overflow-hidden border-2 border-dashed rounded-lg bg-gray-50">
                        <template x-if="imageUrl"><img :src="imageUrl" class="object-cover w-full h-full"></template>
                        <template x-if="!imageUrl"><span class="text-[10px] text-gray-400">Preview</span></template>
                    </div>
                    <input type="file" name="image" @change="fileChosen" class="text-xs file:bg-blue-50 file:border-0 file:rounded-lg file:px-4 file:py-2">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-600 uppercase">Status</label>
                <select name="is_active" class="w-full text-sm border-gray-300 rounded-lg">
                    <option value="1" {{ old('is_active', $subCategory->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $subCategory->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t">
            <a href="{{ route('admin.sub-categories.index') }}" class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">Cancel</a>
            <button type="submit" class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg font-bold text-xs uppercase shadow-md hover:bg-[#0666a3]">Save Sub-Category</button>
        </div>
    </form>
</div>
<script>
function imagePreview() {
    return {
        imageUrl: '{{ $subCategory->image_path ? asset('storage/'.$subCategory->image_path) : '' }}',
        fileChosen(e) { let f = e.target.files[0]; if(f){ let r = new FileReader(); r.onload = (ex) => this.imageUrl = ex.target.result; r.readAsDataURL(f); } }
    }
}
</script>
