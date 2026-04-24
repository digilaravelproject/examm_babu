<!DOCTYPE html>
<html lang="en" class="h-full select-none">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} - Exam Babu Interface</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

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
    @contextmenu.prevent @keydown.f12.prevent @keydown.ctrl.shift.i.prevent>

    <!-- INSTRUCTIONS MODAL -->
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

    <!-- MAIN INTERFACE -->
    <header class="h-16 bg-[#3498db] text-white flex justify-between items-center px-4 shadow-md z-50 shrink-0">
        <div class="text-lg font-bold tracking-wide truncate max-w-md">{{ $exam->title }}</div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <span class="text-[10px] text-blue-100 uppercase font-semibold block">Time Remaining</span>
                <span class="font-mono text-xl font-bold"
                    :class="timeRemaining < 300 ? 'text-yellow-300 animate-pulse' : 'text-white'"
                    x-text="formatTime(timeRemaining)"></span>
            </div>
            <button @click="submitExam()"
                class="px-5 py-2 text-sm font-bold bg-red-500 rounded shadow hover:bg-red-600 active:scale-95">Submit</button>
        </div>
    </header>

    <div class="relative flex flex-1 overflow-hidden">
        <!-- QUESTION AREA -->
        <main class="relative flex flex-col flex-1 w-full bg-white border-gray-300 md:border-r">
            <!-- Section Tabs -->
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

            <!-- Question Header -->
            <div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
                <h2 class="text-lg font-bold text-red-600">Question No. <span x-text="currQIdx+1"></span></h2>
                <div class="flex gap-2">
                    <span class="px-3 py-1 text-xs font-bold text-green-700 bg-green-100 rounded">Correct: +<span
                            x-text="currQ?.marks"></span></span>
                </div>
            </div>

            <!-- Skeleton Loader -->
            <div class="flex-1 p-6 overflow-y-auto" x-show="loading || isInitialLoading" x-cloak>
                <div class="animate-pulse space-y-8">
                    <!-- Question Text Skeleton -->
                    <div class="space-y-3">
                        <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-6 bg-gray-200 rounded w-1/2"></div>
                    </div>
                    
                    <!-- Options Skeleton -->
                    <div class="space-y-4">
                        <template x-for="i in [1,2,3,4]">
                            <div class="flex items-center p-4 border-2 border-gray-100 rounded-xl">
                                <div class="w-6 h-6 bg-gray-200 rounded-full mr-4"></div>
                                <div class="h-4 bg-gray-200 rounded flex-1"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Question Content -->
            <div class="flex-1 p-6 overflow-y-auto" x-show="!loading && !isInitialLoading && currQ" x-cloak>
                <div class="flex flex-col h-full gap-8 lg:flex-row">
                    <!-- Passage -->
                    <template x-if="currQ?.passage">
                        <div
                            class="lg:w-1/2 overflow-y-auto border border-gray-200 rounded-xl p-5 bg-gray-50 max-h-[40vh] lg:max-h-full">
                            <h3 class="pb-2 mb-4 font-bold text-blue-800 border-b" x-text="currQ.passage.title"></h3>
                            <div class="text-sm font-medium leading-relaxed prose max-w-none"
                                x-html="currQ.passage.body"></div>
                        </div>
                    </template>

                    <!-- Interaction Pane -->
                    <div class="flex-1">
                        <!-- Question Text -->
                        <div class="mb-8">
                            <div class="text-lg font-bold leading-relaxed text-gray-800" x-html="renderFIB(currQ)">
                            </div>

                            <!-- Translation -->
                            <template x-if="secondaryLang && currQ?.allow_translation && currQ?.translated_text">
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                                    <div class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-2" x-text="secondaryLang === 'hi' ? 'Hindi Translation' : 'Translation'"></div>
                                    <div class="text-lg font-bold leading-relaxed text-blue-700" x-html="renderFIB(currQ, true)"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Options -->
                        <div class="space-y-4">
                            <template x-if="['MSA','TOF'].includes(currQ.type_code)">
                                <div class="grid gap-3">
                                    <template x-for="(opt,oIdx) in currQ.options" :key="oIdx">
                                        <div @click="selectOption(oIdx)"
                                            class="flex items-start p-4 transition border-2 cursor-pointer rounded-xl hover:bg-gray-50"
                                            :class="(currQ.selected_option == oIdx) ? 'border-blue-500 bg-blue-50' :
                                                'border-gray-200'">
                                            <div class="flex items-center justify-center w-6 h-6 mr-3 border-2 rounded-full mt-0.5 shrink-0"
                                                :class="(currQ.selected_option == oIdx) ? 'border-blue-600 bg-blue-600' :
                                                    'border-gray-400'">
                                                <div class="w-2 h-2 bg-white rounded-full"
                                                    x-show="currQ.selected_option == oIdx"></div>
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <div x-html="typeof opt === 'object' ? opt.option : opt" class="font-medium text-gray-700"></div>
                                                <template x-if="secondaryLang && opt.translated_option">
                                                    <div class="mt-1 text-sm font-bold text-blue-700 leading-relaxed"
                                                        x-html="opt.translated_option"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE: MMA (Checkbox) --}}
                            <template x-if="['MMA', 'MMS'].includes(currQ.type_code)">
                                <div class="grid gap-3">
                                    <template x-for="(opt, oIdx) in currQ.options" :key="oIdx">
                                        <div @click="toggleMMA(oIdx)"
                                            class="flex items-start p-4 transition border-2 cursor-pointer rounded-xl hover:bg-gray-50"
                                            :class="isMMAChecked(oIdx) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                            <div class="flex items-center justify-center w-6 h-6 mr-4 border-2 rounded mt-0.5 shrink-0"
                                                :class="isMMAChecked(oIdx) ? 'border-blue-600 bg-blue-600' : 'border-gray-400'">
                                                <span class="text-xs text-white" x-show="isMMAChecked(oIdx)">✔</span>
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <div class="font-medium text-gray-700" x-html="typeof opt === 'object' ? opt.option : opt"></div>
                                                <template x-if="secondaryLang && opt.translated_option">
                                                    <div class="mt-1 text-sm font-bold text-blue-700 leading-relaxed"
                                                        x-html="opt.translated_option"></div>
                                                </template>
                                            </div>
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
                                                    <div class="flex-1">
                                                        <div x-html="match.value"></div>
                                                        <template x-if="secondaryLang && match.translated_value">
                                                            <div class="mt-1 text-xs font-bold text-blue-600"
                                                                x-html="match.translated_value"></div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="mtf-right-item" draggable="true"
                                                    @dragstart="mtfDragStart($event, mIdx)" @dragover.prevent=""
                                                    @drop="mtfDrop($event, mIdx)">
                                                    <div class="flex-1">
                                                        <div class="font-medium text-gray-800"
                                                            x-html="currQ.mtfPairs[mIdx].value"></div>
                                                        <template x-if="secondaryLang && currQ.mtfPairs[mIdx].translated_value">
                                                            <div class="mt-1 text-xs font-bold text-blue-600"
                                                                x-html="currQ.mtfPairs[mIdx].translated_value"></div>
                                                        </template>
                                                    </div>
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
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-800" x-html="item.value"></div>
                                                    <template x-if="secondaryLang && item.translated_value">
                                                        <div class="mt-1 text-xs font-bold text-blue-600"
                                                            x-html="item.translated_value"></div>
                                                    </template>
                                                </div>
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

            <!-- Footer Controls -->
            <footer class="flex items-center justify-between h-16 px-6 border-t border-gray-300 bg-gray-50">
                <div class="flex gap-3">
                    <button @click="markReview()"
                        class="px-6 py-2 font-bold text-white bg-purple-600 rounded shadow-md hover:bg-purple-700">Mark
                        & Next</button>
                    <button @click="clearResponse()"
                        class="px-6 py-2 font-bold text-gray-700 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100">Clear
                        Response</button>
                </div>
                <button @click="saveNext()"
                    class="px-10 py-2 bg-[#27AE60] text-white font-bold rounded shadow-lg hover:bg-[#219150] border-b-4 border-[#1e8449] active:scale-95">Save
                    & Next</button>
            </footer>
        </main>

        <!-- Sidebar / Palette -->
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

    <!-- Scripts -->
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
                isInitialLoading: false,

                // Debounce store for FIB
                fibDebounceTimers: {},

                get currentSectionQs() {
                    return this.loadedSections[this.sectionsMeta[this.currSecIdx].id] || [];
                },
                get currQ() {
                    return this.currentSectionQs[this.currQIdx] || null;
                },

                init() {
                    window.examApp = this;
                    if (this.sectionsMeta.length > 0) {
                        this.secondaryLang = this.sectionsMeta[0].translation_language || '';
                    }

                    // Lightweight security
                    window.addEventListener("blur", () => {
                        if (this.started) this.violation();
                    });
                    document.addEventListener("visibilitychange", () => {
                        if (document.hidden && this.started) this.violation();
                    });

                    // Prevent copy/paste/cut
                    document.addEventListener("copy", e => e.preventDefault());
                    document.addEventListener("paste", e => e.preventDefault());
                    document.addEventListener("cut", e => e.preventDefault());

                    history.pushState(null, null, location.href);
                    window.onpopstate = () => history.go(1);
                },

                async startSequence() {
                    this.showInstructions = false;
                    this.isInitialLoading = true;
                    
                    await this.loadData(0);
                    
                    // Critical: Ensure the first question's translation is ready if applicable
                    if (this.currQ && this.secondaryLang && this.currQ.allow_translation && !this.currQ.translated_text) {
                        await this.translateQuestion(this.currQ);
                    }
                    
                    this.isInitialLoading = false;
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

                        this.loadedSections[secId] = data.questions.map(q => {
                            if (q.type_code === 'FIB') {
                                if (!q.selected_option || !Array.isArray(q.selected_option)) {
                                    const matchCount = (q.text.match(/##/g) || []).length / 2;
                                    q.selected_option = new Array(matchCount).fill('');
                                }
                            }

                            if (q.type_code === 'MTF' && q.options.pairs) {
                                if (!q.selected_option) {
                                    q.mtfPairs = [...q.options.pairs].sort(() => Math.random() - 0.5);
                                    q.selected_option = q.mtfPairs.map(p => p.id);
                                } else {
                                    const savedIds = q.selected_option;
                                    q.mtfPairs = savedIds.map(id => q.options.pairs.find(p => p.id == id));
                                }
                            }

                            if (q.type_code === 'ORD') {
                                if (!q.selected_option) {
                                    q.mtfPairs = [...q.options].sort(() => Math.random() - 0.5);
                                    q.selected_option = q.mtfPairs.map(o => o.id);
                                } else {
                                    const savedIds = q.selected_option;
                                    q.mtfPairs = savedIds.map(id => q.options.find(o => o.id == id));
                                }
                            }

                            // Initialize translation state explicitly for perfect Alpine.js reactivity
                            q.translated_text = '';
                            if (q.type_code === 'MTF') {
                                if (q.options.matches) q.options.matches.forEach(m => m.translated_value = '');
                                if (q.options.pairs) q.options.pairs.forEach(p => p.translated_value = '');
                            }
                            if (q.type_code === 'ORD' && Array.isArray(q.options)) {
                                q.options.forEach(o => o.translated_value = '');
                            }
                            if (Array.isArray(q.options)) {
                                q.options.forEach(opt => {
                                    if (typeof opt === 'object') opt.translated_option = '';
                                });
                            }

                            if (this.secondaryLang && q.allow_translation) {
                                this.translateQuestion(q);
                            }
                            return q;
                        });

                        this.currSecIdx = idx;
                        this.currQIdx = 0;

                        // Ensure the first question is translated if needed
                        if (this.currQ && this.secondaryLang && this.currQ.allow_translation && !this.currQ.translated_text) {
                            await this.translateQuestion(this.currQ);
                        }
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
                    if (q.type_code === 'MTF' && q.options.matches && q.options.pairs) {
                        // Translate MTF Left Items (matches)
                        await Promise.all(q.options.matches.map(async (m) => {
                            if (m.value && !m.translated_value) {
                                try {
                                    const optUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(m.value)}`;
                                    const res = await fetch(optUrl);
                                    const data = await res.json();
                                    m.translated_value = data[0].map(x => x[0]).join('');
                                } catch (e) { m.translated_value = m.value; }
                            }
                        }));
                        // Translate MTF Right Items (pairs)
                        await Promise.all(q.options.pairs.map(async (p) => {
                            if (p.value && !p.translated_value) {
                                try {
                                    const optUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(p.value)}`;
                                    const res = await fetch(optUrl);
                                    const data = await res.json();
                                    p.translated_value = data[0].map(x => x[0]).join('');
                                } catch (e) { p.translated_value = p.value; }
                            }
                        }));
                    } else if (q.type_code === 'ORD' && Array.isArray(q.options)) {
                        await Promise.all(q.options.map(async (o) => {
                            if (o.value && !o.translated_value) {
                                try {
                                    const optUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(o.value)}`;
                                    const res = await fetch(optUrl);
                                    const data = await res.json();
                                    o.translated_value = data[0].map(x => x[0]).join('');
                                } catch (e) { o.translated_value = o.value; }
                            }
                        }));
                    } else if (Array.isArray(q.options)) {
                        await Promise.all(q.options.map(async (opt) => {
                            if (opt.option && !opt.translated_option) {
                                try {
                                    const optUrl =
                                        `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${this.secondaryLang}&dt=t&q=${encodeURIComponent(opt.option)}`;
                                    const optRes = await fetch(optUrl);
                                    const optData = await optRes.json();
                                    opt.translated_option = optData[0].map(x => x[0]).join('');
                                } catch (e) {
                                    opt.translated_option = opt.option;
                                }
                            }
                        }));
                    }
                    // Trigger Alpine reactivity by refreshing the object reference in the pool if needed
                    // Since currQ is a getter, we just need to ensure the underlying object is updated.
                    this.renderMath();
                },

                renderFIB(q, isTranslation = false) {
                    if (!q) return '';
                    let text = isTranslation ? q.translated_text : q.text;
                    if (q.type_code !== 'FIB' || !text) return text;

                    let i = 0;
                    return text.replace(/##(.*?)##/g, (match) => {
                        const val = q.selected_option[i] || '';
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
                    if (!Array.isArray(this.currQ.selected_option)) this.currQ.selected_option = [];
                    const pos = this.currQ.selected_option.indexOf(idx);
                    pos === -1 ? this.currQ.selected_option.push(idx) : this.currQ.selected_option.splice(pos, 1);
                },
                isMMAChecked(idx) {
                    if (!this.currQ || !Array.isArray(this.currQ.selected_option)) return false;
                    return this.currQ.selected_option.some(v => v == idx);
                },

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
                    let status = statusOverride || (this.hasAnswered(q) ? 'answered' : 'not_answered');
                    q.status = status;

                    let backStatus = status;
                    await fetch(`/student/exam/save-answer/${sessionCode}`, {
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
                    if (Array.isArray(ans)) return q.type_code === 'FIB' ? ans.some(v => v.trim() !== '') : ans.length > 0;
                    return true;
                },

                markReview() {
                    this.saveAnswer(this.hasAnswered(this.currQ) ? 'answered_mark_for_review' : 'mark_for_review').then(
                    () => this.next());
                },
                saveNext() {
                    this.saveAnswer().then(() => this.next());
                },
                clearResponse() {
                    if (Array.isArray(this.currQ.selected_option)) this.currQ.selected_option = this.currQ.type_code ===
                        'FIB' ? new Array(this.currQ.selected_option.length).fill('') : [];
                    else this.currQ.selected_option = null;
                    this.currQ.status = 'visited';
                    this.saveAnswer('visited');
                },

                next() {
                    this.currQIdx < this.currentSectionQs.length - 1 ? this.jumpTo(this.currQIdx + 1) : (this.currSecIdx <
                        this.sectionsMeta.length - 1 ? this.switchSection(this.currSecIdx + 1) : null);
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
                        }).then(r => r.json()).then(d => {
                            if (d.redirect) window.location.href = d.redirect
                        })
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
                        if (r.isConfirmed) doSubmit()
                    });
                },

                renderMath() {
                    this.$nextTick(() => {
                        if (window.MathJax) window.MathJax.typesetPromise();
                    })
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
