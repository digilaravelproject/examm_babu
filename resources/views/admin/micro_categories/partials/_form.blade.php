<div class="overflow-hidden bg-white border border-gray-200 shadow-xl rounded-xl">

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur">
            <h3 class="flex items-center gap-2 text-base font-extrabold tracking-wide text-gray-800 uppercase">
                <span class="text-xl">📁</span>
                {{ $method === 'PUT' ? 'Update Details' : 'Micro Category Details' }}
            </h3>
        </div>

        @if ($errors->any())
            <div class="px-6 pt-4">
                <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
                    <ul class="pl-5 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="p-6 space-y-6">
            {{-- ROW 1: Parent Sub-Category & Name --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- Parent Sub-Category --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
                        Parent Sub-Category <span class="text-red-500">*</span>
                    </label>
                    <select name="sub_category_id" class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium">
                        <option value="">Select Parent</option>
                        @foreach ($subCategories as $sub)
                            <option value="{{ $sub->id }}"
                                {{ old('sub_category_id', $microCategory->sub_category_id) == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }} ({{ $sub->category->name ?? 'No Cat' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $microCategory->name) }}"
                        class="w-full border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#0777be] focus:ring-1 focus:ring-[#0777be] transition shadow-sm font-medium">
                </div>
            </div>

            {{-- ROW 2: Code (Read Only) --}}
            @if ($method === 'PUT')
            <div class="space-y-1.5">
                <label class="block text-xs font-bold tracking-wide text-gray-400 uppercase">System Code (Read Only)</label>
                <div class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-sm font-mono text-gray-500">
                    {{ $microCategory->code }}
                </div>
            </div>
            @endif

            {{-- ROW 3: Image & Status --}}
            <div class="grid items-start grid-cols-1 gap-8 md:grid-cols-2">

                {{-- Image Upload --}}
                <div class="space-y-3" x-data="imagePreview()">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Micro-Category Icon</label>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-20 h-20 overflow-hidden border-2 border-gray-300 border-dashed rounded-xl bg-gray-50">
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="object-cover w-full h-full">
                            </template>
                            <template x-if="!imageUrl">
                                <span class="text-[10px] text-gray-400">No Image</span>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="image" @change="fileChosen" accept="image/*"
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0777be] hover:file:bg-blue-100 transition">
                        </div>
                    </div>
                </div>

                {{-- Status Toggle --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold tracking-wide text-gray-600 uppercase">Status</label>
                    <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer">
                        <span class="text-sm font-bold text-gray-700">Active?</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $microCategory->is_active ?? 1) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#94c940] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
            <a href="{{ url()->previous() }}" class="px-4 py-2 text-xs font-bold tracking-wide text-gray-500 uppercase hover:text-gray-700">Cancel</a>
            <button type="submit"
                class="px-8 py-2.5 bg-[#0777be] text-white rounded-lg shadow hover:bg-[#0666a3] font-bold text-xs uppercase tracking-wide transition-all">
                {{ $method === 'PUT' ? 'Update' : 'Save' }} Micro Category
            </button>
        </div>
    </form>
</div>

<script>
    function imagePreview() {
        return {
            imageUrl: '{{ $microCategory->image_path ? asset($microCategory->image_path) : '' }}',
            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imageUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
