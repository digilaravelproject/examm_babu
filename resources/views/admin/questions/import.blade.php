@extends('layouts.admin')

@section('content')
<div class="container-fluid p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="bg-[#0777be] px-8 py-6 text-white">
                <h2 class="text-2xl font-bold">Bulk Import Questions</h2>
                <p class="text-blue-100 text-sm">Fast import directly from Excel/CSV.</p>
            </div>

            <div class="p-8 space-y-6">
                {{-- Dynamic Message Box --}}
                <div id="message-box" class="hidden p-4 rounded-lg border flex items-center gap-3">
                    {{-- JS will inject text here --}}
                </div>

                {{-- Form --}}
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">

                        {{-- Step 1: Download --}}
                        <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                            <label class="block text-gray-700 font-bold mb-2">Step 1: Get the Format</label>
                            <a href="{{ route('admin.questions.import.sample') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50 text-gray-700 font-medium shadow-sm transition-all">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Download Sample Excel File
                            </a>
                            <p class="text-xs text-gray-500 mt-2">Use this file to fill your questions. Do not change the header row.</p>
                        </div>

                        {{-- Step 2: Upload --}}
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Step 2: Upload Filled File</label>
                            <input type="file" name="excel_file" required accept=".xlsx, .csv, .xls" class="w-full border p-3 rounded-xl bg-gray-50 focus:ring-2 focus:ring-[#0777be] outline-none">
                        </div>

                        {{-- Submit Button --}}
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
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let btn = document.getElementById('submitBtn');
    let msgBox = document.getElementById('message-box');

    // Reset UI
    msgBox.classList.add('hidden');
    msgBox.className = "hidden p-4 rounded-lg border flex items-center gap-3"; // Reset classes

    // Disable button & Show Loading
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Processing Import...
    `;

    fetch("{{ route('admin.questions.import.post') }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        // Reset Button
        btn.disabled = false;
        btn.innerHTML = `<span>START IMPORT</span>`;

        msgBox.classList.remove('hidden');

        if(data.success) {
            // Success Style
            msgBox.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
            msgBox.innerHTML = `<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> ${data.message}`;

            // Clear Form
            document.getElementById('importForm').reset();

            // Redirect after 1.5 seconds
            setTimeout(() => { window.location.href = "{{ route('admin.questions.index') }}"; }, 1500);
        } else {
            // Error Style
            msgBox.classList.add('bg-red-100', 'text-red-700', 'border-red-200');
            msgBox.innerHTML = `<strong>Error:</strong> ${data.message}`;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<span>START IMPORT</span>`;
        msgBox.classList.remove('hidden');
        msgBox.classList.add('bg-red-100', 'text-red-700', 'border-red-200');
        msgBox.innerHTML = "Server Error. Please check your internet or file size.";
        console.error(err);
    });
});
</script>
@endsection
