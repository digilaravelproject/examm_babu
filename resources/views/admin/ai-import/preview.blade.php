@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.ai-import.index') }}" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors flex items-center">
                                <i class="fas fa-magic mr-2"></i> AI Import
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-slate-300 text-[10px] mx-1"></i>
                                <span class="text-sm font-bold text-slate-900 ml-1">Verification Gallery</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    Verify Extracted Content
                </h1>
                <p class="text-slate-500 mt-1">Found <span class="text-indigo-600 font-bold tabular-nums">{{ count($questions) }}</span> questions. Please review them carefully before permanent storage.</p>
            </div>

            @if(count($questions) > 0)
            <div class="flex items-center gap-3">
                <button id="approveBtn" class="inline-flex items-center px-8 py-4 bg-indigo-600 border border-transparent rounded-2xl font-black text-white hover:bg-indigo-700 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-indigo-200 uppercase tracking-widest text-sm">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Save to Database
                </button>
            </div>
            @endif
        </div>

        <div id="status-message" class="hidden p-6 mb-8 rounded-2xl border-2 transition-all duration-500 animate-in slide-in-from-top-4"></div>

        @if(count($questions) == 0)
            <div class="bg-white rounded-3xl p-16 text-center border-2 border-dashed border-slate-200 shadow-sm space-y-6">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300">
                    <i class="fas fa-ghost text-5xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Virtual Desert</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mt-2">AI couldn't find any questions matching our quality standards. This usually happens if the PDF scan is poor or text is unrecognizable.</p>
                </div>
                <a href="{{ route('admin.ai-import.index') }}" class="inline-block px-6 py-3 bg-indigo-50 text-indigo-700 rounded-xl font-bold hover:bg-indigo-100 transition">Try a Different Scan</a>
            </div>
        @else
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                @foreach($questions as $index => $q)
                    <div class="group bg-white rounded-3xl shadow-sm hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 border border-slate-100 overflow-hidden relative flex flex-col">
                        {{-- Top Strip --}}
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-violet-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        {{-- ID & Meta --}}
                        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center space-x-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs shadow-lg shadow-indigo-100">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest bg-white px-2 py-1 rounded border border-slate-200">
                                    {{ $q['type'] ?? 'MSA' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-full border border-indigo-100">
                                    <i class="fas fa-file-alt mr-1"></i> Page {{ $q['source_page'] ?? '?' }}
                                </span>
                            </div>
                        </div>

                        {{-- Question Content --}}
                        <div class="p-8 flex-grow space-y-6">
                            <div class="prose prose-slate max-w-none">
                                <h3 class="text-lg font-bold text-slate-800 leading-relaxed">
                                    {!! $q['question'] ?? '<span class="text-rose-400 italic">No question extracted</span>' !!}
                                </h3>
                            </div>

                            @if(isset($q['options']) && is_array($q['options']) && count($q['options']) > 0)
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($q['options'] as $optIdx => $opt)
                                        @php
                                            $isCorrect = false;
                                            if (($q['type'] ?? 'MSA') === 'MMA') {
                                                $isCorrect = in_array($optIdx, $q['correct_option_indices'] ?? []);
                                            } else {
                                                $isCorrect = (isset($q['correct_option_index']) && $q['correct_option_index'] == $optIdx);
                                            }
                                            $letter = chr(65 + $optIdx);
                                        @endphp
                                        <div class="relative group/opt p-4 rounded-2xl border-2 transition-all {{ $isCorrect ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-50' }}">
                                            <div class="flex items-start gap-4">
                                                <span class="flex-shrink-0 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-white text-slate-400 border border-slate-200' }}">
                                                    {{ $letter }}
                                                </span>
                                                <div class="text-sm font-medium {{ $isCorrect ? 'text-emerald-900' : 'text-slate-700' }}">
                                                    {!! $opt !!}
                                                </div>
                                                @if($isCorrect)
                                                    <i class="fas fa-check-circle text-emerald-500 ml-auto self-center opacity-80"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(isset($q['correct_answer_text']) && $q['correct_answer_text'])
                                <div class="p-4 bg-blue-50 border-2 border-blue-100 rounded-2xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                                            <i class="fas fa-key text-xs"></i>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] uppercase font-black text-blue-400 tracking-tighter">Verified Answer</label>
                                            <span class="text-sm font-bold text-blue-900">{{ $q['correct_answer_text'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Solution/Hint Footer --}}
                        @if(!empty($q['solution']) || !empty($q['hint']))
                        <div class="px-8 py-6 bg-slate-50/30 border-t border-slate-100 mt-auto group-hover:bg-indigo-50/30 transition-colors">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-info-circle text-indigo-400 mt-0.5"></i>
                                <div class="text-xs text-slate-500 leading-relaxed italic">
                                    <strong class="text-slate-700 not-italic uppercase tracking-widest text-[10px]">AI Insight:</strong> 
                                    {!! $q['solution'] ?: $q['hint'] !!}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const approveBtn = document.getElementById('approveBtn');
        const statusMsg = document.getElementById('status-message');

        if(approveBtn) {
            approveBtn.addEventListener('click', async function() {
                if(!confirm("Ready to integrate these into your database? This action cannot be undone.")) return;

                approveBtn.disabled = true;
                const originalContent = approveBtn.innerHTML;
                approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Persisting...';
                approveBtn.classList.replace('bg-indigo-600', 'bg-slate-400');

                try {
                    const res = await fetch("{{ route('admin.ai-import.approve', $batchId) }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });

                    const data = await res.json();

                    if(data.success) {
                        statusMsg.className = "p-6 mb-8 rounded-2xl border-2 bg-emerald-50 border-emerald-100 text-emerald-800 font-bold flex items-center";
                        statusMsg.innerHTML = '<i class="fas fa-check-circle text-2xl mr-4 text-emerald-500"></i>' + data.message;
                        statusMsg.classList.remove('hidden');
                        
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    } else {
                        throw new Error(data.message);
                    }
                } catch (err) {
                    approveBtn.disabled = false;
                    approveBtn.innerHTML = originalContent;
                    approveBtn.classList.replace('bg-slate-400', 'bg-indigo-600');

                    statusMsg.className = "p-6 mb-8 rounded-2xl border-2 bg-rose-50 border-rose-100 text-rose-800 font-bold flex items-center";
                    statusMsg.innerHTML = '<i class="fas fa-exclamation-triangle text-2xl mr-4 text-rose-500"></i>' + err.message;
                    statusMsg.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    });
</script>
@endsection
