<form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-bold uppercase mb-1">Category *</label>
        <select name="category_id" required class="w-full text-sm border-gray-300 rounded-lg">
            <option value="">Choose Category</option>
            @foreach ($subCategories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase mb-1">Name *</label>
        <input type="text" name="name" required class="w-full text-sm border-gray-300 rounded-lg">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold uppercase mb-1">Duration *</label>
            <input type="number" name="duration" value="1" required
                class="w-full text-sm border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase mb-1">Price *</label>
            <input type="number" name="price" step="0.01" required
                class="w-full text-sm border-gray-300 rounded-lg">
        </div>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full text-sm border-gray-300 rounded-lg"></textarea>
    </div>
    <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase">Active Status</label>
        <input type="checkbox" name="is_active" value="1" checked>
    </div>
    <button type="submit" class="w-full py-2 bg-[#07476e] text-white font-bold rounded-lg">Create</button>
</form>
