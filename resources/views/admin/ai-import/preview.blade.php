@extends('layouts.admin')

@section('content')
<div class="min-h-screen py-10 bg-gray-100">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <a href="{{ route('admin.ai-import.index') }}" class="text-indigo-600 hover:underline mb-2 inline-block"><i class="fas fa-arrow-left"></i> Back to Import</a>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-eye text-indigo-600 mr-2"></i> Preview Extracted Questions
                </h2>
                <p class="text-sm text-gray-500 mt-1">Review the questions extracted by AI. Images will be displayed if available.</p>
            </div>

            @if(count($questions) > 0)
            <button id="approveBtn" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700 hover:scale-105 transition-all focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex justify-center items-center">
                <i class="fas fa-check-circle mr-2"></i> Approve & Import All ({{ count($questions) }})
            </button>
            @endif
        </div>

        <div id="status-message" class="hidden p-4 mb-6 rounded-md shadow-sm"></div>

        @if(count($questions) == 0)
            <div class="p-8 text-center bg-white rounded-xl shadow border border-gray-100">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <i class="fas fa-folder-open text-3xl"></i>
                </div>
                <p class="text-gray-600 text-lg font-medium">No questions could be safely extracted.</p>
                <a href="{{ route('admin.ai-import.index') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition font-medium">Try Another File</a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($questions as $index => $q)
                    <div class="p-6 bg-white rounded-xl shadow relative overflow-hidden border border-gray-100">
                        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>

                        <div class="flex justify-between items-start mb-4">
                            <h4 class="font-bold text-lg text-gray-800 flex-1">
                                <span class="text-indigo-600 mr-2">Q{{ $index + 1 }}.</span>
                                {{-- Ye HTML allow karega taaki <img> tags correctly render ho --}}
                                {!! $q['question'] ?? 'N/A' !!}
                            </h4>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-indigo-100 text-indigo-700 border border-indigo-200 ml-2">
                                {{ $q['type'] ?? 'MSA' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            @if(isset($q['options']) && is_array($q['options']) && count($q['options']) > 0)
                                @foreach($q['options'] as $optIndex => $opt)
                                    @php
                                        $type = $q['type'] ?? 'MSA';
                                        $isCorrect = false;
                                        if ($type === 'MMA' && isset($q['correct_option_indices'])) {
                                            $isCorrect = in_array($optIndex, $q['correct_option_indices']);
                                        } else {
                                            $isCorrect = (isset($q['correct_option_index']) && $q['correct_option_index'] == $optIndex);
                                        }
                                        $letter = chr(65 + $optIndex);
                                    @endphp
                                    <div class="p-3 border rounded-lg {{ $isCorrect ? 'bg-green-50 border-green-300 shadow-sm' : 'bg-gray-50 border-gray-200' }} transition-colors">
                                        <span class="font-semibold {{ $isCorrect ? 'text-green-700' : 'text-gray-600' }} mr-2">{{ $letter }})</span>
                                        <span class="{{ $isCorrect ? 'text-green-900 font-medium' : 'text-gray-700' }}">{!! $opt !!}</span>
                                        @if($isCorrect)
                                            <i class="fas fa-check-circle text-green-500 float-right mt-1"></i>
                                        @endif
                                    </div>
                                @endforeach
                            @elseif(isset($q['correct_answer_text']))
                                <div class="col-span-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <strong class="text-blue-700 mr-2">Correct Answer:</strong>
                                    <span class="text-blue-900">{{ $q['correct_answer_text'] }}</span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($q['solution']) || !empty($q['hint']))
                            <div class="mt-4 p-4 bg-indigo-50 text-indigo-900 text-sm rounded-lg border border-indigo-100">
                                <strong><i class="fas fa-lightbulb text-yellow-500 mr-1"></i> Explanation & Hint:</strong> {!! $q['solution'] ?? $q['hint'] !!}
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
                if(!confirm("Are you sure you want to save all questions to the database?")) return;

                approveBtn.disabled = true;
                approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving to Database...';
                approveBtn.classList.add('opacity-75', 'cursor-not-allowed');

                try {
                    const response = await fetch("{{ route('admin.ai-import.approve', $batchId) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if(data.success) {
                        statusMsg.className = "p-4 mb-6 rounded-md shadow-sm bg-green-50 text-green-700 border-l-4 border-green-500";
                        statusMsg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Import completed successfully. Redirecting...');
                        statusMsg.classList.remove('hidden');

                        setTimeout(() => {
                            window.location.href = data.redirect || "{{ route('admin.ai-import.index') }}";
                        }, 1000);
                    } else {
                        throw new Error(data.message || 'Error approving batch. Please check logs.');
                    }
                } catch (error) {
                    approveBtn.disabled = false;
                    approveBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Approve & Import All ({{ count($questions) }})';
                    approveBtn.classList.remove('opacity-75', 'cursor-not-allowed');

                    statusMsg.className = "p-4 mb-6 rounded-md shadow-sm bg-red-50 text-red-700 border-l-4 border-red-500";
                    statusMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>' + error.message;
                    statusMsg.classList.remove('hidden');

                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    });
</script>
@endsection
