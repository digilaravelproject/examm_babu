-- Active: 1771832095693@@127.0.0.1@3306@bizgurukul
<!DOCTYPE html>
<html lang="en" class="h-full select-none">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} - Exam Babu Interface</title>

    {{-- CSS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- MathJax --}}
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)'],
                    ['$', '$']
                ]
            },
            startup: {
                typeset: false
            }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Roboto', sans-serif;
            user-select: none;
        }

        /* --- TCS iON Classic Palette --- */
        .btn-status {
            width: 40px;
            height: 35px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.1s;
            background: white;
            border: 1px solid #ccc;
            clip-path: polygon(0% 0%, 100% 0%, 100% 85%, 50% 100%, 0% 85%);
            margin-bottom: 4px;
        }

        .st-not-visited {
            background: #ffffff;
            color: #000;
            border-color: #ccc;
            clip-path: none;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        .st-not-answered {
            background: #E74C3C;
            color: #fff;
            border: none;
        }

        .st-answered {
            background: #27AE60;
            color: #fff;
            border: none;
        }

        .st-marked {
            background: #8E44AD;
            color: #fff;
            clip-path: none;
            border-radius: 50%;
        }

        .st-ans-marked {
            background: #8E44AD;
            color: #fff;
            clip-path: none;
            border-radius: 50%;
            position: relative;
        }

        .st-ans-marked::after {
            content: '✔';
            position: absolute;
            bottom: 0;
            right: -2px;
            font-size: 9px;
            background: #27AE60;
            color: white;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid white;
        }

        .active-q {
            box-shadow: 0 0 0 2px #3498db inset;
            border-color: #3498db;
            font-weight: bold;
        }

        /* Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        /* FIB Styling Fix */
        .fib-container input {
            border: none;
            border-bottom: 2px solid #3498db;
            outline: none;
            padding: 2px 8px;
            font-weight: bold;
            color: #2c3e50;
            background: #f8fafc;
            transition: all 0.2s;
            min-width: 100px;
        }

        .fib-container input:focus {
            border-bottom-color: #27ae60;
            background: #f0fdf4;
        }

        /* MTF Styles */
        .mtf-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: stretch;
        }

        .mtf-left-item {
            padding: 12px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .mtf-right-item {
            padding: 12px;
            background: #fff;
            border: 2px dashed #3498db;
            border-radius: 8px;
            cursor: move;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mtf-right-item:hover {
            background: #eff6ff;
        }

        /* Loader */
        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
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

        /* Language Separator */
        .lang-sep {
            display: flex;
            align-items: center;
            color: #94a3b8;
            margin: 20px 0;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .lang-sep::before,
        .lang-sep::after {
            content: '';
            flex: 1;
            border-bottom: 1px dashed #e2e8f0;
        }

        .lang-sep::before {
            margin-right: 10px;
        }

        .lang-sep::after {
            margin-left: 10px;
        }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden bg-gray-100" x-data="examEngine(@js($sections), {{ $remainingSeconds }}, '{{ $session->code }}')" x-init="init()"
    @contextmenu.prevent="return false;" @keydown.f12.prevent="return false;"
    @keydown.ctrl.shift.i.prevent="return false;">

    {{-- ========================================== --}}
    {{-- 1. FULL INSTRUCTIONS MODAL (Restore) --}}
    {{-- ========================================== --}}
    <div x-show="showInstructions" class="fixed inset-0 z-[100] bg-white overflow-y-auto" x-transition>
        <header class="sticky top-0 flex items-center h-16 px-6 text-white bg-blue-600 shadow-md">
            <h1 class="text-xl font-bold">Instructions - {{ $exam->title }}</h1>
        </header>

        <div class="max-w-5xl p-6 mx-auto md:p-10">
            <div class="flex items-center gap-6 mb-8">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name) }}&background=0D8ABC&color=fff"
                    class="w-20 h-20 border-4 border-gray-100 rounded-full shadow-sm">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="font-medium text-gray-500">System Candidate ID: <span
                            class="text-blue-600">{{ $session->code }}</span></p>
                </div>
            </div>

            <div
                class="p-5 mb-8 text-sm leading-relaxed text-yellow-800 border-l-4 border-yellow-500 rounded-r-lg bg-yellow-50">
                <strong class="block mb-1 text-lg">⚠️ Important Security Notice:</strong>
                Switching tabs, windows, or minimizing the browser is strictly prohibited. Your session will be
                <strong>terminated immediately</strong> after 3 warnings. Ensure you have a stable internet connection.
            </div>

            <div class="grid grid-cols-1 gap-8 mb-10 md:grid-cols-3">
                <div class="space-y-4 text-gray-700 md:col-span-2">
                    <h3 class="pb-2 text-xl font-bold text-gray-800 border-b">General Guidelines</h3>
                    <ul class="pl-5 space-y-2 list-disc">
                        <li>The clock will be set at the server. The countdown timer in the top right corner of screen
                            will display the remaining time available.</li>
                        <li>When the timer reaches zero, the examination will end by itself. You will not be required to
                            end or submit your examination.</li>
                        <li>The Question Palette displayed on the right side of screen will show the status of each
                            question using TCS iON symbols.</li>
                    </ul>
                </div>
                {{--
                <div class="p-6 border border-blue-200 shadow-sm rounded-xl bg-blue-50">
                    <h3 class="flex items-center gap-2 mb-4 font-bold text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c1.382 4.06 3.868 7.428 7.042 9.5">
                            </path>
                        </svg>
                        Language Preference
                    </h3>
                    <label class="block mb-2 text-xs font-bold text-gray-500 uppercase">Secondary Translation
                        Language</label>
                    <select x-model="secondaryLang"
                        class="w-full p-3 font-bold text-gray-800 transition bg-white border-2 border-blue-300 rounded-lg focus:ring-4 focus:ring-blue-100">
                        <option value="">-- No Translation --</option>
                        <option value="hi">Hindi (हिन्दी)</option>
                        <option value="mr">Marathi (मराठी)</option>
                    </select>
                    <p class="mt-3 text-[11px] text-blue-600 font-medium italic">* Questions will be available in
                        English and your chosen language.</p>
                </div> --}}
            </div>

            <div class="pt-6 border-t">
                <label
                    class="flex items-center gap-4 p-4 transition cursor-pointer select-none rounded-xl hover:bg-blue-50 group">
                    <input type="checkbox" x-model="agreed"
                        class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-lg font-bold text-gray-800 transition group-hover:text-blue-700">I have read and
                        understood the instructions. All computer hardware allotted to me is in proper working
                        condition.</span>
                </label>
            </div>

            <div class="flex justify-center mt-10">
                <button @click="startSequence()" :disabled="!agreed"
                    class="px-16 py-5 text-xl font-bold text-white transition transform bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    I am ready to begin
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. MAIN INTERFACE --}}
    {{-- ========================================== --}}
    <header class="h-16 bg-[#3498db] text-white flex justify-between items-center px-4 shadow-md z-50 shrink-0">
        <div class="flex items-center gap-3">
            <button @click="showPalette = !showPalette" class="p-2 rounded md:hidden hover:bg-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
            <div class="text-lg font-bold tracking-wide truncate max-w-[200px] md:max-w-md">{{ $exam->title }}</div>
        </div>

        <div class="flex items-center gap-6">
            <div class="text-right">
                <span class="text-[10px] text-blue-100 uppercase font-semibold block">Time Remaining</span>
                <span class="font-mono text-xl font-bold"
                    :class="timeRemaining < 300 ? 'text-yellow-300 animate-pulse' : 'text-white'"
                    x-text="formatTime(timeRemaining)"></span>
            </div>
            <button @click="submitExam()"
                class="px-5 py-2 text-sm font-bold transition transform bg-red-500 rounded shadow hover:bg-red-600 active:scale-95">Submit</button>
        </div>
    </header>

    <div class="relative flex flex-1 overflow-hidden">
        {{-- LEFT: Main Question Area --}}
        <main class="relative flex flex-col flex-1 w-full bg-white border-gray-300 md:border-r">
            {{-- Section Tabs --}}
            <div class="flex overflow-x-auto border-b border-gray-300 bg-gray-50">
                <template x-for="(sec, idx) in sectionsMeta" :key="sec.id">
                    <button @click="switchSection(idx)"
                        class="relative px-6 py-3 text-sm font-bold transition-colors border-r border-gray-300 whitespace-nowrap focus:outline-none"
                        :class="currSecIdx === idx ? 'bg-white text-blue-600 border-t-2 border-blue-500' :
                            'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                        <span x-text="sec.name"></span>
                    </button>
                </template>
            </div>

            {{-- Question Header --}}
            <div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
                <h2 class="text-lg font-bold text-red-600">Question No. <span x-text="currQIdx + 1"></span></h2>
                <div class="flex gap-2">
                    <span class="px-3 py-1 text-xs font-bold text-green-700 bg-green-100 rounded">Correct: +<span
                            x-text="currQ?.marks"></span></span>
                </div>
            </div>

            {{-- Question Content Scrollable --}}
            <div class="flex-1 p-6 overflow-y-auto" x-show="!loading && currQ">
                <div class="flex flex-col h-full gap-8 lg:flex-row">
                    {{-- Passage Pane --}}
                    <template x-if="currQ?.passage">
                        <div
                            class="lg:w-1/2 overflow-y-auto border border-gray-200 rounded-xl p-5 bg-gray-50 max-h-[40vh] lg:max-h-full">
                            <h3 class="pb-2 mb-4 font-bold text-blue-800 border-b" x-text="currQ.passage.title"></h3>
                            <div class="text-sm font-medium leading-relaxed prose max-w-none"
                                x-html="currQ.passage.body"></div>
                        </div>
                    </template>

                    {{-- Interaction Pane --}}
                    <div class="flex-1">
                        {{-- 1. Question Text (Dual Language Support) --}}
                        <div class="mb-8">
                            {{-- IMPORTANT: Use x-html to render text, but rely on window.updateFIB for inputs --}}
                            <div class="text-lg font-bold leading-relaxed text-gray-800" x-html="renderFIB(currQ)">
                            </div>

                            <template x-if="secondaryLang && currQ?.allow_translation">
                                <div class="mt-6">
                                    <div class="lang-sep"
                                        x-text="secondaryLang === 'hi' ? 'HINDI VERSION' : 'MARATHI VERSION'"></div>

                                    <template x-if="currQ.translated_text">
                                        <div class="text-lg font-bold leading-relaxed text-blue-700"
                                            x-html="renderFIB(currQ, true)"></div>
                                    </template>

                                    <template x-if="currQ.translated_text === null">
                                        <div class="text-sm italic text-gray-400 animate-pulse">Translating question...
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- 2. Interaction Based on Type --}}
                        <div class="space-y-4">
                            {{-- TYPE: MSA / TOF (Radio) --}}
                            <template x-if="['MSA', 'TOF'].includes(currQ.type_code)">
                                <div class="grid gap-3">
                                    <template x-for="(opt, oIdx) in currQ.options" :key="oIdx">
                                        <div @click="selectOption(oIdx)"
                                            class="flex items-center p-4 transition border-2 cursor-pointer rounded-xl hover:bg-gray-50"
                                            :class="currQ.selected_option === oIdx ? 'border-blue-500 bg-blue-50' :
                                                'border-gray-200'">
                                            <div class="flex items-center justify-center w-6 h-6 mr-4 border-2 rounded-full"
                                                :class="currQ.selected_option === oIdx ? 'border-blue-600 bg-blue-600' :
                                                    'border-gray-400'">
                                                <div class="w-2 h-2 bg-white rounded-full"
                                                    x-show="currQ.selected_option === oIdx"></div>
                                            </div>
                                            <div class="flex-1 font-medium text-gray-700" x-html="opt.option"></div>
                                            {{-- Translated Option (Show only if language is selected) --}}
                                            <template x-if="secondaryLang && opt.translated_option">
                                                <div class="mt-1 text-sm font-bold text-blue-700"
                                                    x-html="opt.translated_option"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE: MMA (Checkbox) --}}
                            <template x-if="['MMA', 'MMS'].includes(currQ.type_code)">
                                <div class="grid gap-3">
                                    <template x-for="(opt, oIdx) in currQ.options" :key="oIdx">
                                        <div @click="toggleMMA(oIdx)"
                                            class="flex items-center p-4 transition border-2 cursor-pointer rounded-xl hover:bg-gray-50"
                                            :class="isMMAChecked(oIdx) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                            <div class="flex items-center justify-center w-6 h-6 mr-4 border-2 rounded"
                                                :class="isMMAChecked(oIdx) ? 'border-blue-600 bg-blue-600' : 'border-gray-400'">
                                                <span class="text-xs text-white" x-show="isMMAChecked(oIdx)">✔</span>
                                            </div>
                                            <div class="flex-1 font-medium text-gray-700" x-html="opt.option"></div>
                                            {{-- Translated Option --}}
                                            <template x-if="secondaryLang && opt.translated_option">
                                                <div class="mt-1 text-sm font-bold text-blue-700"
                                                    x-html="opt.translated_option"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE: MTF (Drag and Drop) --}}
                            <template x-if="currQ.type_code === 'MTF'">
                                <div class="space-y-4">
                                    <div class="p-3 mb-2 text-xs font-bold text-blue-700 rounded bg-blue-50">
                                        Instructions: Drag items on the right to match the left items correctly.</div>
                                    <div class="space-y-2">
                                        <template x-for="(match, mIdx) in currQ.options.matches"
                                            :key="mIdx">
                                            <div class="mtf-grid">
                                                <div class="font-bold text-gray-600 mtf-left-item">
                                                    <span
                                                        class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-[10px] mr-3"
                                                        x-text="mIdx + 1"></span>
                                                    <div x-html="match.value"></div>
                                                </div>
                                                <div class="mtf-right-item" draggable="true"
                                                    @dragstart="mtfDragStart($event, mIdx)" @dragover.prevent=""
                                                    @drop="mtfDrop($event, mIdx)">
                                                    <div class="flex-1 font-medium text-gray-800"
                                                        x-html="currQ.mtfPairs[mIdx].value"></div>
                                                    <span class="text-gray-300">☰</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- TYPE: ORD (Ordering) --}}
                            <template x-if="currQ.type_code === 'ORD'">
                                <div class="space-y-2">
                                    <div class="p-3 mb-2 text-xs font-bold text-blue-700 rounded bg-blue-50">
                                        Instructions: Drag and drop to arrange the items in correct order.</div>
                                    <template x-for="(item, iIdx) in currQ.mtfPairs" :key="iIdx">
                                        <div class="flex items-center justify-between p-4 transition bg-white border-2 border-gray-200 cursor-move rounded-xl hover:border-blue-300"
                                            draggable="true" @dragstart="ordDragStart($event, iIdx)"
                                            @dragover.prevent="" @drop="ordDrop($event, iIdx)">
                                            <div class="flex items-center gap-4">
                                                <span
                                                    class="flex items-center justify-center w-8 h-8 font-bold text-blue-700 bg-blue-100 rounded-full"
                                                    x-text="iIdx + 1"></span>
                                                <div class="font-medium text-gray-800" x-html="item.value"></div>
                                            </div>
                                            <span class="text-gray-300">☰</span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE: SAQ (Short Answer) --}}
                            <template x-if="currQ.type_code === 'SAQ' || currQ.type_code === 'LAQ'">
                                <div class="mt-4">
                                    <label class="block mb-2 text-sm font-bold text-gray-700">Write your answer
                                        below:</label>
                                    <textarea class="w-full p-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                        rows="4" placeholder="Type your answer here..." x-model="currQ.selected_option"></textarea>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Controls --}}
            <footer class="flex items-center justify-between h-16 px-6 border-t border-gray-300 bg-gray-50">
                <div class="flex gap-3">
                    <button @click="markReview()"
                        class="px-6 py-2 font-bold text-white transition bg-purple-600 rounded shadow-md hover:bg-purple-700">Mark
                        & Next</button>
                    <button @click="clearResponse()"
                        class="px-6 py-2 font-bold text-gray-700 transition bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100">Clear
                        Response</button>
                </div>
                <button @click="saveNext()"
                    class="px-10 py-2 bg-[#27AE60] text-white font-bold rounded shadow-lg hover:bg-[#219150] transition transform active:scale-95 border-b-4 border-[#1e8449]">Save
                    & Next</button>
            </footer>
        </main>

        {{-- RIGHT: Palette (Restore Sidebar) --}}
        <aside
            class="fixed inset-0 z-50 flex flex-col w-full h-full transition-transform duration-300 bg-white md:relative md:w-80 md:translate-x-0 shrink-0"
            :class="showPalette ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">

            <div class="flex items-center gap-3 p-4 border-b border-gray-200 bg-blue-50">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name) }}"
                    class="w-12 h-12 border-2 border-white rounded-full shadow-sm">
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $user->first_name }}
                        {{ $user->last_name }}
                    </p>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Candidate</p>
                </div>
            </div>

            <div class="p-4 border-b bg-gray-50">
                <div class="grid grid-cols-2 gap-x-2 gap-y-3 text-[11px] font-bold text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 st-answered"></div> Answered
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 st-not-answered"></div> Not Answered
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 border st-not-visited"></div> Not Visited
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 st-marked"></div> Marked
                    </div>
                </div>
            </div>

            <div class="flex-1 p-4 overflow-y-auto">
                <h3 class="mb-4 text-xs font-bold tracking-widest text-gray-400 uppercase">Question Palette</h3>
                <div class="grid grid-cols-5 gap-2">
                    <template x-for="(q, idx) in currentSectionQs" :key="q.id">
                        <div @click="jumpTo(idx)" class="btn-status" :class="getStatusClass(q, idx)">
                            <span x-text="idx + 1"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 bg-gray-100 border-t">
                <button @click="submitExam()"
                    class="w-full bg-[#2980b9] hover:bg-[#2c3e50] text-white font-bold py-3 rounded-lg shadow-lg transition">SUBMIT
                    TEST</button>
            </div>
        </aside>
    </div>

    {{-- SCRIPTS --}}
    <script>
        function examEngine(sectionsMeta, duration, sessionCode) {
            return {
                sectionsMeta,
                loadedSections: {},
                currSecIdx: 0,
                currQIdx: 0,
                timeRemaining: duration,
                timer: null,
                qStartTime: 0,
                showInstructions: true,
                agreed: false,
                loading: false,
                secondaryLang: '',
                warnings: 0,
                started: false,
                showPalette: false,

                get currentSectionQs() {
                    return this.loadedSections[this.sectionsMeta[this.currSecIdx].id] || [];
                },
                get currQ() {
                    return this.currentSectionQs[this.currQIdx] || null;
                },

                init() {
                    // Make this available globally for window.updateFIB
                    window.examApp = this;
                    if (this.sectionsMeta.length > 0) {
                        this.secondaryLang = this.sectionsMeta[0].translation_language || '';
                    }
                    // Security
                    window.addEventListener("blur", () => {
                        if (this.started) this.violation();
                    });
                    document.addEventListener("visibilitychange", () => {
                        if (document.hidden && this.started) this.violation();
                    });
                    history.pushState(null, null, location.href);
                    window.onpopstate = () => history.go(1);
                },

                async startSequence() {
                    this.showInstructions = false;
                    await this.loadData(0);
                    this.startExamTimer();
                },

                startExamTimer() {
                    this.started = true;
                    this.qStartTime = Date.now();
                    this.timer = setInterval(() => {
                        if (this.timeRemaining > 0) this.timeRemaining--;
                        else this.submitExam(true);
                    }, 1000);
                },

                formatTime(s) {
                    const hrs = Math.floor(s / 3600);
                    const mins = Math.floor((s % 3600) / 60);
                    const secs = s % 60;
                    return [hrs, mins, secs].map(v => v.toString().padStart(2, '0')).join(':');
                },

                async loadData(idx) {
                    const secId = this.sectionsMeta[idx].id;
                    if (this.loadedSections[secId]) {
                        this.currSecIdx = idx;
                        this.currQIdx = 0;
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(`/student/exam/fetch-section/${sessionCode}/${secId}`);
                        const data = await res.json();

                        // Prepare Local State for Questions
                        this.loadedSections[secId] = data.questions.map(q => {
                            // Logic for FIB blanks
                            if (q.type_code === 'FIB') {
                                if (!q.selected_option || !Array.isArray(q.selected_option)) {
                                    const matchCount = (q.text.match(/##/g) || []).length / 2;
                                    q.selected_option = new Array(matchCount).fill('');
                                }
                            }

                            // Logic for MTF shuffle
                            if (q.type_code === 'MTF' && q.options.pairs) {
                                if (!q.selected_option) {
                                    q.mtfPairs = [...q.options.pairs].sort(() => Math.random() - 0.5);
                                    q.selected_option = q.mtfPairs.map(p => p.id);
                                } else {
                                    // Resume state
                                    const savedIds = q.selected_option;
                                    q.mtfPairs = savedIds.map(id => q.options.pairs.find(p => p.id == id));
                                }
                            }

                            // Logic for ORD shuffle
                            if (q.type_code === 'ORD') {
                                if (!q.selected_option) {
                                    q.mtfPairs = [...q.options].sort(() => Math.random() - 0.5);
                                    q.selected_option = q.mtfPairs.map(o => o.id);
                                } else {
                                    const savedIds = q.selected_option;
                                    q.mtfPairs = savedIds.map(id => q.options.find(o => o.id == id));
                                }
                            }
                            q.translated_text = null;
                            if (this.secondaryLang && q.allow_translation) {
                                this.translateQuestion(q);
                            }

                            return q;
                        });

                        this.currSecIdx = idx;
                        this.currQIdx = 0;
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                        this.renderMath();
                    }
                },

                async translateQuestion(q) {
                    if (!this.secondaryLang) return;
                    try {
                        const url =
                            `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(q.text)}`;
                        const res = await fetch(url);
                        const data = await res.json();
                        q.translated_text = data[0].map(x => x[0]).join('');
                    } catch (e) {
                        q.translated_text = q.text;
                    }
                    if (Array.isArray(q.options)) {
                        // Hum Promise.all use karenge taaki saare options ek saath translate ho jayein
                        await Promise.all(q.options.map(async (opt) => {
                            // Agar option text hai aur abhi tak translate nahi hua hai
                            if (opt.option && !opt.translated_option) {
                                try {
                                    const optUrl =
                                        `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(opt.option)}`;
                                    const optRes = await fetch(optUrl);
                                    const optData = await optRes.json();
                                    // Translated text ko naye variable 'translated_option' mein save karein
                                    opt.translated_option = optData[0].map(x => x[0]).join('');
                                } catch (e) {
                                    opt.translated_option = opt
                                    .option; // Error aaye to English hi rakh lo
                                }
                            }
                        }));
                    }
                    this.currQ = {
                        ...q
                    };
                },

                renderFIB(q, isTranslation = false) {
                    // Safe guard against null q
                    if (!q) return '';

                    let text = isTranslation ? q.translated_text : q.text;
                    if (q.type_code !== 'FIB' || !text) return text;

                    let i = 0;
                    return text.replace(/##(.*?)##/g, (match) => {
                        const val = q.selected_option[i] || '';
                        // FIXED: Pass `q.id` to identify question, `i` for index
                        const elId = `fib_input_${q.id}_${i}`;
                        const html = `<input type="text" id="${elId}" class="fib-container input"
                                        style="border: none; border-bottom: 2px solid #3498db; outline: none; padding: 2px 8px; font-weight: bold; color: #2c3e50; background: #f8fafc; min-width: 100px;"
                                        value="${val}"
                                        oninput="window.updateFIB(${q.id}, ${i}, this.value)"
                                        placeholder="Type here...">`;
                        i++;
                        return html;
                    });
                },

                selectOption(idx) {
                    this.currQ.selected_option = idx;
                },

                toggleMMA(idx) {
                    // Logic to handle array init if empty
                    if (!Array.isArray(this.currQ.selected_option)) {
                        this.currQ.selected_option = [];
                    }
                    const pos = this.currQ.selected_option.indexOf(idx);
                    if (pos === -1) {
                        this.currQ.selected_option.push(idx);
                    } else {
                        this.currQ.selected_option.splice(pos, 1);
                    }
                },

                isMMAChecked(idx) {
                    return Array.isArray(this.currQ.selected_option) && this.currQ.selected_option.includes(idx);
                },

                // MTF Drag & Drop Logic Fix (No undefined)
                mtfDragStart(e, idx) {
                    e.dataTransfer.setData('text/plain', idx);
                },
                mtfDrop(e, toIdx) {
                    const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
                    if (isNaN(fromIdx)) return;
                    const q = this.currQ;
                    const item = q.mtfPairs.splice(fromIdx, 1)[0];
                    q.mtfPairs.splice(toIdx, 0, item);
                    q.selected_option = q.mtfPairs.map(p => p.id);
                },

                // ORD Drag & Drop Logic
                ordDragStart(e, idx) {
                    e.dataTransfer.setData('text/plain', idx);
                },
                ordDrop(e, toIdx) {
                    const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
                    if (isNaN(fromIdx)) return;
                    const q = this.currQ;
                    const item = q.mtfPairs.splice(fromIdx, 1)[0];
                    q.mtfPairs.splice(toIdx, 0, item);
                    q.selected_option = q.mtfPairs.map(p => p.id);
                },

                async saveAnswer(statusOverride = null) {
                    const q = this.currQ;
                    const time = Math.round((Date.now() - this.qStartTime) / 1000);
                    this.qStartTime = Date.now();

                    // Status Logic
                    let status = statusOverride || 'visited';
                    if (!statusOverride) {
                        status = this.hasAnswered(q) ? 'answered' : 'not_answered';
                    }

                    q.status = status;

                    // Convert to Backend expected statuses
                    let backStatus = 'visited';
                    if (status === 'answered') backStatus = 'answered';
                    if (status === 'answered_mark_for_review') backStatus = 'answered_mark_for_review';
                    if (status === 'mark_for_review') backStatus = 'mark_for_review';

                    fetch(`/student/exam/save-answer/${sessionCode}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            question_id: q.id,
                            section_id: this.sectionsMeta[this.currSecIdx].id,
                            user_answer: q.selected_option,
                            status: backStatus,
                            time_taken: time,
                            total_time_taken: (duration - this.timeRemaining)
                        })
                    });
                },

                hasAnswered(q) {
                    const ans = q.selected_option;
                    if (ans === null || ans === undefined || ans === '') return false;
                    if (Array.isArray(ans)) {
                        if (q.type_code === 'FIB') return ans.some(v => v.trim() !== '');
                        return ans.length > 0;
                    }
                    return true;
                },

                markReview() {
                    const status = this.hasAnswered(this.currQ) ? 'answered_mark_for_review' : 'mark_for_review';
                    this.saveAnswer(status);
                    this.next();
                },

                saveNext() {
                    this.saveAnswer();
                    this.next();
                },

                clearResponse() {
                    if (Array.isArray(this.currQ.selected_option)) {
                        this.currQ.selected_option = this.currQ.type_code === 'FIB' ? new Array(this.currQ.selected_option
                            .length).fill('') : [];
                    } else {
                        this.currQ.selected_option = null;
                    }
                    this.currQ.status = 'visited';
                    this.saveAnswer('visited');
                },

                next() {
                    if (this.currQIdx < this.currentSectionQs.length - 1) {
                        this.jumpTo(this.currQIdx + 1);
                    } else if (this.currSecIdx < this.sectionsMeta.length - 1) {
                        this.switchSection(this.currSecIdx + 1);
                    }
                },

                jumpTo(idx) {
                    this.currQIdx = idx;
                    this.qStartTime = Date.now();
                    this.renderMath();
                },
                switchSection(idx) {
                    this.secondaryLang = this.sectionsMeta[idx].translation_language || '';
                    this.loadData(idx);
                },

                getStatusClass(q, idx) {
                    let c = (idx === this.currQIdx) ? 'active-q ' : '';
                    if (!q.status || q.status === 'visited') return c + 'st-not-visited';
                    if (q.status === 'answered') return c + 'st-answered';
                    if (q.status === 'not_answered') return c + 'st-not-answered';
                    if (q.status === 'mark_for_review') return c + 'st-marked';
                    if (q.status === 'answered_mark_for_review') return c + 'st-ans-marked';
                    return c;
                },

                violation() {
                    this.warnings++;
                    if (this.warnings >= 3) {
                        fetch(`/student/exam/terminate/${sessionCode}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            })
                            .then(r => r.json()).then(d => window.location.href = d.redirect);
                    } else {
                        Swal.fire({
                            title: `Warning ${this.warnings}/3`,
                            text: "Malpractice detected! Do not leave the exam window.",
                            icon: "warning",
                            confirmButtonColor: "#d33"
                        });
                    }
                },

                submitExam(auto = false) {
                    const doSubmit = () => {
                        Swal.fire({
                            title: 'Submitting...',
                            didOpen: () => Swal.showLoading()
                        });
                        fetch(`/student/exam/finish/${sessionCode}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json()).then(d => {
                                if (d.redirect) window.location.href = d.redirect;
                            });
                    };
                    if (auto) doSubmit();
                    else Swal.fire({
                        title: 'Finish Exam?',
                        text: 'Are you sure you want to submit your test?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit',
                        confirmButtonColor: '#27ae60'
                    }).then(r => {
                        if (r.isConfirmed) doSubmit();
                    });
                },

                renderMath() {
                    this.$nextTick(() => {
                        if (window.MathJax) window.MathJax.typesetPromise();
                    });
                }
            };
        }

        // Global FIB Input handler to prevent Focus Loss
        // UPDATED: Finds the specific question in memory and updates it directly
        window.updateFIB = (qId, index, value) => {
            if (!window.examApp) return;
            const app = window.examApp;

            // Search all sections to find the question object
            let foundQ = null;
            // Optimistic check for current question first
            if (app.currQ && app.currQ.id == qId) {
                foundQ = app.currQ;
            } else {
                for (const secId in app.loadedSections) {
                    const q = app.loadedSections[secId].find(q => q.id == qId);
                    if (q) {
                        foundQ = q;
                        break;
                    }
                }
            }

            if (foundQ) {
                // 1. Identify the Element ID
                const elId = `fib_input_${qId}_${index}`;
                const currentEl = document.getElementById(elId);
                const selectionStart = currentEl ? currentEl.selectionStart : 0;
                const selectionEnd = currentEl ? currentEl.selectionEnd : 0;

                // 2. Update Data (Triggers Alpine Re-render via x-html)
                if (!Array.isArray(foundQ.selected_option)) foundQ.selected_option = [];
                foundQ.selected_option[index] = value;

                // 3. Restore Focus after DOM updates
                app.$nextTick(() => {
                    const newEl = document.getElementById(elId);
                    if (newEl) {
                        newEl.focus();
                        newEl.setSelectionRange(selectionStart, selectionEnd);
                    }
                });
            }
        };
    </script>
</body>

</html>
