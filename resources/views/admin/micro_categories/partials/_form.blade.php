<div class="bg-white border border-gray-200 shadow-sm rounded-xl">

    {{-- Form Start --}}
    <form action="{{ $route }}" method="POST" enctype="multipart/form-data" class="block">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif

        <div class="p-8 space-y-8">

            {{-- ROW 1: Parent Sub-Category & (Optional Field/Spacer) --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Parent Sub-Category --}}
                <div>
                    <label for="sub_category_id" class="block mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase">
                        Parent Sub-Category <span class="text-red-500">*</span>
                    </label>
                    <select name="sub_category_id" id="sub_category_id"
                            class="w-full py-2.5 text-sm text-gray-900 border-gray-300 rounded-lg shadow-sm focus:ring-[#0777be] focus:border-[#0777be]">
                        <option value="">Select Parent</option>
                        @foreach($subCategories as $sub)
                            <option value="{{ $sub->id }}" {{ old('sub_category_id', $microCategory->sub_category_id) == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }} ({{ $sub->category->name ?? 'No Cat' }})
                            </option>
                        @endforeach
                    </select>
                    @error('sub_category_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Empty Spacer or Display Code (Read Only) if editing --}}
                <div>
                     {{-- Agar edit mode hai to Code dikha sakte ho, nahi to khali chhod do layout match karne ke liye --}}
                     @if($method === 'PUT')
                        <label class="block mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase">System Code</label>
                        <input type="text" value="{{ $microCategory->code }}" disabled class="w-full py-2.5 text-sm text-gray-500 bg-gray-50 border-gray-300 rounded-lg cursor-not-allowed">
                     @endif
                </div>
            </div>

            {{-- ROW 2: Name (Full Width) --}}
            <div>
                <label for="name" class="block mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $microCategory->name) }}"
                       placeholder="Enter micro category name"
                       class="w-full py-2.5 text-sm text-gray-900 border-gray-300 rounded-lg shadow-sm focus:ring-[#0777be] focus:border-[#0777be]">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- ROW 3: Icon/Image & Status --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 items-start">

                {{-- Image Upload Section --}}
                <div>
                    <label class="block mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase">Icon/Image</label>
                    <div class="flex items-center gap-4" x-data="imagePreview()">
                        {{-- Preview Box --}}
                        <div class="relative flex items-center justify-center w-24 h-24 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs text-gray-400">Preview</span>
                            </template>

                            {{-- Existing Image (Backend) --}}
                            @if($microCategory->image_path && !old('image'))
                                <img src="{{ asset('storage/' . $microCategory->image_path) }}" class="absolute inset-0 w-full h-full object-cover" x-show="!preview">
                            @endif
                        </div>

                        {{-- Choose File Button --}}
                        <div>
                            <label for="image" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer hover:bg-gray-50">
                                Choose file
                            </label>
                            <input type="file" name="image" id="image" class="hidden" @change="updatePreview($event)">
                            <p class="mt-2 text-xs text-gray-500" x-text="fileName || 'No file chosen'"></p>
                        </div>
                    </div>
                    @error('image') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Status Dropdown (Right Side aligned like screenshot) --}}
                <div>
                    <label for="is_active" class="block mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase">
                        Status
                    </label>
                    <select name="is_active" id="is_active"
                            class="w-full py-2.5 text-sm text-gray-900 border-gray-300 rounded-lg shadow-sm focus:ring-[#0777be] focus:border-[#0777be]">
                        <option value="1" {{ old('is_active', $microCategory->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $microCategory->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="flex items-center justify-end px-8 py-5 space-x-4 bg-gray-50/50 border-t border-gray-200 rounded-b-xl">
            <a href="{{ route('admin.micro-categories.index') }}" class="px-4 text-sm font-bold text-gray-500 uppercase hover:text-gray-700 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white uppercase bg-[#0777be] rounded-lg shadow hover:bg-[#0666a3] focus:ring-2 focus:ring-offset-2 focus:ring-[#0777be] transition-all">
                Save Micro-Category
            </button>
        </div>

    </form>
</div>

{{-- Alpine JS for Image Preview (Simple Logic) --}}
<script>
    function imagePreview() {
        return {
            preview: null,
            fileName: null,
            updatePreview(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.preview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
