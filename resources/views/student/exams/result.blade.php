<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Result - {{ $session->exam->title }}</title>

    {{-- Fonts & Tailwind --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    {{-- Scripts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }

        .score-card {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .stat-card {
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        /* Loader Animation */
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-100">

    {{-- Header --}}
    <header class="flex items-center h-16 px-6 bg-white border-b border-gray-200 shadow-sm">
        <div class="container flex items-center justify-between mx-auto">
            <h1 class="text-xl font-bold tracking-wide text-gray-800">Exam Result</h1>
            <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('student.dashboard') }}"
                class="text-sm font-medium text-gray-500 hover:text-[#3498db] transition">
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
                            {{ $session->results['score'] }} <span class="text-2xl font-normal opacity-75">/
                                {{ $session->exam->total_marks }}</span>
                        </div>

                        <div
                            class="inline-block px-4 py-1 mt-2 text-xs font-bold border rounded-full bg-white/20 backdrop-blur-sm border-white/30">
                            {{ $session->results['percentage'] }}% Secured
                        </div>
                    </div>

                    <div class="p-6 text-center">
                        <div class="mb-4">
                            <span class="block text-xs font-bold text-gray-400 uppercase">Status</span>
                            @if (($session->results['pass_or_fail'] ?? 'Failed') === 'Passed')
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
                                <div class="text-lg font-bold text-gray-800">
                                    {{ gmdate('H:i:s', $session->total_time_taken) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons & Share Section --}}
                <div class="grid gap-3">

                    {{-- SHARE BUTTON START --}}
                    <div x-data="{
                        openShare: false,
                        parentEmail: '',
                        instructorEmail: '',
                        selfCopy: false,
                        loading: false
                    }">

                        <button @click="openShare = true"
                            class="flex items-center justify-center w-full gap-2 py-3 font-bold text-white transition bg-indigo-600 shadow rounded-xl hover:bg-indigo-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z">
                                </path>
                            </svg>
                            Share Result via Email
                        </button>

                        <div x-show="openShare"
                            class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-60 backdrop-blur-sm"
                            x-cloak>
                            <div class="relative w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl"
                                @click.away="openShare = false">

                                <button @click="openShare = false"
                                    class="absolute text-xl font-bold text-gray-400 top-4 right-4 hover:text-gray-600">&times;</button>

                                <h3 class="mb-2 text-xl font-bold text-gray-800">Share Report Card</h3>
                                <p class="mb-5 text-sm text-gray-500">Enter emails to send a secure link of your
                                    detailed result.</p>

                                <div class="mb-6 space-y-4">
                                    <div>
                                        <label class="block mb-1 text-xs font-bold text-gray-700 uppercase">Parent's
                                            Email</label>
                                        <input type="email" x-model="parentEmail"
                                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition"
                                            placeholder="parent@example.com">
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-xs font-bold text-gray-700 uppercase">Instructor's
                                            Email (Optional)</label>
                                        <input type="email" x-model="instructorEmail"
                                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition"
                                            placeholder="teacher@example.com">
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="selfCopy" x-model="selfCopy"
                                            class="w-4 h-4 text-indigo-600 rounded cursor-pointer focus:ring-indigo-500">
                                        <label for="selfCopy"
                                            class="text-sm font-medium text-gray-700 cursor-pointer">Send a copy to me
                                            also</label>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                    <button @click="openShare = false"
                                        class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-lg transition">Cancel</button>
                                    <button
                                        @click="
                                            if(!parentEmail && !instructorEmail && !selfCopy) {
                                                Swal.fire('Warning', 'Please enter at least one email or check Send to me.', 'warning');
                                                return;
                                            }
                                            loading = true;
                                            fetch('{{ route('student.exam.share.send', $session->code) }}', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                body: JSON.stringify({
                                                    parent_email: parentEmail,
                                                    instructor_email: instructorEmail,
                                                    self_copy: selfCopy
                                                })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                loading = false;
                                                if(data.success) {
                                                    Swal.fire('Sent!', data.message, 'success');
                                                    openShare = false;
                                                    parentEmail = ''; instructorEmail = ''; selfCopy = false;
                                                }
                                                else { Swal.fire('Error', data.message, 'error'); }
                                            })
                                            .catch(err => {
                                                loading = false;
                                                console.error(err);
                                                Swal.fire('Error', 'Server error occurred. Please check logs.', 'error');
                                            });
                                        "
                                        class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-70 transition shadow-md"
                                        :disabled="loading">
                                        <span x-show="loading"
                                            class="w-4 h-4 border-2 border-white rounded-full border-t-transparent animate-spin"></span>
                                        <span x-text="loading ? 'Sending...' : 'Send Email'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- SHARE BUTTON END --}}

                    <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('student.dashboard') }}"
                        class="block w-full py-3 font-bold text-center text-gray-600 transition bg-white border-2 border-gray-200 hover:border-gray-400 hover:text-gray-800 rounded-xl">
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
                            <div class="mb-1 text-3xl font-bold text-blue-600">
                                {{ $session->results['total_questions'] }}</div>
                            <div class="text-xs font-bold text-blue-400 uppercase">Total Qs</div>
                        </div>

                        {{-- Attempted --}}
                        <div class="p-4 text-center border border-gray-200 stat-card bg-gray-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-gray-700">
                                {{ $session->results['answered_questions'] }}</div>
                            <div class="text-xs font-bold text-gray-400 uppercase">Attempted</div>
                        </div>

                        {{-- Correct --}}
                        <div class="p-4 text-center border border-green-100 stat-card bg-green-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-green-600">
                                {{ $session->results['correct_answered_questions'] }}</div>
                            <div class="text-xs font-bold text-green-500 uppercase">Correct</div>
                        </div>

                        {{-- Wrong --}}
                        <div class="p-4 text-center border border-red-100 stat-card bg-red-50 rounded-xl">
                            <div class="mb-1 text-3xl font-bold text-red-600">
                                {{ $session->results['wrong_answered_questions'] }}</div>
                            <div class="text-xs font-bold text-red-400 uppercase">Wrong</div>
                        </div>
                    </div>

                    {{-- Marks Breakdown --}}
                    <h3 class="mb-4 text-sm font-bold tracking-wide text-gray-800 uppercase">Marks Breakdown</h3>
                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-8 h-8 font-bold text-green-600 bg-green-100 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700">Marks Earned</span>
                            </div>
                            <span
                                class="text-lg font-bold text-green-600">+{{ $session->results['marks_earned'] }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-8 h-8 font-bold text-red-600 bg-red-100 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4"></path>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700">Negative Marking</span>
                            </div>
                            <span
                                class="text-lg font-bold text-red-600">-{{ $session->results['marks_deducted'] }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 mt-2 border border-blue-100 rounded-lg bg-blue-50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-8 h-8 font-bold text-white bg-blue-600 rounded-full">
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
