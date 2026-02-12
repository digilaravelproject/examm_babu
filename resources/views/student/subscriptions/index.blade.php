@extends('layouts.student')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-slate-900">My Subscriptions</h1>
            <p class="mt-2 text-sm text-slate-500">Manage your active plans and billing history.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('pricing') }}"
               class="inline-flex items-center justify-center rounded-xl border border-transparent bg-[var(--brand-blue)] px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto transition-all">
                Browse Plans
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex flex-col mt-8">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">

                @if($subscriptions->count() > 0)
                    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-black ring-opacity-5 md:rounded-2xl">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 sm:pl-6">Plan Details</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Duration</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Dates</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($subscriptions as $sub)
                                    <tr class="transition-colors group hover:bg-slate-50">
                                        {{-- Plan Name --}}
                                        <td class="py-4 pl-4 pr-3 whitespace-nowrap sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-blue-50 text-[var(--brand-blue)] flex items-center justify-center font-bold text-lg">
                                                    {{ substr($sub->plan->name ?? 'P', 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-bold text-slate-900">{{ $sub->plan->name ?? 'Unknown Plan' }}</div>
                                                    <div class="text-xs text-slate-500">ID: #{{ $sub->id }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if($sub->status === 'active')
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 mr-1.5"></span> Active
                                                </span>
                                            @elseif($sub->status === 'cancelled')
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                                    Cancelled
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800">
                                                    {{ ucfirst($sub->status) }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Duration --}}
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-slate-500">
                                            {{ $sub->duration }} Months
                                        </td>

                                        {{-- Dates --}}
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-slate-500">
                                            <div class="flex flex-col">
                                                <span><span class="text-xs font-semibold text-slate-400">Start:</span> {{ $sub->starts_at->format('M d, Y') }}</span>
                                                <span><span class="text-xs font-semibold text-slate-400">End:</span> {{ $sub->ends_at->format('M d, Y') }}</span>
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">
                                            @if($sub->status === 'active')
                                                <form action="{{ route('student.subscriptions.cancel', $sub->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-xs border border-red-200 bg-red-50 px-3 py-1.5 rounded-lg hover:bg-red-100 transition-colors">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs italic text-slate-400">No actions</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $subscriptions->links() }}
                    </div>

                @else
                    {{-- Empty State --}}
                    <div class="py-20 text-center bg-white border border-dashed rounded-2xl border-slate-300">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full text-slate-300 bg-slate-50">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No active subscriptions</h3>
                        <p class="mt-1 text-sm text-slate-500">You haven't purchased any exam plans yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('pricing') }}" class="inline-flex items-center rounded-lg border border-transparent bg-[var(--brand-blue)] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                                Browse Plans
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
