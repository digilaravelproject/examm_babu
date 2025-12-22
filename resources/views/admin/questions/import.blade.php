@extends('layouts.admin')

@section('content')
<div class="container-fluid p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="bg-[#0777be] px-8 py-6 text-white">
                <h2 class="text-2xl font-bold">Bulk Import Questions</h2>
                <p class="text-blue-100 text-sm">Upload Excel/CSV without any queue setup.</p>
            </div>

            <div class="p-8 space-y-6">
                {{-- Success Box --}}
                <div id="success-box" class="hidden p-4 bg-green-100 text-green-700 rounded-lg border border-green-200 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span>Import Completed Successfully!</span>
                </div>

                {{-- Error Box --}}
                <div id="error-box" class="hidden p-4 bg-red-100 text-red-700 rounded-lg border border-red-200"></div>

                {{-- Progress Bar --}}
                <div id="progress-container" class="hidden space-y-2">
                    <div class="flex justify-between font-bold text-sm text-gray-700">
                        <span id="progress-text">Processing...</span>
                        <span id="percent-text">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div id="progress-bar" class="bg-[#94c940] h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                {{-- Form --}}
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        {{-- Step 1 --}}
                        <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                            <label class="block text-gray-700 font-bold mb-2">Step 1: Download Template</label>
                            <a href="{{ route('admin.questions.import.sample') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50 text-gray-700 font-medium shadow-sm transition-all">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Download Sample Excel
                            </a>
                        </div>

                        {{-- Step 2 --}}
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Step 2: Upload File</label>
                            <input type="file" name="excel_file" required accept=".xlsx, .csv, .xls" class="w-full border p-3 rounded-xl bg-gray-50 focus:ring-2 focus:ring-[#0777be] outline-none">
                        </div>

                        {{-- Button --}}
                        <button type="submit" id="submitBtn" class="w-full py-4 bg-[#94c940] text-white rounded-xl font-bold shadow-lg hover:bg-green-600 transition-all flex justify-center items-center gap-3 text-lg">
                            <span>START IMPORT</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const CHUNK_SIZE = 50; // Har baar 50 questions bhejo

document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let btn = document.getElementById('submitBtn');
    let progressContainer = document.getElementById('progress-container');
    let progressBar = document.getElementById('progress-bar');
    let percentText = document.getElementById('percent-text');
    let progressText = document.getElementById('progress-text');
    let errorBox = document.getElementById('error-box');
    let successBox = document.getElementById('success-box');

    // Reset UI
    btn.disabled = true;
    btn.innerText = "Analyzing File...";
    errorBox.classList.add('hidden');
    successBox.classList.add('hidden');
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    percentText.innerText = '0%';

    try {
        // STEP 1: Upload and get Metadata
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

        btn.innerText = "Importing...";

        // STEP 2: Loop through chunks
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

            processed += CHUNK_SIZE;

            // Update Progress
            let percent = Math.min(100, Math.round((processed / total) * 100));
            progressBar.style.width = percent + '%';
            percentText.innerText = percent + '%';
            progressText.innerText = `Imported ${Math.min(processed, total)} of ${total} questions...`;
        }

        // Finished
        btn.innerText = "Completed";
        successBox.classList.remove('hidden');
        setTimeout(() => { window.location.href = "{{ route('admin.questions.index') }}"; }, 2000);

    } catch (err) {
        console.error(err);
        btn.disabled = false;
        btn.innerText = "START IMPORT";
        progressContainer.classList.add('hidden');
        errorBox.classList.remove('hidden');
        errorBox.innerText = err.message;
    }
});
</script>
@endsection
