<div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
    <div class="flex items-center overflow-x-auto">
        @foreach($steps as $s)
            <div class="flex-1 min-w-[140px] relative">
                <a href="{{ $s['url'] ?: '#' }}"
                   class="flex flex-col items-center py-4 px-2 {{ $s['status'] == 'active' ? 'bg-blue-50/50' : '' }}">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mb-1
                        {{ $s['status'] == 'active' ? 'bg-[#0777be] text-white' : 'bg-gray-100 text-gray-400' }}">
                        {{ $s['step'] }}
                    </span>
                    <span class="text-[10px] uppercase font-extrabold tracking-wider {{ $s['status'] == 'active' ? 'text-[#0777be]' : 'text-gray-400' }}">
                        {{ $s['title'] }}
                    </span>
                </a>
            </div>
        @endforeach
    </div>
</div>
