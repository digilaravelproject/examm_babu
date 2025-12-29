@extends('layouts.admin')

@section('content')
<div class="min-h-screen p-6 bg-gray-50">
    <div class="max-w-4xl mx-auto">

        {{-- Main Card --}}
        <div class="overflow-hidden bg-white shadow-2xl rounded-3xl ring-1 ring-black ring-opacity-5">

            {{-- Professional Header --}}
            <div class="relative bg-gradient-to-r from-[#0777be] to-[#055a91] px-10 py-8 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight text-white">Bulk Import Questions</h2>
                        <p class="mt-2 text-blue-100 text-md">
                            Seamlessly upload large datasets using our smart queue system.
                        </p>
                    </div>
                    <div class="hidden sm:block">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-100 bg-blue-800 bg-opacity-50 border border-blue-400 rounded-full">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                            Queue Mode Active
                        </span>
                    </div>
                </div>
                {{-- Decorative pattern --}}
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
            </div>

            <div class="px-10 py-8 space-y-8">

                {{-- ALERTS SECTION --}}

                {{-- Success Box --}}
                <div id="success-box" class="items-center hidden p-4 mb-4 text-green-800 border border-green-200 bg-green-50 rounded-xl animate-fade-in-down">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold">Import Successful!</h4>
                            <p class="text-sm">All questions have been processed. Redirecting you now...</p>
                        </div>
                    </div>
                </div>

                {{-- Error Box --}}
                <div id="error-box" class="hidden p-4 mb-4 text-red-800 border border-red-200 bg-red-50 rounded-xl animate-fade-in-down">
                    {{-- Content injected via JS --}}
                </div>

                {{-- Progress Area --}}
                <div id="progress-container" class="hidden space-y-3">
                    <div class="flex justify-between text-sm font-medium text-gray-600">
                        <span id="progress-text" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Initializing...
                        </span>
                        <span id="percent-text" class="font-bold text-[#0777be]">0%</span>
                    </div>
                    <div class="w-full h-4 overflow-hidden bg-gray-100 rounded-full shadow-inner">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-[#0777be] to-[#3b82f6] transition-all duration-500 ease-out relative" style="width: 0%">
                             <div class="absolute inset-0 bg-white opacity-20 w-full h-full animate-[pulse_2s_infinite]"></div>
                        </div>
                    </div>
                </div>

                {{-- Main Form --}}
                <form id="importForm" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- Grid Layout for Steps --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                        {{-- Step 1: Template --}}
                        <div class="relative p-6 transition-all duration-300 border border-gray-200 rounded-2xl bg-gray-50 hover:bg-white hover:shadow-md group">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-gray-400 rounded-full group-hover:bg-[#0777be] transition-colors">1</span>
                                    <h3 class="font-bold text-gray-700">Get Template</h3>
                                </div>
                                <p class="mb-6 text-sm text-gray-500">Download the strict format to avoid errors during import.</p>
                                <div class="mt-auto">
                                    <a href="{{ route('admin.questions.import.sample') }}" class="flex items-center justify-center w-full gap-2 px-4 py-3 text-sm font-bold text-gray-700 transition-all bg-white border border-gray-300 shadow-sm rounded-xl hover:bg-gray-50 hover:text-[#0777be]">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Download XLSX
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Upload Area (Custom UI) --}}
                        <div class="relative p-6 transition-all duration-300 border-2 border-blue-200 border-dashed rounded-2xl bg-blue-50/50 hover:bg-blue-50 group">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-[#0777be] rounded-full shadow-lg shadow-blue-200">2</span>
                                    <h3 class="font-bold text-gray-800">Upload Data</h3>
                                </div>

                                {{-- Hidden Input --}}
                                <input type="file" name="excel_file" id="fileInput" accept=".xlsx, .csv, .xls" class="hidden">

                                {{-- Custom Trigger --}}
                                <label for="fileInput" class="flex flex-col items-center justify-center flex-1 w-full h-32 mt-2 transition-all border-2 border-white border-dashed cursor-pointer rounded-xl hover:border-blue-400 bg-white/50 hover:bg-white" id="dropZone">
                                    <div class="text-center" id="emptyState">
                                        <svg class="w-10 h-10 mx-auto mb-2 text-blue-300 transition-colors group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        <p class="text-sm font-medium text-gray-600">Click to Browse</p>
                                        <p class="text-xs text-gray-400">or drag file here</p>
                                    </div>

                                    {{-- Selected State --}}
                                    <div id="selectedState" class="hidden w-full px-4 text-center">
                                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-2 bg-green-100 rounded-full">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p id="fileName" class="text-sm font-bold text-gray-800 truncate">filename.xlsx</p>
                                        <p class="text-xs text-blue-500">Ready to upload</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-4">
                        <button type="submit" id="submitBtn" disabled class="flex items-center justify-center w-full gap-2 py-4 text-lg font-bold text-white transition-all bg-gray-300 shadow-none cursor-not-allowed rounded-xl group">
                            <span>Select a File First</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Footer Info --}}
        <div class="mt-6 text-sm text-center text-gray-400">
            <p>System optimized for large datasets. Supports auto-correction for formatted cells.</p>
        </div>
    </div>
