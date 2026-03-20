@extends('layouts.admin')

@section('content')
<div class="min-h-screen py-10 bg-gray-100">
    <div class="max-w-3xl mx-auto">

        {{-- Main Card --}}
        <div class="overflow-hidden bg-white shadow-xl rounded-2xl">

            {{-- Header --}}
            <div class="px-8 py-6 bg-gradient-to-br from-indigo-600 to-purple-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            <i class="mr-2 fas fa-robot"></i> AI Smart Import
                        </h2>
                        <p class="mt-1 text-sm text-indigo-100 opacity-90">
                            Upload PDF -> AI Extracts Questions -> Auto Save
                        </p>
                    </div>
                    <div class="hidden sm:block">
                        <span class="px-3 py-1 text-xs font-semibold text-white uppercase border rounded-full bg-white/20 border-white/30 backdrop-blur-sm">
                            Gemini 1.5 Pro
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-8">

                {{-- ALERTS AREA --}}
                <div id="success-box" class="hidden p-4 mb-6 text-green-700 border-l-4 border-green-500 rounded-md shadow-sm bg-green-50">
                    <div class="flex items-center">
                        <i class="mr-3 text-xl fas fa-check-circle"></i>
                        <div>
                            <h4 class="font-bold">Process Completed!</h4>
                            <p id="success-msg" class="text-sm">Questions have been imported successfully.</p>
                        </div>
                    </div>
                </div>

                <div id="error-box" class="hidden p-4 mb-6 text-red-700 border-l-4 border-red-500 rounded-md shadow-sm bg-red-50">
                    <div class="flex items-center">
                        <i class="mr-3 text-xl fas fa-exclamation-triangle"></i>
                        <span id="error-msg">Something went wrong.</span>
                    </div>
                </div>

                {{-- PROGRESS BAR AREA (Hidden by default) --}}
                <div id="progress-container" class="hidden mb-8">

                    {{-- Top Labels --}}
                    <div class="flex justify-between mb-2 align-bottom">
                        <span id="progress-status" class="text-sm font-bold text-indigo-700 animate-pulse">
                            <i class="mr-1 fas fa-circle-notch fa-spin"></i> Initializing...
                        </span>
                        <span id="percent-text" class="text-sm font-bold text-gray-700">0%</span>
                    </div>

                    {{-- The Bar --}}
                    <div class="relative w-full h-5 mb-3 overflow-hidden bg-gray-200 rounded-full shadow-inner">
                        <div id="progress-bar"
                             class="h-full transition-all duration-300 ease-out bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"
                             style="width: 0%;">
                        </div>
                    </div>

                    {{-- Stats & Stop Button --}}
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-xs text-gray-500">
                            <span class="mr-3">Batch: <span id="current-chunk" class="font-mono font-bold text-gray-700">0</span>/<span id="total-chunks" class="font-mono">0</span></span>
                            <span class="hidden sm:inline">Started: <span id="time-started" class="font-mono">--:--</span></span>
                        </div>

                        {{-- STOP BUTTON --}}
                        <button type="button" id="stopBtn" class="px-3 py-1 text-xs font-bold text-red-600 transition border border-red-200 rounded-lg hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <i class="mr-1 fas fa-stop-circle"></i> Stop Process
                        </button>
                    </div>

                    <p class="mt-2 text-[10px] text-center text-gray-400">Please do not refresh or close the page.</p>
                </div>

                {{-- FORM --}}
                <form id="aiImportForm" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-6">

                        {{-- Step 1 --}}
                        <div>
                            <label class="block mb-2 text-sm font-bold tracking-wide text-gray-700 uppercase">
                                1. Select Topic
                            </label>
                            <div class="relative">
                                <select name="topic_id" id="topicSelect" class="w-full px-4 py-3 text-gray-700 transition border border-gray-300 rounded-lg outline-none appearance-none bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Choose a Topic --</option>
                                    @foreach($topics as $topic)
                                        <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 pointer-events-none">
                                    <i class="text-xs fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2 --}}
                        <div>
                            <label class="block mb-2 text-sm font-bold tracking-wide text-gray-700 uppercase">
                                2. Upload Question Paper
                            </label>

                            <div id="dropZone" class="relative px-4 py-10 text-center transition-all border-2 border-indigo-200 border-dashed cursor-pointer group rounded-xl bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400">
                                <input type="file" name="pdf_file" id="fileInput" accept=".pdf" class="hidden">

                                {{-- State 1: Empty --}}
                                <div id="emptyState" class="transition-transform duration-200 group-hover:scale-105">
                                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3 bg-white rounded-full shadow-sm">
                                        <i class="text-3xl text-indigo-500 fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h3 class="font-semibold text-indigo-900">Click to upload PDF</h3>
                                    <p class="mt-1 text-xs text-indigo-400">Maximum size 100MB</p>
                                </div>

                                {{-- State 2: Selected --}}
                                <div id="fileInfo" class="hidden">
                                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3 bg-red-100 rounded-full">
                                        <i class="text-3xl text-red-500 fas fa-file-pdf"></i>
                                    </div>
                                    <p id="fileName" class="text-lg font-bold text-gray-800 break-all">filename.pdf</p>
                                    <p class="mt-1 text-sm font-medium text-green-600"><i class="fas fa-check"></i> Ready</p>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-4">
                            <button type="submit" id="startBtn" disabled
                                class="flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-gray-400 transition-all bg-gray-200 shadow-none cursor-not-allowed rounded-xl">
                                <span>Select Topic & File to Start</span>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', function() {
        const topicSelect = document.getElementById('topicSelect');
        const fileInput = document.getElementById('fileInput');
        const dropZone = document.getElementById('dropZone');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const percentText = document.getElementById('percent-text');
        const progressStatusEl = document.getElementById('progress-status');
        const errorBox = document.getElementById('error-box');
        const errorMsg = document.getElementById('error-msg');

        const URL_PROCESS = "{{ route('admin.ai-import.process') }}";
        const URL_UPLOAD_CROP = "{{ route('admin.ai-import.upload-cropped-image') }}";
        const URL_CANCEL = "{{ route('admin.ai-import.cancel') }}";

        let isStopped = false;
        let currentBatchId = null;
        let pdfDoc = null;

        function updateButtonState() {
            startBtn.disabled = !(topicSelect.value && fileInput.files.length);
            startBtn.className = startBtn.disabled ? 
                "flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-gray-400 bg-gray-200 rounded-xl cursor-not-allowed" :
                "flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:shadow-xl cursor-pointer";
        }

        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file && file.type === "application/pdf") {
                document.getElementById('emptyState').classList.add('hidden');
                document.getElementById('fileInfo').classList.remove('hidden');
                document.getElementById('fileName').innerText = 'Loading PDF...';
                
                const arrayBuffer = await file.arrayBuffer();
                pdfDoc = await pdfjsLib.getDocument(arrayBuffer).promise;
                document.getElementById('fileName').innerText = `${file.name} (${pdfDoc.numPages} pages)`;
                updateButtonState();
            }
        });
        topicSelect.addEventListener('change', updateButtonState);

        stopBtn.addEventListener('click', async () => {
            if (confirm("Stop processing?")) {
                isStopped = true;
                if (currentBatchId) fetch(URL_CANCEL, { method: 'POST', body: JSON.stringify({ batch_id: currentBatchId }), headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                location.reload();
            }
        });

        document.getElementById('aiImportForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            isStopped = false;
            startBtn.disabled = true;
            progressContainer.classList.remove('hidden');
            errorBox.classList.add('hidden');

            try {
                progressStatusEl.innerText = "Step 1: AI Reading PDF (This may take a minute)...";
                progressBar.style.width = "20%";
                percentText.innerText = "20%";

                const formData = new FormData();
                formData.append('topic_id', topicSelect.value);
                formData.append('pdf_file', fileInput.files[0]);

                const res = await fetch(URL_PROCESS, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                const data = await res.json();
                if (!data.success) throw new Error(data.message);

                currentBatchId = data.batch_id;
                const questions = data.questions;

                progressStatusEl.innerText = "Step 2: Processing Images & Formatting...";
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                for (let i = 0; i < questions.length; i++) {
                    if (isStopped) break;
                    const q = questions[i];
                    
                    if (q.image_box && q.page_number) {
                        progressStatusEl.innerText = `Cropping Image for Question ${i+1}...`;
                        
                        const page = await pdfDoc.getPage(q.page_number);
                        const viewport = page.getViewport({ scale: 2.0 });
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                        const [ymin, xmin, ymax, xmax] = q.image_box;
                        const cropX = (xmin / 1000) * canvas.width;
                        const cropY = (ymin / 1000) * canvas.height;
                        const cropW = ((xmax - xmin) / 1000) * canvas.width;
                        const cropH = ((ymax - ymin) / 1000) * canvas.height;

                        const cropCanvas = document.createElement('canvas');
                        cropCanvas.width = cropW;
                        cropCanvas.height = cropH;
                        cropCanvas.getContext('2d').drawImage(canvas, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);
                        
                        const base64 = cropCanvas.toDataURL('image/jpeg', 0.85);

                        await fetch(URL_UPLOAD_CROP, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ batch_id: currentBatchId, question_index: i, image_base64: base64 })
                        });
                    }

                    let percent = 20 + Math.round(((i + 1) / questions.length) * 80);
                    progressBar.style.width = `${percent}%`;
                    percentText.innerText = `${percent}%`;
                }

                if (!isStopped) {
                    window.location.href = "{{ url('admin/ai-import/preview') }}/" + currentBatchId;
                }

            } catch (err) {
                errorBox.classList.remove('hidden');
                errorMsg.innerText = err.message;
                startBtn.disabled = false;
            }
        });
    });
</script>
@endsection
