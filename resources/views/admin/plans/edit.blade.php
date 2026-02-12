@extends('layouts.admin')

@section('title', 'Edit Plan')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Plan: {{ $plan->name }}</h1>
            <a href="{{ route('admin.plans.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Back to List
            </a>
        </div>

        {{-- Edit Form --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-5"
                x-data="{
                    hasDiscount: {{ $plan->has_discount ? 'true' : 'false' }},
                    restrictedAccess: {{ $plan->feature_restrictions ? 'true' : 'false' }}
                }">

                @csrf
                @method('PUT') {{-- Update ke liye zaroori hai --}}

                {{-- 1. Category --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category <span
                            class="text-red-500">*</span></label>
                    <select name="category_id" required
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
                        <option value="">Choose Category</option>
                        @foreach ($subCategories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $plan->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Plan Name --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Plan Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" placeholder="Enter Name"
                        required
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
                </div>

                {{-- 3. Duration & Price --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Duration (Months) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="duration" value="{{ old('duration', $plan->duration) }}" min="1"
                            required
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Monthly Price <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $plan->price) }}" step="0.01"
                            min="0" required
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
                    </div>
                </div>

                {{-- 4. Discount Section --}}
                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-gray-700 uppercase">Discount</span>
                            <span class="text-[10px] text-gray-500">Provide direct discount to the plan.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_discount" value="1" x-model="hasDiscount"
                                {{ $plan->has_discount ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#10b981]">
                            </div>
                        </label>
                    </div>

                    <div x-show="hasDiscount" style="display: none;" class="mt-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Discount Percentage (%) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="discount_percentage"
                            value="{{ old('discount_percentage', $plan->discount_percentage) }}" min="0"
                            max="100"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]"
                            placeholder="e.g. 10">
                    </div>
                </div>

                {{-- 5. Feature Access Section --}}
                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between">
                        <div class="pr-4">
                            <span class="block text-xs font-bold text-gray-700 uppercase">Feature Access</span>
                            <span class="text-[10px] text-gray-500 leading-tight block mt-1">
                                <span x-show="!restrictedAccess" class="text-green-600 font-semibold">Unlimited</span>
                                <span x-show="restrictedAccess" class="text-orange-600 font-semibold">Restricted</span>
                                (Access based on selection).
                            </span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="feature_restrictions" value="1" x-model="restrictedAccess"
                                {{ $plan->feature_restrictions ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#10b981]">
                            </div>
                        </label>
                    </div>

                    <div x-show="restrictedAccess" style="display: none;"
                        class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200 max-h-48 overflow-y-auto">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Select Features <span
                                class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            @foreach ($features as $feature)
                                <label class="flex items-center hover:bg-gray-100 p-1 rounded cursor-pointer">
                                    <input type="checkbox" name="features[]" value="{{ $feature->id }}"
                                        {{ in_array($feature->id, $selectedFeatures) ? 'checked' : '' }}
                                        class="rounded text-[#10b981] focus:ring-[#10b981] border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">{{ $feature->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 6. Short Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Short Description (Max. 200
                        Characters)</label>
                    <textarea name="description" rows="3" maxlength="200"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">{{ old('description', $plan->description) }}</textarea>
                </div>

                {{-- 7. Sort Order --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sort Order <span
                            class="text-red-500">*</span></label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}"
                        min="0" required
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-[#10b981] focus:border-[#10b981]">
                </div>

                {{-- 8. Popular Toggle --}}
                <div class="flex items-center justify-between py-2 border-t border-gray-100">
                    <div>
                        <span class="block text-xs font-bold text-gray-700 uppercase">Popular</span>
                        <span class="text-[10px] text-gray-500">Yes (Shown as Most Popular)</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_popular" value="1" {{ $plan->is_popular ? 'checked' : '' }}
                            class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#10b981]">
                        </div>
                    </label>
                </div>

                {{-- 9. Status Toggle --}}
                <div class="flex items-center justify-between py-2 border-t border-gray-100">
                    <div>
                        <span class="block text-xs font-bold text-gray-700 uppercase">Status</span>
                        <span class="text-[10px] text-gray-500">Toggle plan visibility.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}
                            class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#07476e]">
                        </div>
                    </label>
                </div>

                {{-- Update Button --}}
                <div class="pt-6">
                    <button type="submit"
                        class="w-full py-2.5 bg-[#07476e] text-white text-sm font-bold rounded-lg shadow hover:bg-[#053552] transition-colors">
                        Update Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
