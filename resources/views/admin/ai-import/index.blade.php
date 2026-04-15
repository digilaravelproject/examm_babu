@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        {{-- Glassmorphism Header --}}
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight sm:text-5xl mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600">
                    AI Smart Import
                </span>
            </h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Digitize your question papers in seconds using advanced Vision AI.
            </p>
        </div>

        {{-- Stepper UI --}}
        <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-100 overflow-hidden border border-slate-100">
            {{-- Progress Header --}}
            <nav aria-label="Progress" class="bg-slate-50/50 border-b border-slate-100">
                <ol role="list" class="flex items-center justify-center py-6">
                  <li class="relative pr-8 sm:pr-20 group" id="step1-header">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                      <div class="h-0.5 w-full bg-slate-200 group-data-[active=true]:bg-indigo-600"></div>
                    </div>
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white ring-8 ring-white shadow-lg shadow-indigo-200">
                      <span class="text-sm font-bold">1</span>
                    </div>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-bold text-indigo-600 uppercase tracking-wider">Configure</span>
                  </li>
                  <li class="relative" id="step2-header">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 border-2 border-slate-200 ring-8 ring-white">
                      <span class="text-sm font-bold">2</span>
                    </div>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-bold text-slate-400 uppercase tracking-wider">Processing</span>
                  </li>
                </ol>
            </nav>

            <div class="p-8 sm:p-12">
                {{-- STEP 1: CONFIGURATION --}}
                <div id="step-config" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <form id="aiImportForm" class="space-y-8">
                        @csrf
                        {{-- Topic Selection --}}
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-slate-700 uppercase tracking-widest">
                                Target Knowledge Area
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-tags text-indigo-400"></i>
                                </div>
                                <select name="topic_id" id="topicSelect" class="block w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-slate-900 font-medium focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none">
                                    <option value="">Choose a Topic...</option>
                                    @foreach($topics as $topic)
                                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        {{-- File Upload --}}
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-slate-700 uppercase tracking-widest">
                                Document (PDF)
                            </label>
                            <div id="dropZone" class="group relative flex flex-col items-center justify-center py-16 px-6 border-3 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 hover:bg-slate-50 hover:border-indigo-400 transition-all cursor-pointer overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <input type="file" name="pdf_file" id="fileInput" accept=".pdf" class="hidden">
                                
                                <div id="emptyState" class="text-center space-y-4 z-10">
                                    <div class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center mx-auto group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-file-pdf text-4xl text-indigo-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-slate-900">Drop PDF paper here</p>
                                        <p class="text-sm text-slate-500 mt-1">or click to browse local files (Max 50MB)</p>
                                    </div>
                                </div>

                                <div id="fileInfo" class="hidden text-center space-y-4 z-10">
                                    <div class="w-20 h-20 bg-emerald-500 rounded-2xl shadow-xl shadow-emerald-200 flex items-center justify-center mx-auto">
                                        <i class="fas fa-check text-4xl text-white"></i>
                                    </div>
                                    <div>
                                        <p id="fileName" class="text-xl font-bold text-emerald-600 truncate max-w-xs mx-auto">file.pdf</p>
                                        <button type="button" onclick="resetFile()" class="text-sm font-bold text-rose-500 hover:text-rose-600 mt-2 uppercase tracking-tight">Remove File</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="pt-6">
                            <button type="submit" id="startBtn" disabled class="w-full py-5 px-8 rounded-2xl bg-slate-200 text-slate-400 font-black text-lg uppercase tracking-widest shadow-xl transition-all disabled:cursor-not-allowed">
                                Analyze Document
                            </button>
                        </div>
                    </form>
                </div>

                {{-- STEP 2: PROCESSING (Hidden) --}}
                <div id="step-processing" class="hidden space-y-12 animate-in fade-in zoom-in-95 duration-500">
                    <div class="text-center space-y-4">
                        <div class="relative inline-block">
                            <i class="fas fa-brain text-6xl text-indigo-500 animate-pulse"></i>
                            <div class="absolute -top-1 -right-1 flex h-4 w-4">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-4 w-4 bg-indigo-500"></span>
                            </div>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900">AI is Digitizing Content</h2>
                        <p class="text-slate-500">Please keep this window open while our AI extracts questions and diagrams.</p>
                    </div>

                    <div class="space-y-4 max-w-md mx-auto">
                        <div class="flex justify-between items-end">
                            <span id="progress-status" class="text-sm font-bold text-indigo-600">Analyzing pages...</span>
                            <span id="percent-text" class="text-2xl font-black text-slate-900 tabular-nums">0%</span>
                        </div>
                        <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div id="progress-bar" class="h-full bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 transition-all duration-700 ease-out" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-tighter pt-2">
                            <span>Batch Processing System v2.0</span>
                            <span>Est. Time remaining: <span id="est-time">Calculating...</span></span>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="button" id="stopBtn" class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-500 font-bold hover:bg-slate-50 transition-colors">
                            <i class="fas fa-stop-circle mr-2 text-rose-500"></i> Abort Extraction
                        </button>
                    </div>
                </div>

                <div id="error-box" class="hidden mt-8 p-6 bg-rose-50 rounded-2xl border-2 border-rose-100 border-l-rose-500 border-l-8">
                    <div class="flex items-start">
                        <i class="fas fa-bomb text-2xl text-rose-500 mt-1"></i>
                        <div class="ml-4">
                            <h4 class="font-black text-rose-900 uppercase text-sm tracking-widest">Process Failed</h4>
                            <p id="error-msg" class="text-rose-700 text-sm mt-1">Internal system error occurred.</p>
                            <button onclick="window.location.reload()" class="mt-4 text-xs font-bold text-white bg-rose-500 px-4 py-2 rounded-lg hover:bg-rose-600 uppercase">Retry Session</button>
                        </div>
                    </div>
                </div>
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
            startBtn.classList.replace('bg-slate-200', 'bg-gradient-to-r');
            startBtn.classList.add('from-indigo-600', 'to-violet-600', 'bg-indigo-600', 'text-white', 'hover:scale-[1.02]');
        } else {
            startBtn.classList.remove('from-indigo-600', 'to-violet-600', 'bg-indigo-600', 'text-white', 'hover:scale-[1.02]');
            startBtn.classList.add('bg-slate-200', 'text-slate-400');
        }
    }

    function resetFile() {
        fileInput.value = "";
        pdfDoc = null;
        document.getElementById('fileInfo').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
        updateButton();
    }

    document.getElementById('aiImportForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        document.getElementById('step-config').classList.add('hidden');
        document.getElementById('step-processing').classList.remove('hidden');
        document.getElementById('step2-header').querySelector('div').classList.replace('bg-white', 'bg-indigo-600');
        document.getElementById('step2-header').querySelector('div').classList.replace('text-slate-400', 'text-white');
        document.getElementById('step2-header').querySelector('div').classList.add('shadow-lg', 'shadow-indigo-200');
        document.getElementById('step2-header').querySelector('span').classList.replace('text-slate-400', 'text-indigo-600');

        try {
            const numPages = pdfDoc.numPages;
            let allQuestions = [];
            const chunkSize = 1;

            for (let i = 1; i <= numPages; i += chunkSize) {
                if (isStopped) break;

                statusText.innerText = `AI Reading Page ${i} of ${numPages}...`;
                let progress = Math.round((i / numPages) * 70); 
                progressBar.style.width = `${progress}%`;
                percentText.innerText = `${progress}%`;

                const fd = new FormData();
                fd.append('topic_id', topicSelect.value);
                if (!currentBatchId) fd.append('pdf_file', fileInput.files[0]);
                else fd.append('batch_id', currentBatchId);
                fd.append('start_page', i);
                fd.append('end_page', i);

                const res = await fetch("{{ route('admin.ai-import.process') }}", {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                const data = await res.json();
                if (!data.success) throw new Error(data.message);

                currentBatchId = data.batch_id;
                allQuestions = data.questions;

                // Rate limiting pause
                if (i < numPages) await new Promise(r => setTimeout(r, 2000));
            }

            if (isStopped) return;

            // STEP 2: Vision Extraction (Images)
            statusText.innerText = `Vision AI: Extracting Diagrams...`;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            for (let i = 0; i < allQuestions.length; i++) {
                if (isStopped) break;
                const q = allQuestions[i];
                if (!q.image_box && !q.option_image_boxes) continue;

                const page = await pdfDoc.getPage(q.page_number_extracted || 1);
                const viewport = page.getViewport({ scale: 2.0 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                // Process Question Image
                if (q.image_box) {
                    const imgBase64 = crop(canvas, q.image_box);
                    await uploadImg(currentBatchId, i, imgBase64, 'question');
                }

                // Process Options Images
                if (q.option_image_boxes) {
                    for (const key in q.option_image_boxes) {
                        const imgBase64 = crop(canvas, q.option_image_boxes[key]);
                        await uploadImg(currentBatchId, i, imgBase64, 'option_'+key);
                    }
                }

                let p = 70 + Math.round(((i+1)/allQuestions.length) * 30);
                progressBar.style.width = `${p}%`;
                percentText.innerText = `${p}%`;
            }

            if (!isStopped) {
                window.location.href = "{{ url('admin/ai-import/preview') }}/" + currentBatchId;
            }

        } catch (err) {
            document.getElementById('error-box').classList.remove('hidden');
            document.getElementById('error-msg').innerText = err.message;
        }
    });

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
        await new Promise(r => setTimeout(r, 500)); // Minor debounce
    }

    stopBtn.addEventListener('click', () => { if(confirm("Abort process?")) { isStopped = true; location.reload(); }});
</script>

<style>
    .border-3 { border-width: 3px; }
</style>
@endsection
