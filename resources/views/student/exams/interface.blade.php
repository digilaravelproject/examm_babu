<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Roboto', sans-serif; user-select: none; }

        /* TCS Palette */
        .btn-status { width: 35px; height: 35px; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; cursor: pointer; background: white; border: 1px solid #ccc; }
        .st-not-answered { background: #E74C3C; color: #fff; border-radius: 4px; clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 50% 100%, 0% 75%); padding-bottom: 5px; }
        .st-answered { background: #27AE60; color: #fff; border-radius: 4px; clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 50% 100%, 0% 75%); padding-bottom: 5px; }
        .st-marked { background: #8E44AD; color: #fff; border-radius: 50%; }
        .st-ans-marked { background: #8E44AD; color: #fff; border-radius: 50%; position: relative; }
        .st-ans-marked::after { content: '✔'; position: absolute; bottom: 0; right: -4px; font-size: 10px; background: #27AE60; color: white; width: 14px; height: 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid white; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>

<body class="flex flex-col h-screen overflow-hidden bg-gray-100"
      x-data="examEngine(
          @js($sections),
          {{ $remainingSeconds }},
          '{{ $session->code }}',
          '{{ route('student.exam.fetch_section', ['sessionCode' => $session->code, 'sectionId' => 'SECTION_ID']) }}',
          '{{ route('student.exam.save_answer', $session->code) }}',
          '{{ route('student.exam.suspend', $session->code) }}',
          '{{ route('student.exam.finish', $session->code) }}'
      )"
      x-init="initEngine()"
      @contextmenu.prevent="return false;"
      @keydown.f12.prevent="return false;">

    <div x-show="showInstructions" class="fixed inset-0 z-[100] bg-white overflow-y-auto">
        <header class="sticky top-0 flex items-center h-16 px-6 text-white bg-blue-600 shadow-md">
            <h1 class="text-xl font-bold">Instructions</h1>
        </header>
        <div class="max-w-5xl p-8 mx-auto">
            <div class="flex items-center gap-4 mb-6">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=0D8ABC&color=fff" class="w-16 h-16 border-4 border-gray-200 rounded-full">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="text-gray-500">Read instructions carefully before starting.</p>
                </div>
            </div>

            <div class="p-5 mb-6 border border-blue-200 rounded-lg bg-blue-50">
                <h3 class="mb-3 font-bold text-gray-800">Language Preference</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Default</label>
                        <select disabled class="w-full p-2 text-gray-600 bg-gray-100 border rounded"><option>English</option></select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Secondary (Optional)</label>
                        <select x-model="secondaryLang" class="w-full p-2 bg-white border border-blue-400 rounded shadow-sm">
                            <option value="">-- None --</option>
                            <option value="hi">Hindi</option>
                            <option value="mr">Marathi</option>
                        </select>
                    </div>
                </div>
            </div>

            <button @click="startExam()" class="w-full px-12 py-4 text-lg font-bold text-white bg-blue-600 rounded-lg shadow-lg md:w-auto hover:bg-blue-700">
                I am ready to begin
            </button>
        </div>
    </div>

    <header class="h-16 bg-[#3498db] text-white flex justify-between items-center px-4 shadow-md z-50 shrink-0" x-show="!showInstructions" x-cloak>
        <div class="text-lg font-bold truncate">{{ $exam->title }}</div>

        <div class="flex items-center gap-4">
            <div class="text-right" x-show="secondaryLang">
                <span class="text-[10px] text-blue-100 font-bold uppercase">View In</span>
                <select x-model="currentLang" @change="handleLangChange()" class="px-2 py-1 text-xs font-bold text-black rounded cursor-pointer">
                    <option value="en">English</option>
                    <option :value="secondaryLang" x-text="secondaryLang.toUpperCase()"></option>
                </select>
            </div>
            <div class="text-right min-w-[80px]">
                <span class="text-[10px] text-blue-100 font-bold uppercase">Time Left</span>
                <div class="font-mono text-xl font-bold" x-text="formatTime(timeRemaining)"></div>
            </div>
            <button @click="finishExam()" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1.5 px-4 rounded shadow">Submit</button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden" x-show="!showInstructions" x-cloak>
        <main class="relative flex flex-col flex-1 bg-white border-r border-gray-300">
            <div class="flex overflow-x-auto border-b border-gray-300 bg-gray-50 no-scrollbar">
                <template x-for="(sec, idx) in sectionsMeta" :key="sec.id">
                    <button @click="switchSection(idx)"
                            class="px-5 py-3 text-sm font-bold transition-colors border-r border-gray-300 whitespace-nowrap focus:outline-none"
                            :class="currentSectionIndex === idx ? 'bg-[#3498db] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        <span x-text="sec.name"></span>
                    </button>
                </template>
            </div>

            <div x-show="isLoading" class="absolute inset-0 z-40 flex flex-col items-center justify-center bg-white">
                <div class="w-10 h-10 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                <p class="mt-2 text-sm font-medium text-gray-500">Loading Section...</p>
            </div>

            <div class="flex-1 p-6 overflow-y-auto" x-show="!isLoading && currentQuestion">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between pb-4 mb-6 bg-white border-b border-gray-200">
                        <h2 class="text-lg font-bold text-red-600">Q.<span x-text="currentQuestionIndex + 1"></span></h2>
                        <div class="flex gap-2 text-xs font-bold text-gray-600">
                            <span class="px-2 py-1 rounded bg-green-50">+<span x-text="currentQuestion?.marks || '1'"></span></span>
                            <span class="px-2 py-1 rounded bg-red-50">-<span x-text="currentQuestion?.negative || '0'"></span></span>
                        </div>
                    </div>

                    <div class="mb-6 text-lg font-medium leading-relaxed text-gray-800 select-none">
                        <div x-html="getQuestionText()"></div>
                        <div x-show="isTranslating" class="mt-2 text-xs text-orange-500 animate-pulse">Translating...</div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(opt, idx) in getOptions()" :key="idx">
                            <div @click="selectOption(idx)"
                                 class="flex items-start p-4 transition-all border-2 cursor-pointer select-none rounded-xl group"
                                 :class="isSelected(idx) ? 'border-[#3498db] bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="flex items-center justify-center w-6 h-6 mr-4 border-2 rounded-full shrink-0"
                                     :class="isSelected(idx) ? 'border-blue-600 bg-blue-600' : 'border-gray-400'">
                                    <div class="w-2.5 h-2.5 bg-white rounded-full" x-show="isSelected(idx)"></div>
                                </div>
                                <span class="font-medium text-gray-700" x-html="opt"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 border-t border-gray-300 bg-gray-50">
                <div class="flex gap-2">
                    <button @click="markForReview()" class="px-4 py-2 text-xs font-bold text-white bg-purple-600 rounded hover:bg-purple-700">Mark & Next</button>
                    <button @click="clearResponse()" class="px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-100">Clear</button>
                </div>
                <button @click="saveAndNext()" class="px-8 py-2 bg-[#27AE60] hover:bg-[#219150] text-white text-sm font-bold rounded shadow-md">Save & Next</button>
            </div>
        </main>

        <aside class="z-20 flex flex-col hidden bg-white border-l border-gray-300 w-80 md:flex shrink-0">
            <div class="p-3 border-b border-gray-200 bg-blue-50">
                <div class="text-sm font-bold text-gray-800">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="text-[10px] text-gray-500 font-semibold">ID: {{ $session->code }}</div>
            </div>

            <div class="p-3 bg-gray-50 border-b border-gray-200 grid grid-cols-2 gap-2 text-[10px] font-medium text-gray-600">
                <div class="flex items-center gap-1"><div class="w-5 h-5 st-answered"></div> Answered</div>
                <div class="flex items-center gap-1"><div class="w-5 h-5 st-not-answered"></div> Not Ans</div>
                <div class="flex items-center gap-1"><div class="w-5 h-5 bg-white border"></div> Not Visited</div>
                <div class="flex items-center gap-1"><div class="w-5 h-5 st-marked"></div> Marked</div>
            </div>

            <div class="flex-1 p-4 overflow-y-auto">
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="(q, idx) in currentSectionQuestions" :key="q.id">
                        <div @click="jumpToQuestion(idx)" class="btn-status" :class="getPaletteClass(q, idx)">
                            <span x-text="idx + 1"></span>
                        </div>
                    </template>
                </div>
            </div>
        </aside>
    </div>

    <script>
        function examEngine(sectionsMeta, duration, sessionCode, fetchUrlTemplate, saveUrl, suspendUrl, finishUrl) {
            return {
                sectionsMeta: sectionsMeta,
                loadedSections: {},
                currentSectionIndex: 0,
                currentQuestionIndex: 0,

                // Timer & Logic State
                initialDuration: duration,
                timeRemaining: duration,
                timerInterval: null,
                questionStartTime: Date.now(),
                warningCount: 0,

                // UX State
                primaryLang: 'en',
                secondaryLang: '',
                currentLang: 'en',
                showInstructions: true,
                isLoading: false,
                isTranslating: false,

                get currentSectionQuestions() {
                    let secId = this.sectionsMeta[this.currentSectionIndex].id;
                    return this.loadedSections[secId] || [];
                },

                get currentQuestion() {
                    return this.currentSectionQuestions[this.currentQuestionIndex] || null;
                },

                initEngine() {
                    // Load First Section
                    this.loadSectionData(0);

                    // Timer Logic
                    this.timerInterval = setInterval(() => {
                        if(this.timeRemaining > 0) this.timeRemaining--;
                        else this.finishExam(true);
                    }, 1000);

                    // Anti-Cheat
                    document.addEventListener("visibilitychange", () => {
                        if(document.hidden && !this.showInstructions) this.handleViolation();
                    });
                },

                startExam() {
                    this.showInstructions = false;
                    this.currentLang = this.primaryLang;
                    this.questionStartTime = Date.now(); // Start tracking first question time
                    document.documentElement.requestFullscreen().catch(e => console.log(e));
                },

                // --- DATA LOGIC ---
                async loadSectionData(index) {
                    let secId = this.sectionsMeta[index].id;
                    if(this.loadedSections[secId]) {
                        this.switchContext(index, 0);
                        return;
                    }

                    this.isLoading = true;
                    let url = fetchUrlTemplate.replace('SECTION_ID', secId);

                    try {
                        let res = await fetch(url);
                        let data = await res.json();

                        // Process data to match frontend structure for Translations
                        // Backend returns 'text' and 'options'. We wrap them for translation logic.
                        let processed = data.questions.map(q => ({
                            ...q,
                            text: { en: q.text },
                            options: { en: q.options }
                        }));

                        this.loadedSections[secId] = processed;
                        this.switchContext(index, 0);
                    } catch(e) {
                        console.error(e);
                        Swal.fire("Error", "Network Error. Please retry.", "error");
                    } finally {
                        this.isLoading = false;
                    }
                },

                switchContext(secIndex, qIndex) {
                    this.currentSectionIndex = secIndex;
                    this.currentQuestionIndex = qIndex;
                    this.questionStartTime = Date.now(); // Reset time tracker for new question
                },

                async saveAnswer(uiStatus) {
                    const q = this.currentQuestion;
                    const secId = this.sectionsMeta[this.currentSectionIndex].id;

                    // Calculate Time Taken for this specific question
                    const now = Date.now();
                    const timeSpent = Math.round((now - this.questionStartTime) / 1000);
                    this.questionStartTime = now; // Reset

                    // Map UI status to Backend status
                    let backendStatus = 'visited';
                    if (uiStatus === 'answered') backendStatus = 'answered';
                    if (uiStatus === 'ans_marked') backendStatus = 'answered_mark_for_review';
                    if (uiStatus === 'marked') backendStatus = 'mark_for_review';
                    if (uiStatus === 'not_answered') backendStatus = 'visited';

                    await fetch(saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            question_id: q.id,
                            section_id: secId,
                            user_answer: q.selected_option, // Changed key to match backend
                            time_taken: timeSpent,         // Added required field
                            total_time_taken: (this.initialDuration - this.timeRemaining), // Added global tracker
                            status: backendStatus          // Mapped status
                        })
                    });
                },

                // --- NAVIGATION & INTERACTION ---
                switchSection(idx) {
                    this.saveAnswer(this.currentQuestion.status); // Save current before switch
                    this.loadSectionData(idx);
                },

                jumpToQuestion(idx) {
                    this.saveAnswer(this.currentQuestion.status); // Save current before jump
                    this.currentQuestionIndex = idx;
                    this.questionStartTime = Date.now(); // Reset timer
                    this.checkTranslation();
                },

                saveAndNext() {
                    const q = this.currentQuestion;
                    // Logic: If has answer, 'answered', else 'not_answered'
                    q.status = (q.selected_option !== null && q.selected_option !== undefined) ? 'answered' : 'not_answered';
                    this.saveAnswer(q.status);
                    this.moveToNext();
                },

                markForReview() {
                    const q = this.currentQuestion;
                    q.status = (q.selected_option !== null && q.selected_option !== undefined) ? 'ans_marked' : 'marked';
                    this.saveAnswer(q.status);
                    this.moveToNext();
                },

                clearResponse() {
                    this.currentQuestion.selected_option = null;
                    this.currentQuestion.status = 'not_answered';
                    this.saveAnswer('not_answered');
                },

                selectOption(idx) {
                    // Handle Radio vs Checkbox based on type (Logic added just in case, simplified for now)
                    this.currentQuestion.selected_option = idx;
                },

                isSelected(idx) {
                    return this.currentQuestion && this.currentQuestion.selected_option === idx;
                },

                moveToNext() {
                    if (this.currentQuestionIndex < this.currentSectionQuestions.length - 1) {
                        this.currentQuestionIndex++;
                        this.questionStartTime = Date.now();
                        this.checkTranslation();
                    } else if (this.currentSectionIndex < this.sectionsMeta.length - 1) {
                        Swal.fire({
                            title: 'Section Completed', text: "Go to next section?",
                            showCancelButton: true, confirmButtonText: 'Yes'
                        }).then(r => { if(r.isConfirmed) this.switchSection(this.currentSectionIndex + 1); });
                    }
                },

                // --- TRANSLATION ---
                handleLangChange() { this.checkTranslation(); },

                getQuestionText() {
                    if(!this.currentQuestion) return '';
                    return this.currentQuestion.text[this.currentLang] || this.currentQuestion.text['en'];
                },

                getOptions() {
                    if(!this.currentQuestion) return [];
                    return this.currentQuestion.options[this.currentLang] || this.currentQuestion.options['en'];
                },

                async checkTranslation() {
                    if(this.currentLang === 'en') return;
                    if(this.currentQuestion.text[this.currentLang]) return;

                    this.isTranslating = true;
                    try {
                        let textUrl = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(this.currentQuestion.text['en'])}&langpair=en|${this.currentLang}`;
                        let res = await fetch(textUrl);
                        let data = await res.json();
                        this.currentQuestion.text[this.currentLang] = data.responseData.translatedText;
                    } catch(e) {}
                    this.isTranslating = false;
                },

                // --- UTILS ---
                formatTime(s) {
                    if (s < 0) s = 0;
                    return new Date(s * 1000).toISOString().substr(11, 8);
                },

                getPaletteClass(q, idx) {
                    let cls = "";
                    // Status mapping for Palette Colors
                    if (q.status === 'not_visited' || !q.status) cls = 'bg-white border';
                    else if (q.status === 'not_answered' || q.status === 'visited') cls = 'st-not-answered';
                    else if (q.status === 'answered') cls = 'st-answered';
                    else if (q.status === 'marked' || q.status === 'mark_for_review') cls = 'st-marked';
                    else if (q.status === 'ans_marked' || q.status === 'answered_mark_for_review') cls = 'st-ans-marked';

                    if (idx === this.currentQuestionIndex) cls += ' ring-2 ring-blue-500 ring-offset-2';
                    return cls;
                },

                handleViolation() {
                    this.warningCount++;
                    if(this.warningCount >= 3) {
                        fetch(suspendUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(r => r.json()).then(d => window.location.href = d.redirect);
                    } else {
                        Swal.fire("Warning", `Do not switch tabs! Warning ${this.warningCount}/3`, "warning");
                    }
                },

                finishExam(auto = false) {
                    if(!auto) {
                        Swal.fire({
                            title: "Submit Exam?", text: "Are you sure?", icon: "warning",
                            showCancelButton: true, confirmButtonText: "Submit"
                        }).then(r => { if(r.isConfirmed) this.submitData(); });
                    } else {
                        this.submitData();
                    }
                },

                submitData() {
                    fetch(finishUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    }).then(r => r.json()).then(d => window.location.href = d.redirect);
                }
            }
        }
    </script>
</body>
</html>
