{{-- 1. Admin Layout ko Extend kiya --}}
@extends('layouts.admin')

{{-- 2. Header Title Set kiya --}}
@section('header', 'System Activity Logs')

{{-- 3. Main Content Section --}}
@section('content')
    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Date/Time</th>
                            <th class="border p-2 text-left">User Name</th>
                            <th class="border p-2 text-left">Role</th>
                            <th class="border p-2 text-left">Action (Description)</th>
                            <th class="border p-2 text-left">Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-sm whitespace-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                            <td class="border p-2 font-bold">
                                {{ $log->causer ? $log->causer->name : 'System' }}
                            </td>
                            <td class="border p-2 text-sm text-blue-600">
                                {{-- User ka Role fetch karna --}}
                                {{ $log->causer && $log->causer->roles->isNotEmpty() ? $log->causer->roles->first()->name : 'N/A' }}
                            </td>
                            <td class="border p-2">{{ $log->description }}</td>
                            <td class="border p-2 text-xs text-gray-500 font-mono">
                                @if($log->properties && isset($log->properties['attributes']))
                                    {{-- JSON ko thoda pretty print kar sakte hain ya aise hi dikha sakte hain --}}
                                    {{ json_encode($log->properties['attributes']) }}
                                @elseif($log->properties && isset($log->properties['ip']))
                                    IP: {{ $log->properties['ip'] }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="border p-4 text-center text-gray-500">No logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
