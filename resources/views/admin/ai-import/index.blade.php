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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const topicSelect = document.getElementById('topicSelect');
        const fileInput = document.getElementById('fileInput');
        const dropZone = document.getElementById('dropZone');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn'); // <-- Stop Button

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
        const URL_CANCEL = "{{ route('admin.ai-import.cancel') }}"; // Ensure this route exists

        // State Variables
        let isStopped = false;
        let currentBatchId = null;

        // --- 1. UI Interaction Logic ---

        function updateButtonState() {
            const hasTopic = topicSelect.value !== "";
            const hasFile = fileInput.files.length > 0;

            if (hasTopic && hasFile) {
                startBtn.disabled = false;
                startBtn.className = "w-full py-4 text-lg font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.01] transition-all cursor-pointer flex items-center justify-center gap-2";
                startBtn.innerHTML = '<i class="fas fa-magic"></i> Start AI Extraction';
            } else {
                startBtn.disabled = true;
                startBtn.className = "w-full py-4 text-lg font-bold text-gray-400 bg-gray-200 rounded-xl cursor-not-allowed transition-all shadow-none flex items-center justify-center gap-2";
                startBtn.innerHTML = '<span>Select Topic & File to Start</span>';
            }
        }

        // Trigger File Input on Box Click
        dropZone.addEventListener('click', () => fileInput.click());

        // Handle File Selection
        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                document.getElementById('emptyState').classList.add('hidden');
                document.getElementById('fileInfo').classList.remove('hidden');
                document.getElementById('fileName').innerText = this.files[0].name;
            } else {
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('fileInfo').classList.add('hidden');
            }
            updateButtonState();
        });

        topicSelect.addEventListener('change', updateButtonState);

        // --- 2. STOP Button Logic ---
        stopBtn.addEventListener('click', async function() {
            if(!confirm("Are you sure you want to stop the AI process?")) return;

            isStopped = true;
            stopBtn.disabled = true;
            stopBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Stopping...';

            // Optional: Tell server to delete temporary files
            if (currentBatchId) {
                try {
                    await fetch(URL_CANCEL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ batch_id: currentBatchId })
                    });
                } catch(e) { console.error("Cancel API error", e); }
            }

            // Reset UI
            progressContainer.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorMsg.innerText = "Process was stopped by user.";

            startBtn.disabled = false;
            startBtn.innerHTML = 'Start New Import';
            startBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            updateButtonState();
        });


        // --- 3. Processing Logic (Progress Bar) ---

        document.getElementById('aiImportForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            isStopped = false; // Reset stop flag

            // Lock UI
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            startBtn.classList.add('opacity-75');

            // Enable Stop Button
            stopBtn.disabled = false;
            stopBtn.innerHTML = '<i class="mr-1 fas fa-stop-circle"></i> Stop Process';

            // Show Progress Area / Hide Alerts
            progressContainer.classList.remove('hidden');
            errorBox.classList.add('hidden');
            successBox.classList.add('hidden');

            // Reset Bar
            progressBar.style.width = '0%';
            percentText.innerText = '0%';
            currentChunkEl.innerText = '0';

            const formData = new FormData(this);
            let totalQuestions = 0;

            try {
                // STEP A: Prepare
                progressStatusEl.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Uploading PDF...';

                const prepRes = await fetch(URL_PREPARE, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                if(!prepRes.ok) throw new Error("Upload failed. Server error.");

                const prepData = await prepRes.json();
                if (!prepData.success) throw new Error(prepData.message);

                // Setup Progress
                currentBatchId = prepData.batch_id;
                const totalChunks = prepData.total_chunks;

                timeStartedEl.innerText = prepData.start_time || 'Now';
                totalChunksEl.innerText = totalChunks;

                // STEP B: Process Chunks
                for (let i = 0; i < totalChunks; i++) {

                    // CHECK STOP FLAG
                    if(isStopped) break;

                    // Update Status Text
                    progressStatusEl.innerHTML = `<i class="fas fa-brain fa-spin"></i> Analyzing Batch ${i+1}...`;
                    currentChunkEl.innerText = i + 1;

                    // API Call
                    const chunkRes = await fetch(URL_CHUNK, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ batch_id: currentBatchId, chunk_index: i })
                    });

                    if(isStopped) break; // Check again after await

                    const chunkData = await chunkRes.json();

                    if (!chunkData.success) {
                        throw new Error(chunkData.message || `Batch ${i+1} failed`);
                    }

                    totalQuestions += (chunkData.processed_count || 0);

                    // Update Progress Bar
                    let percent = Math.round(((i + 1) / totalChunks) * 100);
                    progressBar.style.width = `${percent}%`;
                    percentText.innerText = `${percent}%`;

                    // Optional delay for smoothness
                    if(totalChunks < 5) await new Promise(r => setTimeout(r, 300));
                }

                // STEP C: Completion (Only if not stopped)
                if(!isStopped) {
                    successBox.classList.remove('hidden');
                    document.getElementById('success-msg').innerHTML = `<strong>Success!</strong> ${totalQuestions} questions extracted and saved.`;

                    progressStatusEl.innerHTML = '<span class="text-green-600"><i class="fas fa-check"></i> Completed</span>';

                    // Reset Buttons
                    startBtn.innerHTML = 'Import Another File';
                    startBtn.disabled = false;
                    startBtn.classList.remove('opacity-75', 'cursor-not-allowed');

                    // Hide Stop Button when done
                    stopBtn.disabled = true;
                    stopBtn.innerHTML = 'Done';
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
