@extends('layouts.admin')
@section('title', 'Edit Subscription')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold mb-6">Edit Subscription #{{ $subscription->id }}</h2>
    <div class="mb-4 text-sm text-gray-600 bg-gray-50 p-3 rounded">
        User: <strong>{{ $subscription->user->name }}</strong><br>
        Plan: <strong>{{ $subscription->plan->name }}</strong>
    </div>

    <form action="{{ route('admin.subscriptions.update', $subscription->id) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-xs font-bold uppercase mb-1">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-lg text-sm">
                <option value="active" {{ $subscription->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="expired" {{ $subscription->status == 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="cancelled" {{ $subscription->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase mb-1">End Date (Expiry)</label>
            <input type="datetime-local" name="ends_at"
                value="{{ \Carbon\Carbon::parse($subscription->ends_at)->format('Y-m-d\TH:i') }}"
                class="w-full border-gray-300 rounded-lg text-sm">
        </div>

        <button type="submit" class="w-full py-2 bg-[#07476e] text-white font-bold rounded-lg">Update Subscription</button>
    </form>
</div>
@endsection
