<div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="flex items-center overflow-x-auto no-scrollbar">
        @foreach($steps as $s)
            <div class="flex-1 min-w-[160px] relative group">
                <a href="{{ $s['url'] ?: '#' }}"
                   class="flex flex-col items-center py-5 px-3 transition-all duration-300 relative
                   {{ $s['status'] == 'active' ? '' : 'hover:bg-gray-50' }}"
                   @if($s['status'] == 'active')
                        style="background: linear-gradient(to bottom, rgba(7, 119, 190, 0.05), transparent);"
                   @endif>

                    {{-- Step Number Circle --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold mb-2 transition-all duration-300 z-10
                        {{ $s['status'] == 'active' ? 'text-white shadow-lg scale-110' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }}"
                        @if($s['status'] == 'active') style="background-color: var(--brand-blue); shadow-color: var(--brand-blue);" @endif>
                        @if($s['status'] == 'completed') {{-- In case you add 'completed' logic later --}}
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
                        <div class="absolute bottom-0 left-0 w-full h-1"
                             style="background-color: var(--brand-blue);">
                        </div>
                    @endif
                </a>

                {{-- Connector Line (Desktop Only) --}}
                @if(!$loop->last)
                    <div class="absolute top-9 left-[70%] w-full h-[2px] bg-gray-100 -z-0 hidden md:block"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
    /* Hide scrollbar for cleaner look on mobile */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
