@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-[#f8fafc] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    AI <span class="text-indigo-600">Question</span> Engine
                </h1>
                <p class="text-slate-500 font-medium mt-1">High-precision PDF extraction powered by Gemini Vision Pro.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">System Active</span>
                </div>
            </div>
        </div>

        {{-- Bento Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left Column: Upload & Config (8/12) --}}
            <div class="lg:col-span-8 space-y-8">
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    {{-- Stepper Progress --}}
                    <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-5 flex items-center justify-between overflow-x-auto whitespace-nowrap">
                        <div class="flex items-center gap-3 shrink-0" id="step1-header">
                            <div id="step1-indicator" class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-indigo-100">1</div>
                            <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Config</span>
                        </div>
                        <div class="h-px w-8 bg-slate-200 mx-2 shrink-0"></div>
                        <div class="flex items-center gap-3 shrink-0" id="step2-header">
                            <div id="step2-indicator" class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold transition-all">2</div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest transition-all">Extract</span>
                        </div>
                        <div class="h-px w-8 bg-slate-200 mx-2 shrink-0"></div>
                        <div class="flex items-center gap-3 shrink-0" id="step3-header">
                            <div id="step3-indicator" class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold transition-all">3</div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest transition-all">Review</span>
                        </div>
                        <div class="h-px w-8 bg-slate-200 mx-2 shrink-0"></div>
                        <div class="flex items-center gap-3 shrink-0" id="step4-header">
                            <div id="step4-indicator" class="w-8 h-8 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold transition-all">4</div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest transition-all">Approve</span>
                        </div>
                    </div>

                    <div class="p-8 sm:p-10">
                        {{-- STEP 1: CONFIGURATION --}}
                        <div id="step-config" class="space-y-8">
                            <form id="aiImportForm" class="space-y-8">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Topic Selection --}}
                                    <div class="space-y-3">
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">
                                            Knowledge Category
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-layer-group text-indigo-400"></i>
                                            </div>
                                            <select name="topic_id" id="topicSelect" class="block w-full pl-11 pr-4 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                                <option value="">Select Topic...</option>
                                                @foreach($topics as $topic)
                                                    <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Page Range --}}
                                    <div class="space-y-3">
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">
                                            Page Range (Optional)
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input type="number" name="start_page" placeholder="Start" class="w-full px-4 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all">
                                            <span class="text-slate-300"><i class="fas fa-minus"></i></span>
                                            <input type="number" name="end_page" placeholder="End" class="w-full px-4 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all">
                                        </div>
                                    </div>
                                </div>

                                {{-- File Upload: Glassmorphism --}}
                                <div class="space-y-3">
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">
                                        Source Document
                                    </label>
                                    <div id="dropZone" class="group relative flex flex-col items-center justify-center py-14 px-6 border-2 border-dashed border-slate-200 rounded-[2rem] bg-slate-50/30 hover:bg-white hover:border-indigo-400 transition-all cursor-pointer overflow-hidden backdrop-blur-sm">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <input type="file" name="pdf_file" id="fileInput" accept=".pdf" class="hidden">
                                        
                                        <div id="emptyState" class="text-center space-y-4">
                                            <div class="w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center mx-auto group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 border border-slate-100">
                                                <i class="fas fa-file-pdf text-3xl text-indigo-500"></i>
                                            </div>
                                            <div>
                                                <p class="text-lg font-bold text-slate-900">Upload PDF Paper</p>
                                                <p class="text-xs text-slate-400 mt-1 font-medium">Drag & drop or click to browse (Max 50MB)</p>
                                            </div>
                                        </div>

                                        <div id="fileInfo" class="hidden text-center space-y-4">
                                            <div class="w-16 h-16 bg-emerald-500 rounded-2xl shadow-lg shadow-emerald-100 flex items-center justify-center mx-auto">
                                                <i class="fas fa-check text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <p id="fileName" class="text-lg font-bold text-emerald-600 truncate max-w-xs mx-auto">file.pdf</p>
                                                <button type="button" onclick="resetFile()" class="text-[10px] font-black text-rose-500 hover:text-rose-600 mt-2 uppercase tracking-widest">Change File</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" id="startBtn" disabled class="w-full py-5 rounded-2xl bg-slate-100 text-slate-400 font-black text-sm uppercase tracking-[0.2em] shadow-sm transition-all flex items-center justify-center gap-3">
                                    <span>Initialize AI Engine</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </button>
                            </form>
                        </div>

                        {{-- STEP 2: PROCESSING --}}
                        <div id="step-processing" class="hidden py-10 space-y-10">
                            <div class="text-center space-y-6">
                                <div class="relative inline-flex">
                                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center">
                                        <i class="fas fa-microchip text-4xl text-indigo-600 animate-pulse"></i>
                                    </div>
                                    <div class="absolute -top-1 -right-1 flex h-6 w-6">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-6 w-6 bg-indigo-600"></span>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-black text-slate-900">Extraction in Progress</h2>
                                    <p class="text-slate-400 font-medium max-w-sm mx-auto mt-2">Our vision models are identifying questions, options, and diagrams.</p>
                                </div>
                            </div>

                            <div class="space-y-6 max-w-md mx-auto">
                                <div class="flex justify-between items-end px-1">
                                    <div class="space-y-1">
                                        <span id="progress-status" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Booting engine...</span>
                                        <div class="text-xs text-slate-400 font-medium" id="est-time">Estimating time...</div>
                                    </div>
                                    <span id="percent-text" class="text-3xl font-black text-slate-900 tabular-nums">0%</span>
                                </div>
                                <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/50">
                                    <div id="progress-bar" class="h-full bg-gradient-to-r from-indigo-600 to-violet-600 rounded-full transition-all duration-700 ease-out shadow-[0_0_15px_rgba(79,70,229,0.3)]" style="width: 0%"></div>
                                </div>
                            </div>

                            <div id="actionArea" class="flex justify-center">
                                <button type="button" id="stopBtn" class="px-6 py-3 rounded-xl text-slate-400 font-bold hover:text-rose-500 hover:bg-rose-50 transition-all text-sm uppercase tracking-widest">
                                    <i class="fas fa-times-circle mr-2"></i> Cancel Process
                                </button>
                            </div>
                        </div>

                        {{-- ERROR BOX --}}
                        <div id="error-box" class="hidden mt-8 p-6 bg-rose-50/50 rounded-3xl border border-rose-100 backdrop-blur-sm">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-lg shadow-rose-100 shrink-0">
                                    <i class="fas fa-exclamation-triangle text-xl text-rose-500"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-black text-rose-900 uppercase text-xs tracking-widest">Engine Failure</h4>
                                    <p id="error-msg" class="text-rose-700 text-sm mt-1 font-medium">Internal system error occurred.</p>
                                    <button onclick="window.location.reload()" class="mt-4 text-[10px] font-black text-white bg-rose-500 px-5 py-2.5 rounded-xl hover:bg-rose-600 transition-all shadow-lg shadow-rose-200 uppercase tracking-widest">Retry Session</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Persistent Status Table --}}
                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Active & Recent Batches</h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-slate-100">Live Status Tracking</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Batch ID</th>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target</th>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                    <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Questions</th>
                                    <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($recentBatches as $batch)
                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-900">#{{ substr($batch->id, 0, 8) }}...</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $batch->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-xs font-bold text-slate-600">{{ $batch->topic->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        @php
                                            $statusClass = match($batch->status) {
                                                'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'processing' => 'bg-indigo-50 text-indigo-600 border-indigo-100 animate-pulse',
                                                'failed' => 'bg-rose-50 text-rose-600 border-rose-100',
                                                default => 'bg-slate-50 text-slate-600 border-slate-100',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border {{ $statusClass }}">
                                            {{ $batch->status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-xs font-black text-slate-900">{{ $batch->questions_count ?: 0 }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        @if($batch->status === 'completed')
                                            <a href="{{ route('admin.ai-import.preview', $batch->id) }}" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Review</a>
                                        @elseif($batch->status === 'processing' || $batch->status === 'pending')
                                            <button onclick="resumeBatch('{{ $batch->id }}')" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Track</button>
                                        @else
                                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-10 text-center text-slate-400 italic text-sm">No active import batches found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column: Specs & Tips (4/12) --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Quick Settings Card --}}
                <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-200">
                    <h3 class="text-sm font-black uppercase tracking-widest opacity-80">Extraction Mode</h3>
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center gap-4 bg-white/10 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-bolt text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold">Standard Vision</p>
                                <p class="text-[10px] opacity-60">Optimized for speed & costs</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/5 p-4 rounded-2xl border border-white/5 opacity-50 grayscale">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shield-alt text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold">Premium Logic (V2)</p>
                                <p class="text-[10px] opacity-60 italic">Coming soon for Pro users</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tips Card --}}
                <div class="bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm">
                    <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-6">Pro Tips</h3>
                    <ul class="space-y-6">
                        <li class="flex gap-4">
                            <div class="text-indigo-500 mt-1"><i class="fas fa-info-circle"></i></div>
                            <p class="text-xs text-slate-600 leading-relaxed"><span class="font-bold text-slate-900">Clear PDFs:</span> Higher resolution (300dpi) ensures 99.9% extraction accuracy.</p>
                        </li>
                        <li class="flex gap-4">
                            <div class="text-indigo-500 mt-1"><i class="fas fa-image"></i></div>
                            <p class="text-xs text-slate-600 leading-relaxed"><span class="font-bold text-slate-900">Diagrams:</span> AI automatically detects bounding boxes for all mathematical figures.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JSON Review Modal --}}
<div id="jsonModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white uppercase tracking-widest">Review Extracted Data</h3>
                <button onclick="closeJsonModal()" class="text-white/80 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-500 mb-4">Edit the raw JSON if you noticed extraction errors (e.g., incorrect page numbers or options). Format must remain valid JSON.</p>
                <textarea id="jsonEditor" class="w-full h-96 p-4 font-mono text-xs bg-slate-50 border-2 border-slate-100 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none" spellcheck="false"></textarea>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeJsonModal()" class="px-6 py-2 text-sm font-bold text-slate-500 uppercase">Cancel</button>
                <button id="saveJsonBtn" class="px-6 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg hover:bg-indigo-700 uppercase">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc = null;
    let isStopped = false;
    let currentBatchId = null;

    const topicSelect = document.getElementById('topicSelect');
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const progressBar = document.getElementById('progress-bar');
    const percentText = document.getElementById('percent-text');
    const statusText = document.getElementById('progress-status');
    const actionArea = document.getElementById('actionArea');

    dropZone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (file && file.type === "application/pdf") {
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('fileInfo').classList.remove('hidden');
            document.getElementById('fileName').innerText = 'Validating PDF...';

            try {
                const arrayBuffer = await file.arrayBuffer();
                pdfDoc = await pdfjsLib.getDocument(arrayBuffer).promise;
                document.getElementById('fileName').innerText = `${file.name} (${pdfDoc.numPages} Pages)`;
                updateButton();
            } catch(e) {
                alert("Only valid PDF files are allowed.");
                resetFile();
            }
        }
    });

    topicSelect.addEventListener('change', updateButton);

    function updateButton() {
        const isValid = topicSelect.value && pdfDoc;
        startBtn.disabled = !isValid;
        if (isValid) {
            startBtn.classList.remove('bg-slate-100', 'text-slate-400');
            startBtn.classList.add('bg-gradient-to-r', 'from-indigo-600', 'to-violet-600', 'text-white', 'shadow-indigo-200', 'hover:scale-[1.02]');
        } else {
            startBtn.classList.add('bg-slate-100', 'text-slate-400');
            startBtn.classList.remove('bg-gradient-to-r', 'from-indigo-600', 'to-violet-600', 'text-white', 'shadow-indigo-200', 'hover:scale-[1.02]');
        }
    }

    function resetFile() {
        fileInput.value = "";
        pdfDoc = null;
        document.getElementById('fileInfo').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
        updateButton();
    }

    function updateStepper(step) {
        // Reset all
        const indicators = ['step1', 'step2', 'step3', 'step4'];
        indicators.forEach((id, idx) => {
            const el = document.getElementById(id + '-indicator');
            const span = el.parentElement.querySelector('span');
            const currentStep = idx + 1;

            if (currentStep < step) {
                // Completed
                el.className = "w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-emerald-100 transition-all";
                el.innerHTML = '<i class="fas fa-check"></i>';
                span.className = "text-[10px] font-black text-emerald-600 uppercase tracking-widest transition-all";
            } else if (currentStep === step) {
                // Active
                el.className = "w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-lg shadow-indigo-100 transition-all scale-110";
                el.innerHTML = currentStep;
                span.className = "text-[10px] font-black text-indigo-600 uppercase tracking-widest transition-all";
            } else {
                // Future
                el.className = "w-8 h-8 rounded-full bg-white border-2 border-slate-200 text-slate-400 flex items-center justify-center text-xs font-bold transition-all";
                el.innerHTML = currentStep;
                span.className = "text-[10px] font-black text-slate-400 uppercase tracking-widest transition-all";
            }
        });
    }

    function switchToProcessing() {
        document.getElementById('step-config').classList.add('hidden');
        document.getElementById('step-processing').classList.remove('hidden');
        updateStepper(2);
    }

    document.getElementById('aiImportForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        switchToProcessing();

        try {
            const fd = new FormData();
            fd.append('topic_id', topicSelect.value);
            fd.append('pdf_file', fileInput.files[0]);
            
            const res = await fetch("{{ route('admin.ai-import.process') }}", {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            currentBatchId = data.batch_id;
            pollStatus(currentBatchId);

        } catch (err) {
            showError(err.message);
        }
    });

    /**
     * Resume tracking a batch that was already started.
     */
    async function resumeBatch(batchId) {
        currentBatchId = batchId;
        isStopped = false;
        switchToProcessing();
        pollStatus(batchId);
    }

    async function pollStatus(batchId) {
        if (isStopped) return;

        try {
            const res = await fetch(`{{ url('admin/ai-import/status') }}/${batchId}`);
            const data = await res.json();

            if (data.status === 'processing' || data.status === 'pending') {
                statusText.innerText = data.message;
                progressBar.style.width = `${data.progress}%`;
                percentText.innerText = `${data.progress}%`;
                updateStepper(2);
                setTimeout(() => pollStatus(batchId), 3000);
            } else if (data.status === 'completed') {
                progressBar.style.width = '100%';
                percentText.innerText = '100%';
                statusText.innerHTML = `<span class="text-emerald-600 font-bold"><i class="fas fa-check-circle mr-2"></i>AI Extraction Complete!</span> Found ${data.questions_count || 'several'} questions.`;
                updateStepper(3);
                showCompletionActions(batchId);
            } else if (data.status === 'failed') {
                throw new Error(data.message);
            }
        } catch (err) {
            showError(err.message);
        }
    }

    function showCompletionActions(batchId) {
        actionArea.className = "flex flex-col items-center space-y-6 animate-in fade-in slide-in-from-bottom-4 mt-8";
        actionArea.innerHTML = `
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full px-4">
                <button onclick="openJsonModal('${batchId}')" id="reviewBtn" class="flex-1 px-8 py-4 bg-white text-slate-700 font-bold rounded-2xl border-2 border-slate-200 hover:border-indigo-400 hover:bg-indigo-50 transition-all uppercase text-xs tracking-widest flex items-center justify-center">
                    <i class="fas fa-edit mr-2 text-indigo-500"></i> Review Data
                </button>
                <button onclick="processImages('${batchId}')" id="processBtn" class="flex-1 px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:scale-105 transition-all uppercase text-xs tracking-widest flex items-center justify-center">
                    <i class="fas fa-images mr-2"></i> Process Diagrams
                </button>
            </div>
            <button onclick="cancelSession('${batchId}')" class="text-xs font-bold text-rose-500 hover:text-rose-600 uppercase tracking-widest opacity-60 hover:opacity-100 transition-opacity">
                <i class="fas fa-trash-alt mr-1"></i> Discard Batch
            </button>
        `;
    }

    async function openJsonModal(batchId) {
        try {
            const res = await fetch("{{ url('admin/ai-import/preview') }}/" + batchId + "?json=1");
            const questions = await res.json();
            jsonEditor.value = JSON.stringify(questions, null, 4);
            currentBatchId = batchId;
            document.getElementById('jsonModal').classList.remove('hidden');
        } catch (e) {
            alert("Failed to load JSON data.");
        }
    }

    function closeJsonModal() {
        document.getElementById('jsonModal').classList.add('hidden');
    }

    document.getElementById('saveJsonBtn').addEventListener('click', async () => {
        const btn = document.getElementById('saveJsonBtn');
        try {
            const updatedData = JSON.parse(jsonEditor.value);
            btn.disabled = true;
            btn.innerText = "Saving...";

            const res = await fetch(`{{ url('admin/ai-import/update-json') }}/${currentBatchId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ questions: updatedData })
            });

            const data = await res.json();
            if (data.success) {
                alert("Data updated successfully!");
                closeJsonModal();
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            alert("Error saving JSON: " + e.message);
        } finally {
            btn.disabled = false;
            btn.innerText = "Save Changes";
        }
    });

    async function cancelSession(batchId) {
        if (!confirm("Are you sure you want to discard this batch? All extracted data will be deleted.")) return;
        try {
            await fetch("{{ route('admin.ai-import.cancel') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ batch_id: batchId })
            });
            window.location.reload();
        } catch (e) {
            alert("Failed to cancel session.");
        }
    }

    async function loadPdfFromUrl(batchId) {
        statusText.innerText = "Loading PDF from server...";
        const res = await fetch(`{{ url('admin/ai-import/download-pdf') }}/${batchId}`);
        if (!res.ok) throw new Error("Failed to download PDF for processing.");
        const arrayBuffer = await res.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument(arrayBuffer).promise;
    }

    async function processImages(batchId) {
        const processBtn = document.getElementById('processBtn');
        const reviewBtn = document.getElementById('reviewBtn');
        if (processBtn) {
            processBtn.disabled = true;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Initializing...';
        }
        if (reviewBtn) reviewBtn.disabled = true;
        
        try {
            // Ensure PDF is loaded (especially if resuming)
            if (!pdfDoc) {
                await loadPdfFromUrl(batchId);
            }

            const res = await fetch("{{ url('admin/ai-import/preview') }}/" + batchId + "?json=1");
            if (!res.ok) throw new Error("Failed to load extraction results.");
            const questions = await res.json();

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // 1. Calculate total images and group by page
            let totalImages = 0;
            const pageGroups = new Map();

            questions.forEach((q, i) => {
                if (q.image_box || (q.option_image_boxes && Object.keys(q.option_image_boxes).length > 0)) {
                    const pageNum = parseInt(q.page_number_extracted || q.source_page || 1);
                    if (!pageGroups.has(pageNum)) pageGroups.set(pageNum, []);
                    pageGroups.get(pageNum).push({ question: q, index: i });
                    
                    if (q.image_box) totalImages++;
                    if (q.option_image_boxes) totalImages += Object.keys(q.option_image_boxes).length;
                }
            });

            if (totalImages === 0) {
                window.location.href = "{{ url('admin/ai-import/preview') }}/" + batchId;
                return;
            }

            // 2. Start Cropping Phase
            let processedCount = 0;
            statusText.innerText = `Finalizing ${totalImages} diagrams...`;
            if (processBtn) processBtn.innerHTML = '<i class="fas fa-cut mr-2"></i> Cropping...';
            
            const sortedPages = Array.from(pageGroups.keys()).sort((a, b) => a - b);

            for (const pageNum of sortedPages) {
                if (isStopped) break;
                
                try {
                    const page = await pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 2.0 });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    
                    // Render page only once per group
                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                    const qs = pageGroups.get(pageNum);
                    for (const item of qs) {
                        const q = item.question;
                        const qIdx = item.index;

                        // Question Image
                        if (q.image_box) {
                            processedCount++;
                            updateCroppingUI(processedCount, totalImages);
                            const imgBase64 = crop(canvas, q.image_box);
                            await uploadImg(batchId, qIdx, imgBase64, 'question');
                        }

                        // Options Images
                        if (q.option_image_boxes) {
                            for (const key in q.option_image_boxes) {
                                processedCount++;
                                updateCroppingUI(processedCount, totalImages);
                                const imgBase64 = crop(canvas, q.option_image_boxes[key]);
                                await uploadImg(batchId, qIdx, imgBase64, 'option_' + key);
                            }
                        }
                    }
                    page.cleanup();
                } catch (pageErr) {
                    console.error(`Error rendering page ${pageNum}:`, pageErr);
                }
            }

            if (!isStopped) {
                statusText.innerText = "Redirecting to preview...";
                progressBar.style.width = '100%';
                if (pdfDoc) {
                    pdfDoc.destroy();
                    pdfDoc = null;
                }
                window.location.href = "{{ url('admin/ai-import/preview') }}/" + batchId;
            }
        } catch (err) {
            showError("Image extraction failed: " + err.message);
            if (processBtn) {
                processBtn.disabled = false;
                processBtn.innerHTML = '<i class="fas fa-images mr-2"></i> Process Diagrams';
            }
            if (reviewBtn) reviewBtn.disabled = false;
        }
    }

    function updateCroppingUI(current, total) {
        const percent = Math.round((current / total) * 100);
        statusText.innerHTML = `<span class="text-indigo-600 font-black tracking-widest uppercase text-xs">Phase 2: Diagram Extraction</span><br>Processing ${current} of ${total} (${percent}%)`;
        progressBar.style.width = `${current/total * 100}%`; 
    }

    function showError(msg) {
        document.getElementById('error-box').classList.remove('hidden');
        document.getElementById('error-msg').innerText = msg;
        document.getElementById('step-processing').classList.add('hidden');
    }

    function crop(sourceCanvas, box) {
        const [ymin, xmin, ymax, xmax] = box;
        const cX = (xmin / 1000) * sourceCanvas.width;
        const cY = (ymin / 1000) * sourceCanvas.height;
        const cW = ((xmax - xmin) / 1000) * sourceCanvas.width;
        const cH = ((ymax - ymin) / 1000) * sourceCanvas.height;

        const cropCanvas = document.createElement('canvas');
        cropCanvas.width = cW;
        cropCanvas.height = cH;
        const cropCtx = cropCanvas.getContext('2d');
        cropCtx.fillStyle = '#FFFFFF';
        cropCtx.fillRect(0, 0, cW, cH);
        cropCtx.drawImage(sourceCanvas, cX, cY, cW, cH, 0, 0, cW, cH);
        return cropCanvas.toDataURL('image/jpeg', 0.9);
    }

    async function uploadImg(batchId, qIdx, base64, type) {
        await fetch("{{ route('admin.ai-import.upload-cropped-image') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ batch_id: batchId, question_index: qIdx, image_base64: base64, image_type: type })
        });
    }

    stopBtn.addEventListener('click', () => { if(confirm("Abort process?")) { isStopped = true; location.reload(); }});
</script>

<style>
    .animate-in { animation: animate-in 0.5s ease-out; }
    @keyframes animate-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
