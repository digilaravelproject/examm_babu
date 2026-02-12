{{-- Wrapper ID for MathJax targeting --}}
<div id="question-preview-content" class="overflow-hidden bg-white rounded-xl font-sans shadow-sm border border-gray-100">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Question Preview</h3>
            <p class="font-mono text-xs text-gray-500 mt-1">
                ID: <span class="bg-gray-200 px-1 rounded">{{ $question->code }}</span> •
                Type: <span class="font-semibold">{{ $question->questionType->name }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 text-xs font-bold text-blue-700 border border-blue-100 rounded-full bg-blue-50">
                Marks: {{ $question->default_marks }}
            </span>
            <span class="px-3 py-1 text-xs font-bold text-purple-700 border border-purple-100 rounded-full bg-purple-50">
                Time: {{ $question->default_time }}s
            </span>
        </div>
    </div>

    <div class="p-6 space-y-8">

        {{-- 1. Comprehension Passage --}}
        @if ($question->comprehension_passage_id && $question->comprehensionPassage)
            <div class="bg-blue-50/30 border-l-4 border-blue-500 rounded-r-lg p-5">
                <h4 class="mb-3 text-xs font-bold tracking-wider text-blue-400 uppercase">Comprehension Passage</h4>
                <div class="prose-sm prose text-gray-900 max-w-none math-content">
                    <h3 class="mt-0 text-blue-900 font-bold">{{ $question->comprehensionPassage->title }}</h3>
                    {!! $question->comprehensionPassage->body !!}
                </div>
            </div>
        @endif

        {{-- 2. Question Text --}}
        <div>
            <h4 class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">Question Text</h4>
            <div class="p-5 prose-sm prose text-gray-900 border border-gray-200 rounded-xl bg-gray-50/50 max-w-none math-content shadow-sm">
                @if ($question->questionType->code === 'FIB')
                    @php
                        // Replace ##Answer## with a visual blank line
                        $fibQuestion = preg_replace_callback(
                            '/##(.*?)##/',
                            function ($matches) {
                                return '<span class="font-mono font-bold text-gray-400 inline-block px-4 border-b-2 border-gray-300 bg-white mx-1 rounded-t">________</span>';
                            },
                            $question->question
                        );
                    @endphp
                    {!! $fibQuestion !!}
                @else
                    {!! $question->question !!}
                @endif
            </div>
        </div>

        {{-- 3. Options & Answers --}}
        <div>
            <h4 class="mb-3 text-xs font-bold tracking-wider text-gray-400 uppercase">Options & Correct Answer</h4>
            <div class="space-y-3">
                @php
                    $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                @endphp

                {{-- TYPE: MATCH THE FOLLOWING (MTF) --}}
                @if ($question->questionType->code === 'MTF')
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="grid grid-cols-2 bg-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider text-center border-b">
                            <div class="py-2 border-r">Column A (Question)</div>
                            <div class="py-2">Column B (Answer Pair)</div>
                        </div>

                        @foreach ($options as $i => $opt)
                            @php
                                $optArr = is_array($opt) ? $opt : (array)$opt;
                                $left = $optArr['option'] ?? '';
                                $pair = $optArr['pair'] ?? '';

                                // Logic: If Pair is empty but Option has comma, split it
                                if (empty($pair) && !empty($left) && strpos($left, ',') !== false) {
                                    $parts = explode(',', $left, 2);
                                    $left = trim($parts[0]);
                                    $pair = trim($parts[1]);
                                }
                            @endphp
                            <div class="grid grid-cols-2 border-b last:border-b-0">
                                {{-- Left Side --}}
                                <div class="p-4 border-r border-gray-100 bg-white flex items-center gap-3">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 text-gray-600 font-bold text-xs flex items-center justify-center">{{ $i+1 }}</span>
                                    <div class="text-sm text-gray-800 math-content">{!! $left !!}</div>
                                </div>
                                {{-- Right Side --}}
                                <div class="p-4 bg-blue-50/20 flex items-center gap-3">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 font-bold text-xs flex items-center justify-center">{{ chr(65 + $i) }}</span>
                                    <div class="text-sm text-gray-800 math-content">{!! $pair !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                {{-- TYPE: FILL IN BLANKS (FIB) --}}
                @elseif ($question->questionType->code === 'FIB')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h5 class="text-xs font-bold text-green-700 uppercase mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Correct Answer(s):
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            @php
                                // Extract answers again for display if not saved or just use regex on question text
                                preg_match_all('/##(.*?)##/', $question->question, $matches);
                                $fibAnswers = $matches[1] ?? [];
                            @endphp

                            @if(count($fibAnswers) > 0)
                                @foreach($fibAnswers as $idx => $ans)
                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-bold bg-white text-green-800 border border-green-200 shadow-sm">
                                        {{-- <span class="text-green-400 mr-2 text-xs uppercase tracking-wide">Blank {{ $idx + 1 }}</span> --}}
                                        {{ $ans }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-sm text-red-500 italic">No answers detected in question text. Ensure format ##Answer## is used.</span>
                            @endif
                        </div>
                    </div>

                {{-- TYPE: OTHER (MSA, MMA, ORD, TOF) --}}
                @else
                    @foreach ($options as $opt)
                        @php
                            $optArr = is_array($opt) ? $opt : (array)$opt;
                            $isCorrect = false;

                            // Check correctness based on type
                            if (is_array($question->correct_answer)) {
                                // MMA Logic (Array of indices)
                                $isCorrect = in_array($loop->index, $question->correct_answer);
                            } else {
                                // MSA Logic (Single index)
                                $isCorrect = (string)$question->correct_answer === (string)$loop->index;
                            }
                        @endphp

                        <div class="relative flex items-start p-4 rounded-lg border transition-all
                            {{ $isCorrect ? 'border-green-500 bg-green-50/50 ring-1 ring-green-500/30' : 'border-gray-200 bg-white' }}">

                            <div class="flex items-center gap-4 w-full">
                                {{-- Index Label --}}
                                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold
                                    {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                    {{ chr(65 + $loop->index) }}
                                </span>

                                <div class="flex-1 space-y-2">
                                    {{-- Option Text --}}
                                    @if (!empty($optArr['option']))
                                        <div class="text-sm font-medium math-content {{ $isCorrect ? 'text-green-900' : 'text-gray-700' }}">
                                            {!! $optArr['option'] !!}
                                        </div>
                                    @endif

                                    {{-- Option Image --}}
                                    @if (!empty($optArr['image']))
                                        <div>
                                            <img src="{{ asset($optArr['image']) }}" class="h-20 w-auto object-contain rounded border border-gray-200 bg-white p-1">
                                        </div>
                                    @endif
                                </div>

                                {{-- Correct Badge --}}
                                @if ($isCorrect)
                                    <div class="absolute top-0 right-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-bl-lg rounded-tr-lg text-xs font-bold bg-green-500 text-white">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            Correct
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- 4. Solution Details --}}
        @if ($question->solution)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl overflow-hidden">
                <div class="px-6 py-3 border-b border-yellow-100 bg-yellow-100/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    <h4 class="text-sm font-bold text-yellow-800 uppercase tracking-wide">Detailed Solution</h4>
                </div>
                <div class="p-6">
                    <div class="prose-sm prose text-gray-800 math-content max-w-none">
                        {!! $question->solution !!}
                    </div>
                    @if ($question->solution_video)
                        <div class="mt-4 pt-4 border-t border-yellow-200">
                            <a href="{{ $question->solution_video }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700 hover:underline">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                                Watch Video Explanation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Metadata Footer --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-100 text-xs text-gray-500">
            <div>
                <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Skill Category</span>
                <span class="font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded">{{ $question->skill->name ?? 'Uncategorized' }}</span>
            </div>
            <div>
                <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Topic</span>
                <span class="font-semibold text-gray-700">{{ $question->topic->name ?? 'General' }}</span>
            </div>
            <div>
                <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Difficulty</span>
                <span class="font-semibold text-gray-700">{{ $question->difficultyLevel->name ?? 'Medium' }}</span>
            </div>
            <div>
                <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Status</span>
                <span class="inline-flex items-center gap-1 font-bold {{ $question->is_active ? 'text-green-600' : 'text-orange-500' }}">
                    <span class="w-2 h-2 rounded-full {{ $question->is_active ? 'bg-green-500' : 'bg-orange-500' }}"></span>
                    {{ $question->is_active ? 'Active' : 'Pending Review' }}
                </span>
            </div>
        </div>

    </div>
</div>

{{--
    MATHJAX RELOAD SCRIPT
    Ensures math renders even after AJAX loads
--}}
<script>
    (function() {
        const previewElement = document.getElementById('question-preview-content');

        if (typeof MathJax !== 'undefined') {
            // Version 3.x Support
            if (MathJax.typesetPromise) {
                MathJax.typesetPromise([previewElement]).then(() => {
                    console.log('MathJax rendered preview successfully.');
                }).catch((err) => console.log('MathJax error: ' + err.message));
            }
            // Version 2.x Support (Legacy fallback)
            else if (MathJax.Hub) {
                MathJax.Hub.Queue(["Typeset", MathJax.Hub, previewElement]);
            }
        }
    })();
</script>
