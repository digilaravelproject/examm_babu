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
                            DeepSeek V3
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
                                    <p class="mt-1 text-xs text-indigo-400">Maximum size 20MB</p>
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
        // Elements
        const topicSelect = document.getElementById('topicSelect');
        const fileInput = document.getElementById('fileInput');
        const dropZone = document.getElementById('dropZone');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');

        // Progress Elements
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const percentText = document.getElementById('percent-text');
        const progressStatusEl = document.getElementById('progress-status');
        const timeStartedEl = document.getElementById('time-started');
        const totalChunksEl = document.getElementById('total-chunks');
        const currentChunkEl = document.getElementById('current-chunk');

        // Alert Elements
        const errorBox = document.getElementById('error-box');
        const errorMsg = document.getElementById('error-msg');
        const successBox = document.getElementById('success-box');

        // URLs
        const URL_PREPARE = "{{ route('admin.ai-import.prepare') }}";
        const URL_CHUNK = "{{ route('admin.ai-import.chunk') }}";
        const URL_CANCEL = "{{ route('admin.ai-import.cancel') }}";

        // State Variables
        let isStopped = false;
        let currentBatchId = null;
        let pdfDocument = null;
        let totalPages = 0;

        // --- 1. UI Interaction Logic ---

        function updateButtonState() {
            const hasTopic = topicSelect.value !== "";
            const hasFile = fileInput.files.length > 0 && pdfDocument !== null;

            if (hasTopic && hasFile) {
                startBtn.disabled = false;
                startBtn.className = "flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-white transition-all shadow-lg cursor-pointer bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:shadow-xl hover:scale-[1.01]";
                startBtn.innerHTML = '<i class="fas fa-magic"></i> Start AI Extraction';
            } else {
                startBtn.disabled = true;
                startBtn.className = "flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-gray-400 transition-all bg-gray-200 shadow-none cursor-not-allowed rounded-xl";
                startBtn.innerHTML = '<span>Select Topic & Valid File to Start</span>';
            }
        }

        // Trigger File Input on Box Click
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle File Selection and Load PDF.js
        fileInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (file) {
                if(file.type !== "application/pdf") {
                    alert("Please select a valid PDF file.");
                    fileInput.value = "";
                    return;
                }

                document.getElementById('emptyState').classList.add('hidden');
                document.getElementById('fileInfo').classList.remove('hidden');
                document.getElementById('fileName').innerText = 'Loading PDF...';

                try {
                    const fileReader = new FileReader();
                    fileReader.onload = async function() {
                        const typedarray = new Uint8Array(this.result);
                        try {
                            pdfDocument = await pdfjsLib.getDocument(typedarray).promise;
                            totalPages = pdfDocument.numPages;
                            document.getElementById('fileName').innerHTML = file.name + ` <span class="text-xs text-gray-500">(${totalPages} pages)</span>`;
                            updateButtonState();
                        } catch(err) {
                            alert("Failed to read PDF. It might be corrupted.");
                            console.error(err);
                            pdfDocument = null;
                            fileInput.value = "";
                            document.getElementById('emptyState').classList.remove('hidden');
                            document.getElementById('fileInfo').classList.add('hidden');
                            updateButtonState();
                        }
                    };
                    fileReader.readAsArrayBuffer(file);
                } catch(e) {
                    console.error("FileReader Error:", e);
                }
            } else {
                pdfDocument = null;
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('fileInfo').classList.add('hidden');
                updateButtonState();
            }
        });

        topicSelect.addEventListener('change', updateButtonState);

        // --- 2. STOP Button Logic ---
        stopBtn.addEventListener('click', async function() {
            if(!confirm("Are you sure you want to stop the AI process?")) return;

            isStopped = true;
            stopBtn.disabled = true;
            stopBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Stopping...';

            if (currentBatchId) {
                try {
                    await fetch(URL_CANCEL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ batch_id: currentBatchId })
                    });
                } catch(e) { console.error("Cancel API error", e); }
            }

            progressContainer.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorMsg.innerText = "Process was stopped by user.";

            startBtn.disabled = false;
            startBtn.innerHTML = 'Start New Import';
            startBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            updateButtonState();
        });


        // --- 3. Processing Logic (PDF -> Canvas -> Base64 -> AI) ---

        document.getElementById('aiImportForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if(!pdfDocument) return;

            isStopped = false; // Reset stop flag

            // Lock UI
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            startBtn.classList.add('opacity-75');

            stopBtn.disabled = false;
            stopBtn.innerHTML = '<i class="mr-1 fas fa-stop-circle"></i> Stop Process';

            // Show Progress Area / Hide Alerts
            progressContainer.classList.remove('hidden');
            errorBox.classList.add('hidden');
            successBox.classList.add('hidden');

            progressBar.style.width = '0%';
            percentText.innerText = '0%';
            currentChunkEl.innerText = '0';

            let totalQuestions = 0;

            try {
                // STEP A: Prepare Batch metadata on server
                progressStatusEl.innerHTML = '<i class="fas fa-server"></i> Initiating Batch...';

                // Send topic_id and total_chunks to prepare endpoint
                const prepDataPayload = new FormData();
                prepDataPayload.append('topic_id', topicSelect.value);
                prepDataPayload.append('total_chunks', totalPages);

                const prepRes = await fetch(URL_PREPARE, {
                    method: 'POST',
                    body: prepDataPayload,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                if(!prepRes.ok) throw new Error("Initialization failed. Server error.");

                const prepData = await prepRes.json();
                if (!prepData.success) throw new Error(prepData.message);

                currentBatchId = prepData.batch_id;
                timeStartedEl.innerText = prepData.start_time || 'Now';
                totalChunksEl.innerText = totalPages;

                // Hidden canvas for PDF rendering
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");

                // STEP B: Process Pages
                for (let i = 1; i <= totalPages; i++) {
                    if(isStopped) break;

                    progressStatusEl.innerHTML = `<i class="fas fa-image fa-pulse"></i> Rendering Page ${i}...`;

                    // Render PDF Page to Base64
                    const page = await pdfDocument.getPage(i);
                    // Higher scale for better OCR accuracy
                    const viewport = page.getViewport({ scale: 2.0 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                    const imageBase64 = canvas.toDataURL("image/jpeg", 0.85);

                    if(isStopped) break;

                    progressStatusEl.innerHTML = `<i class="fas fa-brain fa-spin"></i> API Extracting Questions from Page ${i}...`;
                    currentChunkEl.innerText = i;

                    // Send Image to Server Chunk processing
                    const chunkRes = await fetch(URL_CHUNK, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            batch_id: currentBatchId,
                            chunk_index: i - 1,
                            image_base64: imageBase64
                        })
                    });

                    if(isStopped) break;

                    if(!chunkRes.ok) {
                        throw new Error(`Server returned status ${chunkRes.status} for Page ${i}`);
                    }

                    const chunkData = await chunkRes.json();

                    if (!chunkData.success) {
                        throw new Error(chunkData.message || `Page ${i} processing failed`);
                    }

                    totalQuestions += (chunkData.processed_count || 0);

                    // Update Progress Bar
                    let percent = Math.round((i / totalPages) * 100);
                    progressBar.style.width = `${percent}%`;
                    percentText.innerText = `${percent}%`;
                }

                // Clean up canvas memory
                canvas.width = 0;
                canvas.height = 0;

                // STEP C: Completion (Only if not stopped)
                if(!isStopped) {
                    successBox.classList.remove('hidden');
                    document.getElementById('success-msg').innerHTML = `<strong>Success!</strong> Extraction completed. Redirecting to preview...`;

                    progressStatusEl.innerHTML = '<span class="text-green-600"><i class="fas fa-check"></i> Completed</span>';

                    setTimeout(function() {
                        window.location.href = "{{ url('admin/ai-import/preview') }}/" + currentBatchId;
                    }, 1000);
                }

            } catch (error) {
                console.error(error);
                if(!isStopped) {
                    progressContainer.classList.add('hidden');
                    errorBox.classList.remove('hidden');
                    errorMsg.innerText = error.message || "An unexpected error occurred.";

                    startBtn.disabled = false;
                    startBtn.innerHTML = 'Try Again';
                    startBtn.classList.remove('opacity-75');
                    updateButtonState();
                }
            }
        });
    });
</script>
@endsection
