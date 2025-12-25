<form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category <span class="text-red-500">*</span></label>
        <select name="sub_category_id" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
            <option value="">Choose Category</option>
            @foreach($subCategories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Plan Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" placeholder="Enter Name" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Duration (Months) <span class="text-red-500">*</span></label>
            <input type="number" name="duration" value="1" min="1" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Monthly Price <span class="text-red-500">*</span></label>
            <input type="number" name="price" value="0" step="0.01" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Short Description (Max. 200 Characters)</label>
        <textarea name="description" rows="3" maxlength="200" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]"></textarea>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sort Order <span class="text-red-500">*</span></label>
        <input type="number" name="sort_order" value="1" class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
    </div>

    <div class="flex items-center justify-between py-2">
        <div>
            <span class="block text-xs font-bold text-gray-700 uppercase">Status - Active</span>
            <span class="text-[10px] text-gray-500">Active (Shown Everywhere), In-active (Hidden Everywhere).</span>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#10b981]"></div>
        </label>
    </div>

    <div class="pt-4">
        <button type="submit" class="w-full py-2.5 bg-[#07476e] text-white text-sm font-bold rounded-lg shadow hover:bg-[#053552] transition-colors">
            Create
        </button>
    </div>
</form>