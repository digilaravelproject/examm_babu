<!DOCTYPE html>
<html lang="en" class="h-full select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} - Exam Babu</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    {{-- Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- MathJax --}}
    <script>
        window.MathJax = {
            tex: { inlineMath: [['\\(', '\\)'], ['$', '$']] },
            startup: { typeset: false }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Roboto', sans-serif; user-select: none; }

        /* --- TCS iON Palette Styles --- */
        .btn-status {
            width: 40px; height: 35px; font-size: 14px; font-weight: 500;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.1s; background: white; border: 1px solid #ccc;
            clip-path: polygon(0% 0%, 100% 0%, 100% 85%, 50% 100%, 0% 85%); /* Iconic Shape */
            margin-bottom: 4px;
        }

        /* Status Colors */
        .st-not-visited { background: #ffffff; color: #000; border-color: #ccc; clip-path: none; border-radius: 4px; border: 1px solid #e5e7eb; }
        .st-not-answered { background: #E74C3C; color: #fff; border: none; }
        .st-answered { background: #27AE60; color: #fff; border: none; }
        .st-marked { background: #8E44AD; color: #fff; clip-path: none; border-radius: 50%; }
        .st-ans-marked { background: #8E44AD; color: #fff; clip-path: none; border-radius: 50%; position: relative; }
        .st-ans-marked::after {
            content: '✔'; position: absolute; bottom: 0; right: -2px;
            font-size: 9px; background: #27AE60; color: white;
            width: 14px; height: 14px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; border: 1px solid white;
        }

        /* Active Question Highlighting */
        .active-q { box-shadow: 0 0 0 2px #3498db inset; border-color: #3498db; font-weight: bold; }

        /* Split Layout & Scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Loader */
        .loader {
            border: 4px solid #f3f3f3; border-radius: 50%; border-top: 4px solid #3498db;
            width: 40px; height: 40px; animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Image Handling */
        img { max-width: 100%; height: auto; border-radius: 4px; border: 1px solid #eee; margin-top: 5px; display: block; }

        /* Dual Language Separator */
        .lang-separator {
            display: flex; align-items: center; color: #9ca3af; margin: 15px 0; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
        }
        .lang-separator::before, .lang-separator::after { content: ''; flex: 1; border-bottom: 1px dashed #d1d5db; }
        .lang-separator::before { margin-right: 10px; } .lang-separator::after { margin-left: 10px; }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden bg-gray-100"
      x-data="examEngine(
          @js($sections),
          {{ $remainingSeconds }},
          '{{ $session->code }}',
          '{{ route('student.exam.fetch_section', ['sessionCode' => $session->code, 'sectionId' => 'SECTION_ID']) }}',
          '{{ route('student.exam.save_answer', $session->code) }}',
          '{{ route('student.exam.terminate', $session->code) }}',
          '{{ route('student.exam.finish', $session->code) }}'
      )"
      x-init="init()"
      @contextmenu.prevent="return false;"
      @keydown.f12.prevent="return false;"
      @keydown.ctrl.shift.i.prevent="return false;"
      @keydown.ctrl.u.prevent="return false;">

    {{-- ========================================== --}}
    {{-- 1. INSTRUCTIONS MODAL (Start Screen)       --}}
    {{-- ========================================== --}}
    <div x-show="showInstructions" class="fixed inset-0 z-[100] bg-white overflow-y-auto">
        <header class="sticky top-0 flex items-center h-16 px-6 text-white bg-blue-600 shadow-md">
            <h1 class="text-xl font-bold">Instructions - {{ $exam->title }}</h1>
        </header>

        <div class="max-w-5xl p-6 mx-auto md:p-10">
            <div class="flex items-center gap-4 mb-6">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=0D8ABC&color=fff" class="w-16 h-16 border-4 border-gray-200 rounded-full">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="text-gray-500">Please read the instructions carefully before starting.</p>
                </div>
            </div>

            <div class="p-4 mb-6 text-sm text-yellow-800 border-l-4 border-yellow-500 bg-yellow-50">
                <strong>Strict Warning:</strong> Switching tabs or windows is prohibited.
                You will be <strong>disqualified</strong> immediately after 3 warnings.
            </div>

            <div class="p-5 mb-6 border border-blue-200 rounded-lg bg-blue-50">
                <h3 class="pb-2 mb-3 font-bold text-gray-800 border-b border-blue-200">Language Preference</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">Primary Language</label>
                        <select class="w-full p-2 text-gray-600 bg-gray-100 border border-gray-300 rounded cursor-not-allowed" disabled>
                            <option>English</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">Secondary Language (Optional)</label>
                        <select x-model="secondaryLang" class="w-full p-2 font-bold text-gray-800 bg-white border border-blue-400 rounded shadow-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Language --</option>
                            <option value="hi">Hindi</option>
                            <option value="mr">Marathi</option>
                            <option value="bn">Bengali</option>
                            <option value="gu">Gujarati</option>
                            <option value="ta">Tamil</option>
                        </select>
                        <p class="text-[10px] text-blue-600 mt-1" x-show="secondaryLang">
                            * Questions will be translated to <span x-text="secondaryLang.toUpperCase()"></span> automatically.
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-6 border-t">
                <label class="flex items-center gap-3 p-3 transition rounded-lg cursor-pointer select-none hover:bg-gray-50">
                    <input type="checkbox" x-model="agreed" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="font-bold text-gray-800">I have read the instructions and agree to the terms.</span>
                </label>
            </div>

            <div class="flex justify-center mt-8">
                <button @click="startSequence()" :disabled="!agreed"
                    class="px-12 py-4 text-lg font-bold text-white transition transform bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    I am ready to begin
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. PREPARING SCREEN (Translation Loader)   --}}
    {{-- ========================================== --}}
    <div x-show="isPreparing" class="fixed inset-0 z-[90] bg-white flex flex-col items-center justify-center" x-cloak>
        <div class="mb-4 loader"></div>
        <h2 class="text-xl font-bold text-gray-800">Preparing Exam Environment...</h2>
        <p class="mt-2 text-sm font-medium text-gray-500">Translating Content... <span x-text="progress + '%'"></span></p>
        <div class="w-64 h-2 mt-4 overflow-hidden bg-gray-200 rounded">
            <div class="h-full transition-all duration-300 bg-blue-500" :style="'width: '+progress+'%'"></div>
        </div>
        <p class="mt-2 text-xs text-gray-400">Do not refresh the page.</p>
    </div>

    {{-- ========================================== --}}
    {{-- 3. MAIN EXAM INTERFACE                     --}}
    {{-- ========================================== --}}

    {{-- Header --}}
    <header class="h-16 bg-[#3498db] text-white flex justify-between items-center px-4 shadow-md z-50 shrink-0" x-show="started" x-cloak>
        <div class="text-lg font-bold tracking-wide truncate">{{ $exam->title }}</div>

        <div class="flex items-center gap-6">
            <div class="flex flex-col items-end min-w-[80px]">
                <span class="text-[10px] text-blue-100 uppercase font-semibold">Time Left</span>
                <span class="font-mono text-xl font-bold" :class="timeRemaining < 300 ? 'text-yellow-300 animate-pulse' : 'text-white'" x-text="formatTime(timeRemaining)"></span>
            </div>

            <button @click="submitExam()" class="px-4 py-2 text-xs font-bold text-white transition bg-red-500 border border-red-600 rounded shadow-md hover:bg-red-600 active:scale-95">
                Submit Exam
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden" x-show="started" x-cloak>

        {{-- LEFT: Main Question Area --}}
        <main class="relative flex flex-col flex-1 bg-white border-r border-gray-300">

            {{-- Section Tabs --}}
            <div class="flex overflow-x-auto border-b border-gray-300 bg-gray-50 no-scrollbar">
                <template x-for="(sec, idx) in sectionsMeta" :key="sec.id">
                    <button @click="switchSection(idx)"
                        class="relative px-5 py-3 text-sm font-bold transition-colors border-r border-gray-300 whitespace-nowrap focus:outline-none"
                        :class="currSecIdx === idx ? 'bg-[#3498db] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        <span x-text="sec.name"></span>
                    </button>
                </template>
            </div>

            {{-- Question Header --}}
            <div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 shadow-sm">
                <h2 class="text-lg font-bold text-red-600">Question No. <span x-text="currQIdx + 1"></span></h2>
                <div class="flex gap-2 text-xs font-bold text-gray-600">
                    <span class="px-2 py-1 text-green-700 border border-green-200 rounded bg-green-50">+<span x-text="currQ?.marks"></span></span>
                    <span class="px-2 py-1 text-red-700 border border-red-200 rounded bg-red-50">-<span x-text="currQ?.negative"></span></span>
                </div>
            </div>

            {{-- Question Content Scrollable --}}
            <div class="flex-1 p-6 overflow-y-auto bg-white" x-show="!loading && currQ">
                {{-- Flex Container for Split View (Passage) --}}
                <div class="flex h-full gap-6" :class="{'flex-row': currQ.passage, 'flex-col': !currQ.passage}">

                    {{-- Passage Pane --}}
                    <template x-if="currQ.passage">
                        <div class="w-1/2 pr-2 overflow-y-auto border-r border-gray-200">
                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="pb-2 mb-3 font-bold text-blue-800 border-b" x-text="currQ.passage.title"></h3>
                                <div class="text-sm font-medium leading-relaxed prose text-gray-800" x-html="currQ.passage.body"></div>
                            </div>
                        </div>
                    </template>

                    {{-- Question Pane --}}
                    <div class="flex-1 overflow-y-auto" :class="{'pl-2': currQ.passage}">

                        {{-- 1. QUESTION TEXT (Dual Lang) --}}
                        <div class="mb-6">
                            {{-- English (Always) --}}
                            <div class="text-lg font-medium leading-relaxed text-gray-800" x-html="currQ.text['en']"></div>

                            {{-- Secondary Language (If Selected) --}}
                            <template x-if="secondaryLang && currQ.text[secondaryLang]">
                                <div class="mt-4">
                                    <div class="lang-separator" x-text="secondaryLang"></div>
                                    <div class="text-lg font-medium text-[#0777be] leading-relaxed" x-html="currQ.text[secondaryLang]"></div>
                                </div>
                            </template>
                        </div>

                        {{-- 2. OPTIONS (Based on Question Type) --}}
                        <div class="space-y-4">

                            {{-- TYPE A: MSA / TOF (Radio Buttons - Existing) --}}
                            <template x-if="currQ.type_code === 'MSA' || currQ.type_code === 'TOF'">
                                <div class="space-y-3">
                                    <template x-for="(opt, idx) in currQ.options['en']" :key="idx">
                                        <div @click="selectOption(idx)"
                                            class="relative flex items-start p-4 transition-all border-2 cursor-pointer select-none rounded-xl group"
                                            :class="currQ.selected_option === idx ? 'border-[#3498db] bg-blue-50 shadow-md' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'">

                                            <div class="flex items-center justify-center w-6 h-6 mt-1 mr-4 border-2 rounded-full shrink-0"
                                                :class="currQ.selected_option === idx ? 'border-blue-600 bg-blue-600' : 'border-gray-400 group-hover:border-blue-400'">
                                                <div class="w-2.5 h-2.5 bg-white rounded-full transition-transform duration-200"
                                                    :class="currQ.selected_option === idx ? 'scale-100' : 'scale-0'"></div>
                                            </div>

                                            <div class="flex-1">
                                                <div class="text-base font-medium text-gray-700">
                                                    <template x-if="opt.image">
                                                        <img :src="opt.image" class="mb-2 border rounded shadow-sm max-h-32">
                                                    </template>
                                                    <span x-html="opt.option"></span>
                                                </div>
                                                <template x-if="secondaryLang && currQ.options[secondaryLang]">
                                                    <div class="mt-2 pt-2 border-t border-dashed border-gray-300 text-base font-medium text-[#0777be]">
                                                        <span x-html="currQ.options[secondaryLang][idx].option"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE B: MMA (Checkbox - Multiple Select) --}}
                            <template x-if="currQ.type_code === 'MMA' || currQ.type_code === 'MMS'">
                                <div class="space-y-3">
                                    <template x-for="(opt, idx) in currQ.options['en']" :key="idx">
                                        <div class="relative flex items-start p-4 transition-all border-2 cursor-pointer select-none rounded-xl hover:bg-gray-50"
                                             :class="isChecked(idx) ? 'border-[#3498db] bg-blue-50' : 'border-gray-200'">
                                            <input type="checkbox" :value="idx" x-model="currQ.selected_option" class="w-5 h-5 mt-1 mr-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <div class="flex-1">
                                                <div class="text-base font-medium text-gray-700">
                                                    <template x-if="opt.image"><img :src="opt.image" class="mb-2 max-h-32"></template>
                                                    <span x-html="opt.option"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- TYPE C: FIB / SAQ (Text Input) --}}
                            <template x-if="currQ.type_code === 'FIB' || currQ.type_code === 'SAQ'">
                                <div class="mt-4">
                                    <label class="block mb-2 text-sm font-bold text-gray-700">Type your answer here:</label>
                                    <textarea x-model="currQ.selected_option" rows="4"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        placeholder="Enter your answer..."></textarea>
                                </div>
                            </template>

                            {{-- TYPE D: MTF (Match The Following) --}}
                            <template x-if="currQ.type_code === 'MTF'">
                                <div class="mt-4">
                                    <div class="p-3 mb-4 text-sm text-blue-800 border border-blue-200 rounded bg-blue-50">
                                        <strong>Instructions:</strong> Match items from the Left column with the Dropdown on the Right.
                                    </div>
                                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                                        <table class="w-full text-left border-collapse">
                                            <thead class="bg-gray-100 border-b border-gray-200">
                                                <tr>
                                                    <th class="w-1/2 p-3 font-bold text-gray-700">Column A</th>
                                                    <th class="w-1/2 p-3 font-bold text-gray-700">Column B (Select Match)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <template x-for="(row, idx) in currQ.options['en']" :key="idx">
                                                    <tr class="bg-white hover:bg-gray-50">
                                                        <td class="p-4 border-r border-gray-200">
                                                            <div x-html="row.option" class="font-medium text-gray-800"></div>
                                                        </td>
                                                        <td class="p-4">
                                                            <select x-model="currQ.selected_option[idx]" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                                                <option value="">-- Select Match --</option>
                                                                {{-- Using Shuffled Options generated in JS --}}
                                                                <template x-for="pairItem in currQ.shuffledRight" :key="pairItem">
                                                                    <option :value="pairItem" x-text="pairItem"></option>
                                                                </template>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="flex flex-col items-center justify-center flex-1">
                <div class="loader"></div>
                <p class="mt-4 font-bold text-gray-500 animate-pulse">Loading Section...</p>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-between p-4 border-t border-gray-300 bg-gray-50">
                <div class="flex gap-3">
                    <button @click="markReview()" class="px-5 py-2 text-sm font-bold text-white bg-purple-600 border border-purple-800 rounded shadow-sm hover:bg-purple-700">
                        Mark for Review & Next
                    </button>
                    <button @click="clear()" class="px-5 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100">
                        Clear Response
                    </button>
                </div>
                <button @click="saveNext()" class="px-8 py-2 bg-[#27AE60] hover:bg-[#219150] text-white text-sm font-bold rounded shadow-md border border-[#219150] transform active:scale-95 transition">
                    Save & Next
                </button>
            </div>
        </main>

        {{-- RIGHT: Palette & Info --}}
        <aside class="z-20 flex-col hidden bg-white border-l border-gray-300 md:flex w-80 shrink-0">
            {{-- User Info --}}
            <div class="flex items-center gap-3 p-4 border-b border-gray-200 bg-blue-50">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name) }}&background=0D8ABC&color=fff" class="w-10 h-10 border-2 border-white rounded shadow-sm">
                <div class="overflow-hidden">
                    <div class="text-sm font-bold text-gray-800 truncate">{{ $user->first_name }} {{ $user->last_name }}</div>
                    <div class="text-[10px] text-gray-500 font-bold">ID: {{ $session->code }}</div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-2 gap-y-2 gap-x-1 text-[11px] font-bold text-gray-600">
                    <div class="flex items-center gap-2"><div class="w-5 h-5 st-answered"></div> Answered</div>
                    <div class="flex items-center gap-2"><div class="w-5 h-5 st-not-answered"></div> Not Answered</div>
                    <div class="flex items-center gap-2"><div class="w-5 h-5 border st-not-visited"></div> Not Visited</div>
                    <div class="flex items-center gap-2"><div class="w-5 h-5 st-marked"></div> Marked</div>
                    <div class="flex items-center col-span-2 gap-2"><div class="w-5 h-5 st-ans-marked"></div> Ans & Marked</div>
                </div>
            </div>

            {{-- Grid --}}
            <div class="flex-1 p-4 overflow-y-auto">
                <h3 class="flex justify-between mb-3 text-xs font-bold tracking-wider text-gray-400 uppercase">
                    Question Palette
                    <span class="text-blue-600" x-text="sectionsMeta[currSecIdx].name"></span>
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="(q, idx) in currentSectionQs" :key="q.id">
                        <div @click="jumpTo(idx)" class="btn-status" :class="getStatusClass(q, idx)">
                            <span x-text="idx + 1"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Sidebar Footer --}}
            <div class="p-4 bg-gray-100 border-t border-gray-300">
                <button @click="submitExam()" class="w-full bg-[#2980b9] hover:bg-[#2c3e50] text-white font-bold py-3 rounded shadow-lg transition transform active:scale-95 border border-[#2980b9]">
                    SUBMIT TEST
                </button>
            </div>
        </aside>
    </div>

    {{-- SCRIPTS --}}
    <script>
        function examEngine(sectionsMeta, duration, sessionCode, fetchUrl, saveUrl, terminateUrl, finishUrl) {
            return {
                // Data
                sectionsMeta, loadedSections: {}, currSecIdx: 0, currQIdx: 0,

                // Config
                primaryLang: 'en', secondaryLang: '',

                // State
                showInstructions: true, agreed: false, isPreparing: false,
                started: false, loading: false, progress: 0,
                timeRemaining: duration, timer: null, qStartTime: 0, warnings: 0,

                get currentSectionQs() { return this.loadedSections[this.sectionsMeta[this.currSecIdx].id] || []; },
                get currQ() { return this.currentSectionQs[this.currQIdx] || null; },

                init() {
                    // Security Listeners
                    document.addEventListener("visibilitychange", () => { if(document.hidden && this.started) this.violation(); });
                    window.addEventListener("blur", () => { if(this.started) this.violation(); });

                    // Disable Back Button
                    history.pushState(null, null, location.href); window.onpopstate = () => history.go(1);
                },

                async startSequence() {
                    this.showInstructions = false;

                    if (this.secondaryLang) {
                        this.isPreparing = true;
                        // Load and translate FIRST section immediately
                        await this.loadData(0, true);
                        this.isPreparing = false;
                    } else {
                        // Just load english
                        await this.loadData(0, false);
                    }

                    this.startExamTimer();
                },

                startExamTimer() {
                    this.started = true;
                    this.qStartTime = Date.now();
                    document.documentElement.requestFullscreen().catch(e => console.log(e));

                    this.timer = setInterval(() => {
                        if (this.timeRemaining > 0) this.timeRemaining--;
                        else this.submitExam(true);
                    }, 1000);
                },

                // --- DATA & TRANSLATION ---
                async loadData(idx, translate) {
                    let secId = this.sectionsMeta[idx].id;

                    if (this.loadedSections[secId]) {
                        this.currSecIdx = idx; this.currQIdx = 0; return;
                    }

                    if(!this.isPreparing) this.loading = true;

                    try {
                        let res = await fetch(fetchUrl.replace('SECTION_ID', secId));
                        let data = await res.json();

                        // Structure Data: { id, text: {en: "...", hi: "..."}, options: {en: [...], hi: [...]} }
                        let processed = data.questions.map(q => {
                            // --- FIX: Resume Logic & Answer Initialization ---
                            let initialAns = null;
                            if(q.type === 'MMA' || q.type === 'MMS') initialAns = []; // Checkbox array
                            if(q.type === 'MTF') initialAns = {}; // Object for matching

                            // If Saved Answer exists from DB (Resuming exam)
                            if(q.selected_option !== null && q.selected_option !== undefined) {
                                initialAns = q.selected_option;
                            }

                            // --- FIX: MTF Shuffle Logic ---
                            let shuffledRight = [];
                            if(q.type === 'MTF' && q.options) {
                                // Extract pairs
                                let rights = q.options.map(o => o.pair).filter(p => p);
                                // Shuffle values
                                shuffledRight = rights.sort(() => Math.random() - 0.5);

                                // Fix if Resume data comes as empty array for Object type
                                if(Array.isArray(initialAns) && initialAns.length === 0) initialAns = {};
                            }

                            return {
                                ...q,
                                type_code: q.type, // Alias for consistent access
                                text: { en: q.text },
                                options: { en: q.options }, // options is array of objects {option, image}
                                selected_option: initialAns,
                                shuffledRight: shuffledRight
                            }
                        });

                        // Parallel Translation using Google GTX
                        if(translate && this.secondaryLang) {
                            await this.translateBatch(processed, this.secondaryLang);
                        }

                        this.loadedSections[secId] = processed;
                        this.currSecIdx = idx;
                        this.currQIdx = 0;
                        this.renderMath();

                    } catch(e) {
                        console.error(e);
                        Swal.fire("Error", "Failed to load section.", "error");
                    } finally {
                        this.loading = false;
                    }
                },

                // --- GOOGLE GTX API (FAST & UNLIMITED) ---
                async translateBatch(questions, target) {
                    let promises = questions.map(async (q, index) => {
                        try {
                            // 1. Translate Question Text
                            let qUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${target}&dt=t&q=${encodeURIComponent(q.text['en'])}`;
                            let qRes = await fetch(qUrl);
                            let qJson = await qRes.json();
                            // Combine all segments
                            q.text[target] = qJson[0].map(x => x[0]).join('');

                            // 2. Translate Options (Loop)
                            q.options[target] = [];
                            for(let opt of q.options['en']) {
                                let oUrl = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${target}&dt=t&q=${encodeURIComponent(opt.option)}`;
                                let oRes = await fetch(oUrl);
                                let oJson = await oRes.json();
                                let transText = oJson[0].map(x => x[0]).join('');

                                q.options[target].push({
                                    ...opt, // copy image, etc.
                                    option: transText
                                });
                            }

                            // Update Progress Bar
                            this.progress = Math.round(((index + 1) / questions.length) * 100);

                        } catch(e) {
                            // Fallback to English if translation fails
                            q.text[target] = q.text['en'];
                            q.options[target] = q.options['en'];
                        }
                    });

                    await Promise.all(promises);
                },

                // --- NAVIGATION ---
                switchSection(idx) {
                    this.saveAnswer(this.currQ.status);
                    let needTrans = (this.secondaryLang && !this.loadedSections[this.sectionsMeta[idx].id]);
                    this.loadData(idx, needTrans);
                },

                jumpTo(idx) {
                    this.saveAnswer(this.currQ.status);
                    this.currQIdx = idx;
                    this.qStartTime = Date.now();
                    this.renderMath();
                },

                // --- LOGIC ---
                selectOption(idx) { this.currQ.selected_option = idx; },

                // Helper for MMA
                isChecked(val) {
                    if(!Array.isArray(this.currQ.selected_option)) return false;
                    return this.currQ.selected_option.includes(val);
                },

                // Check if answered (Handles Arrays/Objects for new types)
                hasAnswered() {
                    let ans = this.currQ.selected_option;
                    if(ans === null || ans === undefined || ans === '') return false;
                    if(Array.isArray(ans) && ans.length === 0) return false; // MMA empty
                    if(typeof ans === 'object' && !Array.isArray(ans) && Object.keys(ans).length === 0) return false; // MTF empty
                    return true;
                },

                saveNext() {
                    let answered = this.hasAnswered();
                    this.currQ.status = answered ? 'answered' : 'not_answered';
                    this.saveAnswer(this.currQ.status);
                    this.next();
                },

                markReview() {
                    let answered = this.hasAnswered();
                    this.currQ.status = answered ? 'ans_marked' : 'marked';
                    this.saveAnswer(this.currQ.status);
                    this.next();
                },

                clear() {
                    // Clear based on type
                    if(this.currQ.type_code === 'MMA') this.currQ.selected_option = [];
                    else if(this.currQ.type_code === 'MTF') this.currQ.selected_option = {};
                    else this.currQ.selected_option = null;

                    this.currQ.status = 'not_answered';
                    this.saveAnswer('not_answered');
                },

                next() {
                    if (this.currQIdx < this.currentSectionQs.length - 1) {
                        this.currQIdx++; this.qStartTime = Date.now(); this.renderMath();
                    } else if (this.currSecIdx < this.sectionsMeta.length - 1) {
                        Swal.fire({
                            title: 'Next Section?', showCancelButton: true, confirmButtonText: 'Yes', confirmButtonColor: '#3498db'
                        }).then(r => { if(r.isConfirmed) this.switchSection(this.currSecIdx+1); });
                    }
                },

                async saveAnswer(status) {
                    let q = this.currQ;
                    let time = Math.round((Date.now() - this.qStartTime)/1000);
                    this.qStartTime = Date.now();

                    let backStatus = 'visited';
                    if(status === 'answered') backStatus = 'answered';
                    if(status === 'ans_marked') backStatus = 'answered_mark_for_review';
                    if(status === 'marked') backStatus = 'mark_for_review';

                    fetch(saveUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            question_id: q.id,
                            section_id: this.sectionsMeta[this.currSecIdx].id,
                            user_answer: q.selected_option,
                            time_taken: time,
                            total_time_taken: (this.sectionsMeta[0].duration - this.timeRemaining), // approximate
                            status: backStatus
                        })
                    });
                },

                // --- SECURITY & SUBMIT ---
                violation() {
                    this.warnings++;
                    if(this.warnings >= 3) {
                        fetch(terminateUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(r => r.json()).then(d => window.location.href = d.redirect);
                    } else {
                        Swal.fire({
                            title: `Warning ${this.warnings}/3`,
                            text: "Do not switch tabs! You will be disqualified.",
                            icon: "warning",
                            confirmButtonColor: "#d33",
                            allowOutsideClick: false
                        });
                    }
                },

                submitExam(auto = false) {
                    if(!auto) {
                        Swal.fire({
                            title: "Submit Exam?", text: "Are you sure you want to finish?", icon: "question",
                            showCancelButton: true, confirmButtonText: "Submit", confirmButtonColor: "#27AE60"
                        }).then(r => { if(r.isConfirmed) this.doSubmit(); });
                    } else {
                        this.doSubmit();
                    }
                },

                doSubmit() {
                    // Show Loading
                    Swal.fire({ title: 'Submitting...', didOpen: () => Swal.showLoading() });

                    fetch(finishUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    })
                    .then(r => r.json())
                    .then(d => {
                        // --- FIX: Correct Redirect Handling ---
                        if(d.redirect) window.location.href = d.redirect;
                        else Swal.fire("Error", "Submission failed. No redirect URL.", "error");
                    })
                    .catch(e => {
                        console.error(e);
                        Swal.fire("Error", "Submission failed.", "error");
                    });
                },

                // --- UTILS ---
                formatTime(s) { return new Date(s*1000).toISOString().substr(11,8); },
                renderMath() { this.$nextTick(() => { if(window.MathJax) window.MathJax.typesetPromise(); }); },
                getStatusClass(q, idx) {
                    let c = "";
                    if(q.status === 'not_visited' || !q.status) c='st-not-visited border';
                    else if(q.status === 'not_answered') c='st-not-answered';
                    else if(q.status === 'answered') c='st-answered';
                    else if(q.status === 'marked') c='st-marked';
                    else if(q.status === 'ans_marked') c='st-ans-marked';

                    if(idx === this.currQIdx) c += ' active-q';
                    return c;
                }
            }
        }
    </script>
</body>
</html>
