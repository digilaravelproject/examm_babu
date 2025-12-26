@extends('layouts.admin')
@section('title', 'Edit Plan')
@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-sm">
        <h2 class="text-xl font-bold mb-6">Edit Plan: {{ $plan->name }}</h2>
        <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <select name="category_id" required class="w-full text-sm border-gray-300 rounded-lg">
                @foreach ($subCategories as $cat)
                    <option value="{{ $cat->id }}" {{ $plan->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                @endforeach
            </select>

            <input type="text" name="name" value="{{ $plan->name }}" required
                class="w-full text-sm border-gray-300 rounded-lg">

            <div class="grid grid-cols-2 gap-4">
                <input type="number" name="duration" value="{{ $plan->duration }}" required
                    class="w-full text-sm border-gray-300 rounded-lg">
                <input type="number" name="price" value="{{ $plan->price }}" required
                    class="w-full text-sm border-gray-300 rounded-lg">
            </div>

            <textarea name="description" class="w-full text-sm border-gray-300 rounded-lg">{{ $plan->description }}</textarea>

            <div class="flex items-center justify-between">
                <label>Active Status</label>
                <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
            </div>

            <button type="submit" class="w-full py-2 bg-[#07476e] text-white font-bold rounded-lg">Update</button>
        </form>
    </div>
@endsection
