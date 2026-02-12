<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $session->user->first_name }} - {{ $siteSettings->app_name ?? config('app.name') }}</title>

    {{-- Dynamic Favicon --}}
    @if($siteSettings->favicon_path)
        <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Facades\Storage::url($siteSettings->favicon_path) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('storage/site_images/logo1dotcom.png') }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

    {{-- MathJax --}}
    <script>
        window.MathJax = {
            tex: { inlineMath: [['\\(', '\\)'], ['$', '$']] },
            startup: { typeset: true }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

    <style>
        body { font-family: sans-serif; -webkit-print-color-adjust: exact; }
        .correct-bg { background-color: #d1fae5 !important; border-color: #10b981 !important; }
        .wrong-bg { background-color: #fee2e2 !important; border-color: #ef4444 !important; }
        .user-sel { border-width: 2px; }
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-inside: avoid; }
            body { background: white; }
            .shadow-xl { box-shadow: none; }
        }
    </style>
</head>
<body class="min-h-screen pb-10 bg-gray-100">

    <div class="max-w-5xl min-h-screen mx-auto overflow-hidden bg-white shadow-xl md:rounded-xl md:my-8 print:m-0 print:rounded-none">

        {{-- Header --}}
        <div class="flex flex-col items-center justify-between gap-4 p-6 text-white bg-gradient-to-r from-blue-800 to-indigo-900 md:p-8 md:flex-row print:bg-white print:text-black print:border-b-2 print:border-gray-800">

            {{-- Logo & App Name --}}
            <div class="flex items-center gap-4">
                @if($siteSettings->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($siteSettings->logo_path) }}" alt="{{ $siteSettings->app_name }}"
                         class="object-contain w-16 h-16 p-1 bg-white rounded-lg shadow-sm">
                @else
                    <img src="{{ asset('storage/site_images/logo1dotcom.png') }}" alt="Logo"
                         class="object-contain w-16 h-16 p-1 bg-white rounded-lg shadow-sm">
                @endif

                <div>
                    <div class="text-xs tracking-widest uppercase opacity-70">{{ $siteSettings->app_name ?? config('app.name', 'ExamBabu') }}</div>
                    <h1 class="text-2xl font-bold leading-tight md:text-3xl">{{ $session->exam->title }}</h1>
                    <p class="mt-1 text-sm opacity-90">Candidate: <span class="font-bold text-yellow-300 print:text-black">{{ $session->user->first_name }} {{ $session->user->last_name }}</span></p>
                </div>
            </div>

            {{-- Score --}}
            <div class="p-4 text-right rounded-lg bg-white/10 backdrop-blur-sm print:bg-transparent print:border print:border-gray-300">
                <div class="text-xs font-bold uppercase opacity-80">Final Score</div>
                <div class="text-4xl font-extrabold">{{ $session->results['score'] }} <span class="text-lg font-medium opacity-60">/ {{ $session->exam->total_marks }}</span></div>
                <div class="mt-1 text-xs">Attempted on: {{ $session->created_at->format('d M, Y') }}</div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 border-b border-gray-200 md:grid-cols-4">
            <div class="p-5 text-center border-b border-r md:border-b-0">
                <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">Total Questions</div>
                <div class="mt-1 text-2xl font-bold text-gray-800">{{ $session->results['total_questions'] }}</div>
            </div>
            <div class="p-5 text-center border-b border-r md:border-b-0">
                <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">Attempted</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ $session->results['answered_questions'] }}</div>
            </div>
            <div class="p-5 text-center border-r">
                <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">Accuracy</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ $session->results['accuracy'] }}%</div>
            </div>
            <div class="p-5 text-center">
                <div class="text-xs font-bold tracking-wide text-gray-500 uppercase">Result</div>
                <span class="inline-block mt-1 px-4 py-1 text-sm rounded-full font-bold {{ $session->results['pass_or_fail'] == 'Passed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $session->results['pass_or_fail'] }}
                </span>
            </div>
        </div>

        {{-- Questions List --}}
        <div class="p-4 md:p-8 bg-gray-50 print:bg-white print:p-0">
            @if(empty($reportData))
                <div class="py-10 text-center text-gray-500">No question data available to display.</div>
            @endif

            @foreach($reportData as $section)
                <div class="mb-10 page-break">
                    <h2 class="inline-block pb-3 mb-5 text-xl font-bold text-gray-800 border-b-2 border-indigo-500 print:text-black">{{ $section['name'] }}</h2>

                    @foreach($section['questions'] as $index => $q)
                        <div class="p-6 mb-6 bg-white border border-gray-200 shadow-sm rounded-xl page-break print:shadow-none print:border-gray-300">

                            {{-- Question Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 text-sm font-bold text-white bg-gray-800 rounded-md print:bg-black print:text-white">Q{{ $loop->iteration }}</span>
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide border px-2 py-0.5 rounded">{{ $q->type }}</span>
                                </div>
                                <div>
                                    @if($q->status == 'not_answered' || $q->status == 'not_visited')
                                        <span class="px-3 py-1 text-xs font-bold text-gray-500 bg-gray-100 border border-gray-200 rounded-full">Not Attempted</span>
                                    @elseif($q->is_correct)
                                        <span class="px-3 py-1 text-xs font-bold text-green-700 bg-green-100 border border-green-200 rounded-full print:border-black">Correct (+{{ $q->marks_earned }})</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-bold text-red-700 bg-red-100 border border-red-200 rounded-full print:border-black">Wrong (-{{ $q->marks_deducted }})</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Passage --}}
                            @if($q->passage)
                                <div class="p-5 mb-5 text-sm text-gray-800 border-l-4 border-blue-400 rounded-r shadow-inner bg-blue-50 print:bg-gray-50 print:border-black">
                                    <strong class="block mb-2 text-base text-blue-900 print:text-black">{{ $q->passage['title'] }}</strong>
                                    <div class="prose-sm prose max-w-none">{!! $q->passage['body'] !!}</div>
                                </div>
                            @endif

                            {{-- Question Body --}}
                            <div class="mb-6 text-lg font-medium leading-relaxed text-gray-900 print:text-black">
                                {!! $q->text !!}
                            </div>

                            {{-- Options --}}
                            <div class="mb-6 space-y-3">
                                @if(($q->type === 'MSA' || $q->type === 'MMA' || $q->type === 'TOF') && is_array($q->options))
                                    @foreach($q->options as $optIdx => $opt)
                                        @php
                                            $userVal = $q->user_answer;
                                            $correctVal = $q->correct_answer;

                                            if(is_array($userVal)) $isUser = in_array($optIdx, $userVal);
                                            else $isUser = ($userVal == $optIdx);

                                            if(is_array($correctVal)) $isCorrect = in_array($optIdx, $correctVal);
                                            else $isCorrect = ($correctVal == $optIdx);

                                            $classes = "p-4 rounded-lg border flex justify-between items-center transition ";

                                            if ($isCorrect) $classes .= "correct-bg print:border-black print:font-bold ";
                                            elseif ($isUser && !$isCorrect) $classes .= "wrong-bg print:border-black ";
                                            else $classes .= "bg-white border-gray-200";

                                            if ($isUser) $classes .= " user-sel ring-2 ring-offset-1 " . ($isCorrect ? 'ring-green-500' : 'ring-red-500');
                                        @endphp

                                        <div class="{{ $classes }}">
                                            <div class="flex flex-1 gap-3">
                                                <span class="font-bold text-gray-400 print:text-black">{{ $loop->iteration }}.</span>
                                                <div class="text-gray-800 print:text-black">{!! $opt['option'] ?? $opt !!}</div>
                                            </div>
                                            @if($isUser) <span class="text-[10px] font-bold uppercase ml-2 px-2 py-1 rounded bg-gray-700 text-white print:text-black print:border print:border-black print:bg-transparent">You</span> @endif
                                            @if($isCorrect) <span class="text-[10px] font-bold uppercase ml-2 px-2 py-1 rounded bg-green-600 text-white print:text-black print:border print:border-black print:bg-transparent">Correct</span> @endif
                                        </div>
                                    @endforeach
                                @else
                                    {{-- For Text/FIB Types --}}
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div class="p-4 bg-gray-100 border border-gray-200 rounded-lg print:border-black">
                                            <div class="mb-2 text-xs font-bold text-gray-500 uppercase">Your Answer</div>
                                            <div class="font-mono text-sm font-semibold text-gray-700 break-words">
                                                {{ is_array($q->user_answer) ? json_encode($q->user_answer) : ($q->user_answer ?? 'Not Answered') }}
                                            </div>
                                        </div>
                                        <div class="p-4 border border-green-200 rounded-lg bg-green-50 print:border-black">
                                            <div class="mb-2 text-xs font-bold text-green-700 uppercase">Correct Answer</div>
                                            <div class="font-mono text-sm font-bold text-green-800 break-words">
                                                {{ is_array($q->correct_answer) ? json_encode($q->correct_answer) : $q->correct_answer }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Solution / Explanation --}}
                            @if(!empty($q->explanation))
                                <div class="p-5 border border-yellow-200 rounded-lg bg-yellow-50 print:bg-white print:border-black">
                                    <div class="flex items-center gap-2 pb-2 mb-2 font-bold text-yellow-800 border-b border-yellow-200 print:text-black print:border-black">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Solution
                                    </div>
                                    <div class="text-sm leading-relaxed prose text-gray-800 prose-yellow max-w-none print:text-black">
                                        {!! $q->explanation !!}
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Footer Button (Hidden in Print) --}}
        <div class="p-8 text-center bg-white border-t no-print">
            <button onclick="handlePrint()" class="flex items-center justify-center gap-2 px-8 py-3 mx-auto font-bold text-white transition transform bg-gray-800 rounded-lg shadow-lg hover:bg-black active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Download PDF / Print Report
            </button>
            <p class="mt-4 text-xs text-gray-400">If print doesn't start, please press Ctrl+P</p>
        </div>
    </div>

    {{-- Improved Print Script --}}
    <script>
        function handlePrint() {
            // Ensure MathJax is rendered before printing
            if (window.MathJax && window.MathJax.typesetPromise) {
                window.MathJax.typesetPromise().then(() => {
                    setTimeout(() => {
                        window.print();
                    }, 500); // Small delay to ensure rendering
                }).catch((err) => {
                    console.error('MathJax error:', err);
                    window.print(); // Fallback
                });
            } else {
                window.print();
            }
        }
    </script>
</body>
</html>