</div>

<script>
// --- UI INTERACTION LOGIC ---
const fileInput = document.getElementById('fileInput');
const dropZone = document.getElementById('dropZone');
const emptyState = document.getElementById('emptyState');
const selectedState = document.getElementById('selectedState');
const fileNameDisplay = document.getElementById('fileName');
const submitBtn = document.getElementById('submitBtn');

// File Selection Handler
fileInput.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        let file = this.files[0];

        // Update UI
        emptyState.classList.add('hidden');
        selectedState.classList.remove('hidden');
        fileNameDisplay.innerText = file.name;
        dropZone.classList.add('border-green-400', 'bg-green-50');
        dropZone.classList.remove('border-white');

        // Enable Button
        submitBtn.disabled = false;
        submitBtn.classList.remove('bg-gray-300', 'cursor-not-allowed', 'shadow-none');
        submitBtn.classList.add('bg-[#0777be]', 'hover:bg-[#0666a3]', 'shadow-lg', 'hover:scale-[1.01]', 'cursor-pointer');
        submitBtn.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span>START IMPORT</span>
        `;
    }
});


// --- IMPORT LOGIC (QUEUE SYSTEM) ---
const CHUNK_SIZE = 100;

document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let progressBar = document.getElementById('progress-bar');
    let percentText = document.getElementById('percent-text');
    let progressText = document.getElementById('progress-text');
    let errorBox = document.getElementById('error-box');
    let successBox = document.getElementById('success-box');
    let progressContainer = document.getElementById('progress-container');

    // UI Locking
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Analyzing File...`;

    errorBox.classList.add('hidden');
    errorBox.innerHTML = '';
    successBox.classList.add('hidden');
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    percentText.innerText = '0%';

    let allErrors = [];

    try {
        // STEP 1: Upload
        let uploadRes = await fetch("{{ route('admin.questions.import.prepare') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        let uploadData = await uploadRes.json();

        if (!uploadData.success) {
            throw new Error(uploadData.message || 'Upload failed');
        }

        let total = uploadData.total_rows;
        let batchId = uploadData.batch_id;
        let processed = 0;

        submitBtn.innerHTML = `<svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Importing Data...`;

        // STEP 2: Process Loop
        while (processed < total) {
            let chunkRes = await fetch("{{ route('admin.questions.import.chunk') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    batch_id: batchId,
                    offset: processed,
                    limit: CHUNK_SIZE
                })
            });

            let chunkData = await chunkRes.json();

            if (!chunkData.success) {
                throw new Error(chunkData.message || 'Chunk processing failed');
            }

            if(chunkData.errors && chunkData.errors.length > 0) {
                allErrors = allErrors.concat(chunkData.errors);
            }

            processed += CHUNK_SIZE;

            // Smooth Progress Update
            let percent = Math.min(100, Math.round((processed / total) * 100));
            progressBar.style.width = percent + '%';
            percentText.innerText = percent + '%';

            // Text update logic
            progressText.innerHTML = `
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600 animate-pulse" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Importing: ${Math.min(processed, total)} / ${total}
                </span>
            `;
        }

        // FINISHED
        if (allErrors.length > 0) {
            submitBtn.innerText = "Completed with Errors";
            submitBtn.classList.remove('bg-[#0777be]');
            submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
            submitBtn.disabled = false;

            errorBox.classList.remove('hidden');
            errorBox.innerHTML = `
                <div class="flex items-center gap-2 pb-2 mb-2 font-bold border-b border-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Import Completed with Warnings:
                </div>
                <div class="overflow-y-auto max-h-40">
                    <ul class="pl-5 space-y-1 text-sm list-disc">` +
                    allErrors.map(e => `<li>${e}</li>`).join('') +
                    `</ul>
                </div>`;

        } else {
            // Success State
            submitBtn.innerText = "Success!";
            submitBtn.classList.remove('bg-[#0777be]');
            submitBtn.classList.add('bg-green-600');

            successBox.classList.remove('hidden');
            progressContainer.classList.add('hidden');

            // Redirect after delay
            setTimeout(() => { window.location.href = "{{ route('admin.questions.index') }}"; }, 2000);
        }

    } catch (err) {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>TRY AGAIN</span>`;
        submitBtn.classList.remove('bg-[#0777be]');
        submitBtn.classList.add('bg-gray-800');

        progressContainer.classList.add('hidden');
        errorBox.classList.remove('hidden');
        errorBox.innerText = "System Error: " + err.message;
    }
});
</script>
@endsection
