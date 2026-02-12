{{-- resources/views/admin/exams/partials/_steps.blade.php --}}

<div x-data="{ showPublishModal: {{ session('trigger_publish_modal') ? 'true' : 'false' }}  }" class="mb-6">

    {{-- Steps Container --}}
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex items-center overflow-x-auto no-scrollbar">
            @foreach($steps as $s)
                @php
                    // Determine if this specific step is locked (specifically for Schedules)
                    $isLocked = isset($s['locked']) && $s['locked'];

                    // Determine interaction type
                    $isClickable = $s['url'] && $s['status'] !== 'active';
                @endphp

                <div class="flex-1 min-w-[160px] relative group">

                    {{-- INTERACTION WRAPPER --}}
                    @if($isClickable)
                        @if($isLocked)
                            {{-- LOCKED STEP: Button triggering Modal --}}
                            <button type="button" @click="showPublishModal = true" class="w-full text-left focus:outline-none">
                        @else
                            {{-- NORMAL STEP: Link --}}
                            <a href="{{ $s['url'] }}" class="block w-full">
                        @endif
                    @else
                        {{-- ACTIVE OR UNREACHABLE: Div --}}
                        <div class="w-full">
                    @endif

                        <div class="flex flex-col items-center py-5 px-3 transition-all duration-300 relative
                            {{ $s['status'] == 'active' ? '' : ($isClickable ? 'hover:bg-gray-50 cursor-pointer' : 'cursor-not-allowed opacity-60') }}
                            {{-- Visual cue for locked step --}}
                            {{ $isLocked ? 'bg-gray-50/50' : '' }}"

                            @if($s['status'] == 'active')
                                style="background: linear-gradient(to bottom, rgba(7, 119, 190, 0.05), transparent);"
                            @endif>

                            {{-- Step Number Circle --}}
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold mb-2 transition-all duration-300 z-10 relative
                                {{ $s['status'] == 'active' ? 'text-white shadow-lg scale-110' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }}"
                                @if($s['status'] == 'active') style="background-color: var(--brand-blue); shadow-color: var(--brand-blue);" @endif>

                                @if($isLocked)
                                    {{-- Lock Icon for Locked Steps --}}
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                @elseif(isset($s['completed']) && $s['completed'])
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @else
                                    {{ $s['step'] }}
                                @endif
                            </div>

                            {{-- Step Title --}}
                            <span class="text-[11px] uppercase font-black tracking-widest transition-colors duration-300
                                {{ $s['status'] == 'active' ? '' : 'text-gray-400 group-hover:text-gray-600' }}"
                                @if($s['status'] == 'active') style="color: var(--brand-blue);" @endif>
                                {{ $s['title'] }}
                            </span>

                            {{-- Active Bottom Bar --}}
                            @if($s['status'] == 'active')
                                <div class="absolute bottom-0 left-0 w-full h-1" style="background-color: var(--brand-blue);"></div>
                            @endif
                        </div>

                    {{-- CLOSING TAGS --}}
                    @if($isClickable)
                        @if($isLocked) </button> @else </a> @endif
                    @else
                        </div>
                    @endif

                    {{-- Connector Line --}}
                    @if(!$loop->last)
                        <div class="absolute top-9 left-[50%] w-full h-[2px] bg-gray-100 -z-0 hidden md:block"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- PUBLISH REQUIRED MODAL --}}
    <div x-show="showPublishModal"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showPublishModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-sm overflow-hidden text-left align-bottom transition-all transform bg-white shadow-2xl rounded-2xl sm:my-8 sm:align-middle">
                <div class="p-6 text-center">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Exam is in Draft Mode</h3>
                    <p class="text-sm text-gray-500">
                        Schedules can only be created for <strong>Published</strong> exams. Would you like to publish this exam now?
                    </p>

                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="showPublishModal = false" class="flex-1 px-4 py-2 text-sm font-bold text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Cancel
                        </button>

                        {{-- Publish Action Form --}}
                        @if(isset($routePrefix) && isset($routeParams) && request()->route('exam'))
                            <form action="{{ route($routePrefix . 'exams.quick-publish', array_merge($routeParams, ['exam' => request()->route('exam')])) }}"
                                  method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-green-600 rounded-lg shadow-lg hover:bg-green-700 shadow-green-200">
                                    Publish Now
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
