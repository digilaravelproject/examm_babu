@extends('layouts.admin')
@section('title', 'Email Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="max-w-4xl p-6 mx-auto bg-white border border-gray-200 shadow-sm rounded-xl">
        <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">SMTP Configuration</h2>

        <form action="{{ route('admin.settings.update-email') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- Host --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Mail Host</label>
                    <input type="text" name="host" value="{{ $settings->host }}" placeholder="smtp.mailtrap.io"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                {{-- Port --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Mail Port</label>
                    <input type="number" name="port" value="{{ $settings->port }}" placeholder="2525"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                {{-- Username --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                    <input type="text" name="username" value="{{ $settings->username }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                {{-- Password with Show/Hide Toggle --}}
                <div class="col-span-1" x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                    <div class="relative mt-1">
                        <input :type="show ? 'text' : 'password'" name="password" value="{{ $settings->password }}"
                            class="block w-full rounded-lg border-gray-300 pr-10 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                            {{-- Eye Icon (Show) --}}
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Eye Off Icon (Hide) --}}
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Encryption --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Encryption</label>
                    <select name="encryption"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                        <option value="tls" {{ $settings->encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ $settings->encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="null" {{ $settings->encryption == 'null' ? 'selected' : '' }}>None</option>
                    </select>
                </div>

                <div class="col-span-1"></div> {{-- Empty Spacer --}}

                {{-- From Address --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">From Address</label>
                    <input type="email" name="from_address" value="{{ $settings->from_address }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                {{-- From Name --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">From Name</label>
                    <input type="text" name="from_name" value="{{ $settings->from_name }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>
            </div>

            <div class="flex justify-end pt-4 mt-8 border-t">
                <button type="submit"
                    class="bg-[#0777be] text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-[#0777be]/90 transition">
                    Save Email Configuration
                </button>
            </div>
        </form>
    </div>
@endsection
