import sys

file_path = r'c:\xampp_old\htdocs\Digi_Laravel_Prrojects\Exam-babu-new\exam_babu_live\resources\views\student\exams\dashboard.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target1 = """                {{-- Horizontal Scrollable Grid for this Plan --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($section['exams'] as $exam)"""

replacement1 = """                {{-- Group Exams by Subject --}}
                @php
                    $groupedExams = $section['exams']->groupBy(function($exam) {
                        return $exam->subCategory->name ?? 'Uncategorized';
                    });
                @endphp

                @foreach($groupedExams as $subjectName => $subjectExams)
                    <div class="mb-8 last:mb-0">
                        <h4 class="mb-4 text-lg font-bold text-slate-800 border-b border-slate-200 pb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[var(--brand-blue)]"></span>
                            {{ $subjectName }}
                        </h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($subjectExams as $exam)"""

target2 = """                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach"""

replacement2 = """                        </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach"""

if target1 in content and target2 in content:
    content = content.replace(target1, replacement1)
    content = content.replace(target2, replacement2)
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Replacement successful.")
else:
    print("Target content not found in file.")
    if target1 not in content:
        print("target1 not found")
    if target2 not in content:
        print("target2 not found")
