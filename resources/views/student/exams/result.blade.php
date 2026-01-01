<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result - {{ $session->exam->title }}</title>

    {{-- Fonts & Tailwind --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Roboto', sans-serif; }
        .score-card { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .stat-card { transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">

    {{-- Header --}}
    <header class="flex items-center h-16 px-6 bg-white border-b border-gray-200 shadow-sm">
        <div class="container flex items-center justify-between mx-auto">
            <h1 class="text-xl font-bold tracking-wide text-gray-800">Exam Result</h1>
            <a href="{{ route('student.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-[#3498db] transition">
                &larr; Back to Dashboard
            </a>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="container flex-1 max-w-5xl p-6 mx-auto">

        {{-- Exam Details Title --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ $session->exam->title }}</h2>
            <p class="text-sm text-gray-500">Attempted on: {{ $session->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            {{-- 1. SCORE CARD (Left Main) --}}
            <div class="space-y-6 md:col-span-1">
                <div class="overflow-hidden bg-white border border-gray-100 shadow-lg rounded-2xl">
                    <div class="p-8 text-center text-white score-card">
                        <div class="mb-2 text-sm font-medium tracking-widest uppercase opacity-90">Total Score</div>
                        <div class="mb-1 text-5xl font-extrabold">
                            {{ $session->results['score'] }} <span class="text-2xl font-normal opacity-75">/ {{ $session->exam->total_marks }}</span>
                        </div>

                        <div class="inline-block px-4 py-1 mt-2 text-xs font-bold border rounded-full bg-white/20 backdrop-blur-sm border-white/30">
                            {{ $session->results['percentage'] }}% Secured
                        </div>
                    </div>

                    <div class="p-6 text-center">
                        <div class="mb-4">
                            <span class="block text-xs font-bold text-gray-400 uppercase">Status</span>
                            @if(($session->results['pass_or_fail'] ?? 'Failed') === 'Passed')
                                <span class="text-2xl font-bold text-green-600">PASSED 🎉</span>
                            @else
                                <span class="text-2xl font-bold text-red-600">FAILED</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <div class="text-xs text-gray-500">Accuracy</div>
                                <div class="text-lg font-bold text-gray-800">{{ $session->results['accuracy'] }}%</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Time Taken</div>
                                <div class="text-lg font-bold text-gray-800">{{ gmdate('H:i:s', $session->total_time_taken) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="grid gap-3">
                    {{-- <a href="#" class="block w-full py-3 text-center bg-[#3498db] hover:bg-[#2980b9] text-white font-bold rounded-xl shadow transition">
                        View Detailed Solution
                    </a> --}}
                    <a href="{{ route('student.dashboard') }}" class="block w-full py-3 font-bold text-center text-gray-600 transition bg-white border-2 border-gray-200 hover:border-gray-400 hover:text-gray-800 rounded-xl">
                        Go to Dashboard
                    </a>
                </div>
            </div>

            {{-- 2. DETAILED STATS (Right Grid) --}}
            <div class="md:col-span-2">
                <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-2xl md:p-8">
                    <h3 class="pb-2 mb-6 text-lg font-bold text-gray-800 border-b">Performance Analysis</h3>

                    <div class="grid grid-cols-2 gap-4 mb-8 sm:grid-cols-4">
                        {{-- Total Questions --}}
                        <div class="p-4 text-center border border-blue-100 stat-card bg-blue-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-blue-600">{{ $session->results['total_questions'] }}</div>
                            <div class="text-xs font-bold text-blue-400 uppercase">Total Qs</div>
                        </div>

                        {{-- Attempted --}}
                        <div class="p-4 text-center border border-gray-200 stat-card bg-gray-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-gray-700">{{ $session->results['answered_questions'] }}</div>
                            <div class="text-xs font-bold text-gray-400 uppercase">Attempted</div>
                        </div>

                        {{-- Correct --}}
                        <div class="p-4 text-center border border-green-100 stat-card bg-green-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-green-600">{{ $session->results['correct_answered_questions'] }}</div>
                            <div class="text-xs font-bold text-green-500 uppercase">Correct</div>
                        </div>

                        {{-- Wrong --}}
                        <div class="p-4 text-center border border-red-100 stat-card bg-red-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-red-600">{{ $session->results['wrong_answered_questions'] }}</div>
                            <div class="text-xs font-bold text-red-400 uppercase">Wrong</div>
                        </div>
                    </div>

                    {{-- Marks Breakdown --}}
                    <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Marks Breakdown</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 font-bold text-green-600 bg-green-100 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </div>
                                <span class="font-medium text-gray-700">Marks Earned</span>
                            </div>
                            <span class="text-lg font-bold text-green-600">+{{ $session->results['marks_earned'] }}</span>
                        </div>

                        <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 font-bold text-red-600 bg-red-100 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                </div>
                                <span class="font-medium text-gray-700">Negative Marking</span>
                            </div>
                            <span class="text-lg font-bold text-red-600">-{{ $session->results['marks_deducted'] }}</span>
                        </div>

                        <div class="flex items-center justify-between p-4 mt-2 border border-blue-100 rounded-lg bg-blue-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 font-bold text-white bg-blue-600 rounded-full">
                                    =
                                </div>
                                <span class="font-bold text-gray-800">Final Score</span>
                            </div>
                            <span class="text-xl font-extrabold text-blue-700">{{ $session->results['score'] }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

</body>
</html>
