<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSC/UPSC Mock Test - Exam Babu</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Roboto', sans-serif; user-select: none; }

        /* --- TCS iON Palette Styles --- */
        .btn-status { width: 35px; height: 35px; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.1s; background: white; border: 1px solid #ccc; }

        /* White: Not Visited */
        .st-not-visited { background: #ffffff; color: #000; border-radius: 4px; }

        /* Red: Not Answered (Clipped Shape) */
        .st-not-answered { background: #E74C3C; color: #fff; border-color: #E74C3C; border-radius: 4px; clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 50% 100%, 0% 75%); padding-bottom: 5px; }

        /* Green: Answered (Clipped Shape) */
        .st-answered { background: #27AE60; color: #fff; border-color: #27AE60; border-radius: 4px; clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 50% 100%, 0% 75%); padding-bottom: 5px; }

        /* Purple: Marked */
        .st-marked { background: #8E44AD; color: #fff; border-radius: 50%; border-color: #8E44AD; }

        /* Purple with Green Tick: Answered & Marked */
        .st-ans-marked { background: #8E44AD; color: #fff; position: relative; border-radius: 50%; border-color: #8E44AD; }
        .st-ans-marked::after { content: '✔'; position: absolute; bottom: 0px; right: -4px; font-size: 10px; background: #27AE60; color: white; width: 14px; height: 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid white; z-index: 10; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

{{--
    =========================================================
    PHP DATA ENGINE
    =========================================================
--}}
@php
    $userName = auth()->check() ? auth()->user()->name : "Student Candidate";
    $userImage = "https://ui-avatars.com/api/?name=".urlencode($userName)."&background=0D8ABC&color=fff";
    $examTitle = "SSC CGL Tier-1 Mock Test 2025 (Official Pattern)";
    $examDuration = 60 * 60; // 60 Minutes

    // Helper: Create Bilingual Questions
    function getBilingualQuestion($type, $index) {
        if($type == 'math') {
            return [
                'en' => "Q{$index}: If x + 1/x = 5, find the value of x² + 1/x².",
                'hi' => "प्रश्न {$index}: यदि x + 1/x = 5 है, तो x² + 1/x² का मान ज्ञात करें।"
            ];
        } elseif($type == 'gk') {
            return [
                'en' => "Q{$index}: Which Article deals with 'Right to Education'?",
                'hi' => "प्रश्न {$index}: 'शिक्षा का अधिकार' किस अनुच्छेद से संबंधित है?"
            ];
        } elseif($type == 'eng') {
            return [
                'en' => "Q{$index}: Select the synonym of 'OBSOLETE'.",
                'hi' => "प्रश्न {$index}: 'OBSOLETE' का पर्यायवाची चुनें (English Section)."
            ];
        }
        return [
            'en' => "Q{$index}: Find the missing number: 2, 5, 11, 23, ?",
            'hi' => "प्रश्न {$index}: लुप्त संख्या ज्ञात करें: 2, 5, 11, 23, ?"
        ];
    }

    $sections = [
        ['id' => 's1', 'name' => 'General Intelligence', 'type' => 'reasoning', 'questions' => []],
        ['id' => 's2', 'name' => 'General Awareness', 'type' => 'gk', 'questions' => []],
        ['id' => 's3', 'name' => 'Quantitative Aptitude', 'type' => 'math', 'questions' => []],
        ['id' => 's4', 'name' => 'English Comprehension', 'type' => 'eng', 'questions' => []]
    ];

    foreach($sections as &$section) {
        for($i = 1; $i <= 25; $i++) {
            $section['questions'][] = [
                'id' => $section['id'] . '_' . $i,
                'text' => getBilingualQuestion($section['type'], $i),
                'options' => [
                    'en' => ['Option A', 'Option B', 'Option C', 'Option D'],
                    'hi' => ['विकल्प A', 'विकल्प B', 'विकल्प C', 'विकल्प D']
                ],
                'status' => 'not_visited',
                'selected_option' => null,
                'marks' => 2.0,
                'negative' => 0.50
            ];
        }
    }
    unset($section);
@endphp

<body class="flex flex-col h-screen overflow-hidden bg-gray-100"
      x-data="examEngine(@js($sections), {{ $examDuration }})"
      x-init="initEngine()"
      @contextmenu.prevent="return false;"
      @keydown.ctrl.c.prevent="return false;"
      @keydown.meta.c.prevent="return false;"
      @keydown.f12.prevent="return false;"
>

    <!-- ========================================== -->
    <!-- 1. INSTRUCTIONS PAGE (Start Screen)        -->
    <!-- ========================================== -->
    <div x-show="showInstructions" class="fixed inset-0 z-[100] bg-white overflow-y-auto">
        <header class="sticky top-0 flex items-center h-16 px-6 text-white bg-blue-600 shadow-md">
            <h1 class="text-xl font-bold">Instructions - {{ $examTitle }}</h1>
        </header>

        <div class="max-w-5xl p-6 mx-auto md:p-10">
            <div class="flex items-center gap-4 mb-6">
                <img src="{{ $userImage }}" class="w-16 h-16 border-4 border-gray-200 rounded-full">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $userName }}</h2>
                    <p class="text-gray-500">Please read the instructions carefully.</p>
                </div>
            </div>

            <div class="p-4 mb-6 text-sm text-yellow-800 border-l-4 border-yellow-500 bg-yellow-50">
                <strong>Warning:</strong> Switching tabs is strictly prohibited. You will be disqualified after 3 warnings.
            </div>

            <div class="pb-6 space-y-4 text-sm leading-relaxed text-gray-700 border-b md:text-base">
                <p>1. Total duration: <strong>60 minutes</strong>.</p>
                <p>2. The clock is set at the server. The countdown timer at the top right shows remaining time.</p>
                <p>3. Question Palette Symbols:</p>
                <div class="grid grid-cols-2 gap-4 p-4 pl-4 my-4 rounded-lg md:grid-cols-4 bg-gray-50">
                    <div class="flex items-center gap-2"><div class="w-6 h-6 border st-not-visited"></div> Not Visited</div>
                    <div class="flex items-center gap-2"><div class="w-6 h-6 st-not-answered"></div> Not Answered</div>
                    <div class="flex items-center gap-2"><div class="w-6 h-6 st-answered"></div> Answered</div>
                    <div class="flex items-center gap-2"><div class="w-6 h-6 st-ans-marked"></div> Ans & Marked for Review</div>
                </div>
            </div>

            <div class="mt-6">
                <label class="flex items-center gap-3 p-3 transition rounded-lg cursor-pointer select-none hover:bg-gray-50">
                    <input type="checkbox" x-model="agreedToInstructions" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="font-bold text-gray-800">I have read the instructions and agree to the terms.</span>
                </label>
            </div>

            <div class="flex justify-center mt-8">
                <button @click="startExam()"
                        :disabled="!agreedToInstructions"
                        class="px-12 py-4 text-lg font-bold text-white transition transform bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    I am ready to begin
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. MAIN EXAM INTERFACE                     -->
    <!-- ========================================== -->

    <!-- HEADER -->
    <header class="h-16 bg-[#3498db] text-white flex justify-between items-center px-4 shadow-md z-50 shrink-0" x-show="!showInstructions" x-cloak>
        <div class="hidden text-lg font-bold tracking-wide sm:block">{{ $examTitle }}</div>
        <div class="text-lg font-bold sm:hidden">Exam Babu</div>

        <div class="flex items-center gap-4 sm:gap-6">

            <!-- Language Switcher -->
            <div class="flex flex-col items-end">
                <span class="text-[10px] text-blue-100 font-semibold">Language</span>
                <select x-model="currentLang" class="px-2 py-1 text-xs font-bold text-black rounded shadow-sm cursor-pointer focus:outline-none">
                    <option value="en">English</option>
                    <option value="hi">Hindi</option>
                </select>
            </div>

            <!-- Timer -->
            <div class="flex flex-col items-end min-w-[80px]">
                <span class="text-[10px] text-blue-100 uppercase font-semibold">Time Left</span>
                <span class="font-mono text-xl font-bold" :class="timeRemaining < 300 ? 'text-yellow-300 animate-pulse' : 'text-white'" x-text="formatTime(timeRemaining)"></span>
            </div>

            <!-- Full Paper Button -->
            <button @click="showFullPaper = true" class="hidden px-3 py-1 text-xs font-bold border rounded md:block bg-white/20 hover:bg-white/30 border-white/40">
                Question Paper
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden" x-show="!showInstructions" x-cloak>

        <!-- LEFT: QUESTION AREA -->
        <main class="relative flex flex-col flex-1 bg-white border-r border-gray-300">

            <!-- Section Tabs -->
            <div class="flex overflow-x-auto border-b border-gray-300 bg-gray-50 no-scrollbar">
                <template x-for="(sec, idx) in sections" :key="sec.id">
                    <button @click="switchSection(idx)"
                            class="relative px-4 py-3 text-sm font-bold transition-colors border-r border-gray-300 whitespace-nowrap focus:outline-none"
                            :class="currentSectionIndex === idx ? 'bg-[#3498db] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        <span x-text="sec.name"></span>
                        <!-- Unanswered Indicator -->
                        <div class="absolute w-2 h-2 bg-orange-500 rounded-full top-1 right-1" x-show="sec.questions.some(q => q.status === 'not_answered')"></div>
                    </button>
                </template>
            </div>

            <!-- Question Info Bar -->
            <div class="z-10 flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200 shadow-sm">
                <h2 class="text-base font-bold text-red-600 sm:text-lg">Question No. <span x-text="currentQuestionIndex + 1"></span></h2>

                <!-- Font Size Controls -->
                <div class="flex items-center gap-1 p-1 bg-gray-100 rounded">
                    <button @click="fontSize = 'text-sm'" class="flex items-center justify-center text-xs font-bold rounded w-7 h-7 hover:bg-white" :class="fontSize === 'text-sm' ? 'bg-white shadow text-blue-600' : 'text-gray-500'">A-</button>
                    <button @click="fontSize = 'text-base'" class="flex items-center justify-center text-sm font-bold rounded w-7 h-7 hover:bg-white" :class="fontSize === 'text-base' ? 'bg-white shadow text-blue-600' : 'text-gray-500'">A</button>
                    <button @click="fontSize = 'text-xl'" class="flex items-center justify-center text-lg font-bold rounded w-7 h-7 hover:bg-white" :class="fontSize === 'text-xl' ? 'bg-white shadow text-blue-600' : 'text-gray-500'">A+</button>
                </div>

                <div class="flex gap-2 text-[10px] sm:text-xs font-bold text-gray-600">
                    <span class="px-2 py-1 text-green-600 border border-green-100 rounded bg-green-50">+<span x-text="currentQuestion.marks"></span></span>
                    <span class="px-2 py-1 text-red-600 border border-red-100 rounded bg-red-50">-<span x-text="currentQuestion.negative"></span></span>
                </div>
            </div>

            <!-- Question Content -->
            <div class="flex-1 p-4 overflow-y-auto md:p-8" id="questionArea">
                <div class="max-w-4xl mx-auto" :class="fontSize">
                    <!-- Question Text -->
                    <div class="pb-4 mb-6 font-medium leading-relaxed text-gray-800 border-b select-none"
                         x-html="currentQuestion.text[currentLang]">
                    </div>

                    <!-- Options (FIXED: Using Click Div instead of Radio Input) -->
                    <div class="space-y-3">
                        <template x-for="(opt, optIdx) in currentQuestion.options[currentLang]" :key="optIdx">

                            <!-- Main Clickable Container -->
                            <div @click="selectOption(optIdx)"
                                 class="relative flex items-start p-3 transition-all border-2 rounded-lg cursor-pointer select-none group"
                                 :class="currentQuestion.selected_option === optIdx ? 'border-[#3498db] bg-blue-50 shadow-sm' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'">

                                <!-- Custom Radio Circle -->
                                <div class="flex items-center justify-center w-6 h-6 mr-3 transition-colors border-2 rounded-full shrink-0"
                                     :class="currentQuestion.selected_option === optIdx ? 'border-blue-600 bg-blue-600' : 'border-gray-400 group-hover:border-blue-400'">
                                    <!-- Inner White Dot -->
                                    <div class="w-2.5 h-2.5 bg-white rounded-full transition-transform duration-200"
                                         :class="currentQuestion.selected_option === optIdx ? 'scale-100' : 'scale-0'"></div>
                                </div>

                                <!-- Option Text -->
                                <span class="font-medium text-gray-700 pt-0.5" x-text="opt"></span>
                            </div>

                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex flex-col items-center justify-between gap-3 p-3 border-t border-gray-300 shadow-inner bg-gray-50 sm:flex-row">
                <div class="flex w-full gap-2 sm:w-auto">
                    <button @click="markForReview()" class="flex-1 px-4 py-2 text-xs font-bold text-white transition bg-purple-600 border border-purple-800 rounded shadow-sm sm:flex-none hover:bg-purple-700 sm:text-sm">
                        Mark for Review & Next
                    </button>
                    <button @click="clearResponse()" class="flex-1 px-4 py-2 text-xs font-bold text-gray-700 transition bg-white border border-gray-300 rounded sm:flex-none hover:bg-gray-100 sm:text-sm">
                        Clear Response
                    </button>
                </div>

                <button @click="saveAndNext()" class="w-full sm:w-auto px-8 py-2 bg-[#27AE60] hover:bg-[#219150] text-white text-sm font-bold rounded shadow-md border border-[#219150] transform active:scale-95 transition">
                    Save & Next
                </button>
            </div>
        </main>

        <!-- RIGHT: PALETTE AREA -->
        <aside class="z-20 flex flex-col hidden bg-white border-l border-gray-300 w-80 shrink-0 md:flex">
            <!-- User Mini Info -->
            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-blue-50">
                <img src="{{ $userImage }}" class="w-10 h-10 rounded shadow-sm">
                <div class="overflow-hidden">
                    <div class="text-sm font-bold text-gray-800 truncate">{{ $userName }}</div>
                    <div class="text-[10px] text-gray-500 font-semibold">Candidate ID: EXAM-2025</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="p-3 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-2 gap-y-2 gap-x-1 text-[10px] font-medium text-gray-600">
                    <div class="flex items-center gap-1.5"><div class="w-5 h-5 st-answered"></div> Answered</div>
                    <div class="flex items-center gap-1.5"><div class="w-5 h-5 st-not-answered"></div> Not Answered</div>
                    <div class="flex items-center gap-1.5"><div class="w-5 h-5 bg-white border st-not-visited"></div> Not Visited</div>
                    <div class="flex items-center gap-1.5"><div class="flex items-center justify-center w-5 h-5 text-white st-marked"></div> Marked</div>
                    <div class="flex items-center gap-1.5 col-span-2"><div class="w-5 h-5 st-ans-marked"></div> Ans & Marked for Review</div>
                </div>
            </div>

            <!-- Palette Grid -->
            <div class="relative flex-1 p-4 overflow-y-auto bg-white">
                <h3 class="sticky top-0 z-10 py-1 mb-3 text-sm font-bold text-gray-700 bg-white border-b">
                    Section: <span class="text-blue-600" x-text="sections[currentSectionIndex].name"></span>
                </h3>
                <div class="grid grid-cols-4 gap-3">
                    <template x-for="(q, idx) in sections[currentSectionIndex].questions" :key="q.id">
                        <div @click="jumpToQuestion(idx)"
                             class="btn-status hover:ring-2 hover:ring-gray-300"
                             :class="{
                                'st-not-visited': q.status === 'not_visited',
                                'st-not-answered': q.status === 'not_answered',
                                'st-answered': q.status === 'answered',
                                'st-marked': q.status === 'marked',
                                'st-ans-marked': q.status === 'ans_marked',
                                'ring-2 ring-blue-500 ring-offset-2': idx === currentQuestionIndex
                             }">
                            <span x-text="idx + 1"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="p-4 bg-gray-100 border-t border-gray-300">
                <button @click="confirmSubmit()" class="w-full bg-[#2980b9] hover:bg-[#2c3e50] text-white font-bold py-3 rounded shadow-lg transition transform active:scale-95 border border-[#2980b9]">
                    SUBMIT TEST
                </button>
            </div>
        </aside>
    </div>

    <!-- ========================================== -->
    <!-- MODALS                                     -->
    <!-- ========================================== -->

    <!-- Question Paper View Modal -->
    <div x-show="showFullPaper" class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white w-full max-w-5xl h-[85vh] rounded-xl flex flex-col shadow-2xl">
            <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-xl">
                <h3 class="text-lg font-bold text-gray-800">Question Paper (Full View)</h3>
                <button @click="showFullPaper = false" class="text-2xl font-bold text-gray-500 transition hover:text-red-500">&times;</button>
            </div>
            <div class="flex-1 p-8 overflow-y-auto bg-white">
                <template x-for="sec in sections">
                    <div class="mb-8">
                        <h4 class="inline-block px-4 py-2 mb-4 text-lg font-bold text-white bg-blue-600 rounded shadow-sm" x-text="sec.name"></h4>
                        <div class="space-y-6">
                            <template x-for="(q, idx) in sec.questions">
                                <div class="pb-4 text-sm border-b border-gray-100">
                                    <div class="flex gap-2 mb-2">
                                        <span class="font-bold text-blue-600" x-text="'Q'+(idx+1)+'.'"></span>
                                        <span class="font-medium text-gray-800" x-html="q.text[currentLang]"></span>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 pl-6 text-gray-600 sm:grid-cols-2">
                                        <template x-for="(opt, oIdx) in q.options[currentLang]">
                                            <div class="flex gap-2">
                                                <span class="font-bold" x-text="String.fromCharCode(65+oIdx) + '.'"></span>
                                                <span x-text="opt"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Mobile Palette Drawer -->
    <div x-show="mobileDrawerOpen" class="fixed inset-0 z-50 bg-black/50 md:hidden" @click="mobileDrawerOpen = false" x-cloak></div>
    <div class="fixed inset-y-0 right-0 z-[55] w-64 bg-white shadow-xl transform transition-transform duration-300 md:hidden flex flex-col"
         :class="mobileDrawerOpen ? 'translate-x-0' : 'translate-x-full'" x-cloak>
         <div class="p-4 font-bold border-b bg-blue-50">Question Palette</div>
         <div class="flex-1 p-4 overflow-y-auto">
             <div class="grid grid-cols-4 gap-2">
                 <template x-for="(q, idx) in sections[currentSectionIndex].questions" :key="q.id">
                     <div @click="jumpToQuestion(idx); mobileDrawerOpen = false"
                          class="btn-status"
                          :class="getPaletteClass(q, idx)">
                         <span x-text="idx + 1"></span>
                     </div>
                 </template>
             </div>
         </div>
         <div class="p-4 bg-gray-100">
             <button @click="confirmSubmit()" class="w-full py-2 font-bold text-white bg-blue-600 rounded">Submit Test</button>
         </div>
    </div>

    <!-- Mobile Drawer Trigger -->
    <button @click="mobileDrawerOpen = true" class="fixed z-40 flex items-center justify-center w-12 h-12 font-bold text-white bg-blue-600 rounded-full shadow-lg md:hidden bottom-6 right-6">
        ☰
    </button>

    <script>
        function examEngine(serverData, duration) {
            return {
                sections: serverData,
                currentSectionIndex: 0,
                currentQuestionIndex: 0,

                // State Variables
                timeRemaining: duration,
                timerInterval: null,
                warningCount: 0,
                examStarted: false,

                // UX Settings
                currentLang: 'en',
                fontSize: 'text-base',
                showInstructions: true,
                agreedToInstructions: false,
                showFullPaper: false,
                mobileDrawerOpen: false,

                // Computed getter for current question
                get currentQuestion() {
                    return this.sections[this.currentSectionIndex].questions[this.currentQuestionIndex];
                },

                initEngine() {
                    // Disable Back Button
                    history.pushState(null, null, location.href);
                    window.onpopstate = () => history.go(1);

                    // Proctoring: Detect Tab Switch
                    document.addEventListener("visibilitychange", () => {
                        if (document.hidden && this.examStarted) {
                            this.handleViolation();
                        }
                    });
                },

                // START EXAM LOGIC
                startExam() {
                    this.showInstructions = false;
                    this.examStarted = true;

                    // Request Full Screen
                    const el = document.documentElement;
                    if(el.requestFullscreen) el.requestFullscreen().catch(err => console.log(err));

                    // Start Timer
                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;
                        } else {
                            this.autoSubmit("Time Up!");
                        }
                    }, 1000);

                    // Mark first question as visited
                    this.updateVisitStatus();
                },

                formatTime(seconds) {
                    const h = Math.floor(seconds / 3600);
                    const m = Math.floor((seconds % 3600) / 60);
                    const s = seconds % 60;
                    return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
                },

                // NAVIGATION
                switchSection(idx) {
                    this.currentSectionIndex = idx;
                    this.currentQuestionIndex = 0;
                    this.updateVisitStatus();
                },

                jumpToQuestion(idx) {
                    this.currentQuestionIndex = idx;
                    this.updateVisitStatus();
                },

                moveToNext() {
                    const sectionQuestions = this.sections[this.currentSectionIndex].questions;

                    // If not last question in section
                    if (this.currentQuestionIndex < sectionQuestions.length - 1) {
                        this.currentQuestionIndex++;
                        this.updateVisitStatus();
                    }
                    // If last question in section, check if more sections exist
                    else if (this.currentSectionIndex < this.sections.length - 1) {
                        Swal.fire({
                            title: 'Section Completed',
                            text: "Do you want to proceed to the next section?",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Proceed',
                            confirmButtonColor: '#3498db'
                        }).then((res) => {
                            if(res.isConfirmed) this.switchSection(this.currentSectionIndex + 1);
                        });
                    } else {
                        // End of Exam
                        Swal.fire('Info', 'This was the last question.', 'info');
                    }
                },

                // ANSWER LOGIC (THE FIX)
                selectOption(optIdx) {
                    this.currentQuestion.selected_option = optIdx;
                },

                // STATUS UPDATES
                saveAndNext() {
                    const q = this.currentQuestion;
                    if (q.selected_option !== null) {
                        q.status = 'answered';
                    } else {
                        q.status = 'not_answered';
                    }
                    this.moveToNext();
                },

                markForReview() {
                    const q = this.currentQuestion;
                    if (q.selected_option !== null) {
                        q.status = 'ans_marked';
                    } else {
                        q.status = 'marked';
                    }
                    this.moveToNext();
                },

                clearResponse() {
                    this.currentQuestion.selected_option = null;
                    this.currentQuestion.status = 'not_answered';
                },

                updateVisitStatus() {
                    // Logic: If status is 'not_visited', turn it to 'not_answered' (Red) immediately upon visiting
                    // This mimics real exam behavior.
                    if (this.currentQuestion.status === 'not_visited') {
                        this.currentQuestion.status = 'not_answered';
                    }
                },

                // HELPER
                getPaletteClass(q, idx) {
                    let cls = "";
                    // Status Class
                    if (q.status === 'not_visited') cls = 'st-not-visited';
                    else if (q.status === 'not_answered') cls = 'st-not-answered';
                    else if (q.status === 'answered') cls = 'st-answered';
                    else if (q.status === 'marked') cls = 'st-marked';
                    else if (q.status === 'ans_marked') cls = 'st-ans-marked';

                    // Active Border
                    if (idx === this.currentQuestionIndex) cls += ' ring-2 ring-blue-500 ring-offset-2';
                    return cls;
                },

                // SECURITY
                handleViolation() {
                    this.warningCount++;
                    if (this.warningCount >= 3) {
                        this.autoSubmit("Disqualified: Multiple Window Switches Detected");
                    } else {
                        Swal.fire({
                            title: 'Warning! (' + this.warningCount + '/3)',
                            html: 'You moved away from the exam window.<br><b>Do not switch tabs.</b>',
                            icon: 'warning',
                            confirmButtonColor: '#d33',
                            allowOutsideClick: false
                        });
                    }
                },

                // SUBMIT
                confirmSubmit() {
                    let answered = 0;
                    this.sections.forEach(s => s.questions.forEach(q => {
                        if(q.status === 'answered' || q.status === 'ans_marked') answered++;
                    }));

                    Swal.fire({
                        title: 'Submit Test?',
                        html: `You have answered <b>${answered}</b> questions.<br>Are you sure you want to finish?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Submit Exam',
                        confirmButtonColor: '#27AE60',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) this.finalSubmitLogic();
                    });
                },

                autoSubmit(msg) {
                    clearInterval(this.timerInterval);
                    Swal.fire({
                        title: msg,
                        text: 'Submitting your exam...',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => this.finalSubmitLogic());
                },

                finalSubmitLogic() {
                    // Prepare Payload for Backend
                    const payload = {
                        student: "{{ $userName }}",
                        examData: this.sections
                    };
                    console.log("Submitting:", payload);

                    Swal.fire({
                        title: 'Exam Submitted Successfully!',
                        text: 'Redirecting to results...',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect
                        window.location.href = "/dashboard";
                    });
                }
            }
        }
    </script>
</body>
</html>
