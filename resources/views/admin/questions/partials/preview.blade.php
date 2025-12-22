{{-- Wrapper ID for MathJax targeting --}}
<div id="question-preview-content" class="overflow-hidden bg-white rounded-xl font-sans">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Question Preview</h3>
            <p class="font-mono text-xs text-gray-500">ID: {{ $question->code }} • {{ $question->questionType->name }}</p>
        </div>
        <div class="flex gap-2">
            <span class="px-2 py-1 text-xs text-blue-700 border border-blue-100 rounded bg-blue-50">Marks: {{ $question->default_marks }}</span>
            <span class="px-2 py-1 text-xs text-purple-700 border border-purple-100 rounded bg-purple-50">Time: {{ $question->default_time }}s</span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- Question Text --}}
        <div>
            <h4 class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">Question Text</h4>
            <div class="p-4 prose-sm prose text-gray-900 border border-gray-200 rounded-lg max-w-none bg-gray-50 math-content">
                {!! $question->question !!}
            </div>
        </div>

        {{-- Options --}}
        <div>
            <h4 class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">Options & Answer</h4>
            <div class="grid grid-cols-1 gap-3">
                @php
                    $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                @endphp

                @foreach($options as $opt)
                    @php
                        $isCorrect = false;
                        if(is_array($question->correct_answer)) {
                            $isCorrect = in_array($loop->index, $question->correct_answer);
                        } else {
                            $isCorrect = $question->correct_answer == $loop->index;
                        }
                    @endphp

                    <div class="flex items-start justify-between p-4 rounded-lg border-2 transition-all
                        {{ $isCorrect
                            ? 'border-green-500 bg-green-50 ring-1 ring-green-500/20'
                            : 'border-gray-200 bg-white'
                        }}">

                        <div class="flex items-start gap-3 w-full">
                            {{-- Label --}}
                            <span class="flex-shrink-0 flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full mt-0.5
                                {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ chr(65 + $loop->index) }}
                            </span>

                            <div class="flex flex-col gap-2 w-full">
                                {{-- Option Text (Math Class Added) --}}
                                @if(!empty($opt['option']))
                                    <div class="text-sm font-medium math-content {{ $isCorrect ? 'text-green-800' : 'text-gray-700' }}">
                                        {!! $opt['option'] !!}
                                    </div>
                                @endif

                                {{-- Option Image --}}
                                @if(!empty($opt['image']))
                                    <div class="mt-1">
                                        <img src="{{ asset($opt['image']) }}" class="h-24 w-auto object-contain rounded-lg border border-gray-200 bg-white p-1" alt="Option Image">
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Correct Badge --}}
                        @if($isCorrect)
                            <div class="flex-shrink-0 flex items-center gap-1 px-2 py-1 text-xs font-bold text-green-700 bg-white border border-green-200 rounded shadow-sm ml-2">
                                <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                                Correct
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Solution --}}
        @if($question->solution)
            <div>
                <h4 class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">Solution / Explanation</h4>
                <div class="p-4 text-sm text-gray-800 border border-blue-100 rounded-lg bg-blue-50/50 math-content">
                    {!! $question->solution !!}
                </div>
            </div>
        @endif

        {{-- Metadata --}}
        <div class="grid grid-cols-2 gap-4 pt-4 text-xs text-gray-500 border-t border-gray-100 md:grid-cols-4">
            <div>
                <span class="block font-bold text-gray-400">Skill</span>
                {{ $question->skill->name ?? 'N/A' }}
            </div>
            <div>
                <span class="block font-bold text-gray-400">Topic</span>
                {{ $question->topic->name ?? 'N/A' }}
            </div>
            <div>
                <span class="block font-bold text-gray-400">Difficulty</span>
                {{ $question->difficultyLevel->name ?? 'N/A' }}
            </div>
            <div>
                <span class="block font-bold text-gray-400">Status</span>
                <span class="{{ $question->is_active ? 'text-green-600' : 'text-orange-500' }} font-bold">
                    {{ $question->is_active ? 'Active' : 'Pending' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{--
    MATHJAX RELOAD SCRIPT
    Yeh script ensure karega ki math formulas sundar dikhen.
--}}
<script>
    (function() {
        const previewElement = document.getElementById('question-preview-content');

        if (typeof MathJax !== 'undefined') {
            // Version 3.x Support
            if (MathJax.typesetPromise) {
                MathJax.typesetPromise([previewElement]).then(() => {
                    console.log('MathJax 3 rendered preview');
                }).catch((err) => console.log('MathJax error: ' + err.message));
            }
            // Version 2.x Support (Legacy)
            else if (MathJax.Hub) {
                MathJax.Hub.Queue(["Typeset", MathJax.Hub, previewElement]);
            }
        }
    })();
</script>
