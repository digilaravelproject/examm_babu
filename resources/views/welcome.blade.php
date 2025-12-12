<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exam Babu - India's #1 Learning Platform</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Brand Colors */
            --primary: #2563eb;
            /* Blue-600 */
            --primary-dark: #1d4ed8;
            /* Blue-700 */
            --primary-light: #eff6ff;
            /* Blue-50 */
            --secondary: #0f172a;
            /* Slate-900 */
            --accent: #f59e0b;
            /* Amber-500 */
            --success: #10b981;
            /* Emerald-500 */
            --danger: #ef4444;
            /* Red-500 */

            /* Backgrounds */
            --bg-body: #ffffff;
            --bg-card: #ffffff;
            --bg-glass: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--secondary);
            overflow-x: hidden;
            position: relative;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Outfit', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Global Background Animation */
        .global-bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            filter: blur(60px);
            opacity: 0.4;
            animation: float 15s infinite ease-in-out;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -50px) rotate(10deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(-5deg);
            }
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .card-3d {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-3d:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .mega-menu-enter {
            animation: slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Testbook Style Specifics */
        .live-dot {
            height: 8px;
            width: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Stats Card specific styling for Light Mode */
        .stats-card-light {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .stats-card-light:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1), 0 4px 6px -2px rgba(37, 99, 235, 0.05);
        }
    </style>
</head>

<body class="antialiased selection:bg-blue-100 selection:text-blue-900">

    <!-- Global Background Animation Elements -->
    <div class="global-bg-animation">
        <div
            class="floating-shape bg-blue-300 w-96 h-96 rounded-full top-[-10%] left-[-10%] mix-blend-multiply opacity-30">
        </div>
        <div class="floating-shape bg-purple-300 w-96 h-96 rounded-full top-[20%] right-[-10%] mix-blend-multiply opacity-30"
            style="animation-delay: -5s"></div>
        <div class="floating-shape bg-pink-300 w-96 h-96 rounded-full bottom-[-10%] left-[20%] mix-blend-multiply opacity-30"
            style="animation-delay: -10s"></div>
        <div class="floating-shape bg-yellow-200 w-64 h-64 rounded-full bottom-[40%] right-[30%] mix-blend-multiply opacity-20"
            style="animation-delay: -2s"></div>
    </div>

    {{-- PHP DATA BLOCK --}}
    @php
        // 1. MEGA MENU DATA (Correct Structured)
        // Grouped by Category -> Subcategories (if any) -> Exams
        $examCategories = [
            'Police Exams' => [
                'icon' => '👮',
                'grouped' => true,
                'groups' => [
                    'Delhi Police' => [
                        'Delhi Police Constable',
                        'Delhi Police Head Constable',
                        'Delhi Police Driver',
                        'Delhi Police MTS',
                    ],
                    'Uttar Pradesh Police' => [
                        'UP Police SI',
                        'UP Police Constable',
                        'UP Police ASI',
                        'UP Police Assistant Operator',
                        'UP Police Head Operator',
                        'UP Police Jail Warder',
                    ],
                    'Bihar Police' => [
                        'Bihar Police SI',
                        'Bihar Police Constable',
                        'Bihar Police Prohibition SI',
                        'Bihar Police Fireman',
                        'Bihar Police Forest Guard',
                    ],
                    'Rajasthan Police' => ['Rajasthan Police SI', 'Rajasthan Police Constable', 'Rajasthan Home Guard'],
                    'MP & CG Police' => [
                        'MP Police Constable',
                        'MP Police ASI',
                        'MP Police SI',
                        'CG Police Constable',
                        'CG Police SI',
                    ],
                    'Haryana Police' => [
                        'HSSC Haryana Police Constable',
                        'Haryana Police SI',
                        'Haryana Police Commando',
                    ],
                    'Maharashtra Police' => ['Maharashtra Police Constable', 'MPSC PSI'],
                    'West Bengal Police' => [
                        'Kolkata Police Constable',
                        'WB Police Constable',
                        'WB Police SI',
                        'WB Police Warder',
                    ],
                    'Odisha Police' => [
                        'Odisha Police Constable',
                        'Odisha Police SI',
                        'Odisha Police Jail Warder',
                        'Odisha Police Home Guard',
                    ],
                    'Gujarat Police' => [
                        'Gujarat Police Constable',
                        'Gujarat Police LRB Constable',
                        'Gujarat Police Jail Sepoy',
                        'Gujarat Police ASI',
                    ],
                    'Punjab Police' => [
                        'Punjab Police Jail Warder',
                        'Punjab Police Constable',
                        'Punjab Police SI',
                        'Punjab Police Head Constable',
                    ],
                    'South India' => [
                        'TNUSRB SI',
                        'TNUSRB Constable',
                        'KSP SI',
                        'KSP Constable',
                        'AP Police Constable',
                        'Telangana Police SI',
                    ],
                    'Hill States' => [
                        'Uttarakhand Police SI',
                        'UK Police Constable',
                        'HP Police Constable',
                        'JKSSB Sub Inspector',
                    ],
                    'North East' => [
                        'Assam Police Constable',
                        'Assam Police SI',
                        'Sikkim Police SI',
                        'Meghalaya Police Constable',
                    ],
                    'Other / UT' => ['Chandigarh Police Constable', 'Goa Police Constable', 'DSSSB Jail Warder'],
                ],
            ],
            'SSC Exams' => [
                'icon' => '🏛️',
                'exams' => [
                    'SSC CGL',
                    'SSC CHSL',
                    'SSC MTS',
                    'SSC CPO',
                    'SSC GD Constable',
                    'SSC Stenographer',
                    'SSC Selection Post',
                    'SSC JE 2025',
                    'SSC Havaldar',
                    'Delhi Police Driver',
                ],
            ],
            'Banking Exams' => [
                'icon' => '🏦',
                'exams' => [
                    'SBI PO',
                    'SBI Clerk',
                    'IBPS PO',
                    'IBPS Clerk',
                    'RBI Grade B',
                    'IBPS RRB Clerk',
                    'IBPS RRB PO',
                    'IBPS SO',
                    'RBI Assistant',
                    'JAIIB',
                    'CAIIB',
                    'SEBI Grade A',
                ],
            ],
            'Teaching Exams' => [
                'icon' => '👨‍🏫',
                'exams' => [
                    'CTET 2025',
                    'UGC NET 2025',
                    'CSIR NET 2025',
                    'KVS',
                    'NVS',
                    'Super TET',
                    'DSSSB PGT',
                    'Bihar Primary Teacher',
                    'Bihar Secondary Teacher',
                    'Bihar Senior Secondary Teacher',
                    'EMRS TGT',
                    'TN TRB Assistant Professor',
                    'REET',
                ],
            ],
            'Civil Services' => [
                'icon' => '🇮🇳',
                'exams' => [
                    'UPSC Civil Services 2025',
                    'UPSC CAPF AC',
                    'UPSC NDA',
                    'CDS Exam',
                    'UPPSC RO ARO',
                    'BPSC AEDO',
                    'MPSC',
                    'MPPSC',
                    'RPSC',
                    'JPSC',
                ],
            ],
            'Railways Exams' => [
                'icon' => '🚆',
                'exams' => [
                    'RRB Group D 2025',
                    'RRB NTPC',
                    'RRB ALP',
                    'RPF SI',
                    'RPF Constable',
                    'RRB JE',
                    'RRB Technician',
                    'RRB Section Controller',
                    'RRB Staff Nurse 2024',
                ],
            ],
            'Engineering' => [
                'icon' => '🏗️',
                'exams' => [
                    'GATE Exam 2025',
                    'NHPC JE',
                    'DFCCIL Executive',
                    'ISRO Scientist',
                    'DRDO STA',
                    'BARC',
                    'AIIMS CRE 2025',
                    'IB Junior Intelligence Officer',
                    'BSSC Inter Level',
                ],
            ],
            'Defence Exams' => [
                'icon' => '🎖️',
                'exams' => [
                    'Indian Army Agniveer 2025',
                    'Indian Airforce Agniveer',
                    'AFCAT',
                    'Chandigarh Police Constable',
                    'MP Police ASI',
                    'MP Police Constable',
                    'TNUSRB SI 2025',
                ],
            ],
            'State Govt' => [
                'icon' => '🏙️',
                'exams' => [
                    'UPSSSC VDO 2025',
                    'UPSSSC Junior Assistant',
                    'Haryana CET Group D',
                    'Rajasthan Judiciary 2025',
                    'OSSC CGL',
                    'OSSC CHSL',
                    'TS ICET 2025',
                    'Bihar Jeevika',
                    'Patna High Court Mazdoor',
                    'BSPHCL Store Assistant',
                ],
            ],
            'Insurance Exams' => [
                'icon' => '🛡️',
                'exams' => ['LIC AAO', 'LIC ADO', 'NIACL AO', 'UIIC Assistant', 'ESIC SSO'],
            ],
            'Nursing Exams' => [
                'icon' => '🏥',
                'exams' => ['AIIMS NORCET', 'RRB Staff Nurse', 'ESIC Staff Nurse', 'CHO Exams', 'NEET'],
            ],
            'MBA Entrance' => ['icon' => '🎓', 'exams' => ['CAT 2025', 'XAT', 'SNAP', 'NMAT', 'CMAT', 'MAT', 'IIFT']],
            'CUET & UG' => [
                'icon' => '🎒',
                'exams' => ['CUET 2025', 'IPMAT', 'NPAT', 'SET', 'Christ University Entrance'],
            ],
            'Judiciary Exams' => [
                'icon' => '⚖️',
                'exams' => [
                    'Delhi Judiciary',
                    'UP Judiciary',
                    'Bihar Judiciary',
                    'Rajasthan Judiciary',
                    'MP Judiciary',
                ],
            ],
            'Regulatory Body' => [
                'icon' => '📉',
                'exams' => ['SEBI Grade A', 'RBI Grade B', 'NABARD Grade A', 'PFRDA Grade A', 'IFSCA Grade A'],
            ],
            'Other Govt Exams' => [
                'icon' => '📜',
                'exams' => ['Intelligence Bureau (IB)', 'FCI', 'EPFO SSA', 'NRA CET', 'High Court Exams'],
            ],
        ];

        // 2. POPULAR TEST SERIES (Detailed Cards)
        $popularTestSeries = [
            [
                'title' => 'SSC GD Constable 2026 Mock Test Series',
                'users' => '285.9k',
                'total_tests' => '779',
                'free_tests' => '11',
                'languages' => ['English', 'Hindi', 'Marathi', 'Telugu', 'Tamil', '+4 More'],
                'features' => ['1 Scholarship Test', '7 🟢 Live Test', '45 SSC CGL 2025 Similar PYP'],
                'more_count' => '+726 more tests',
            ],
            [
                'title' => 'SSC CPO Mock Test Series 2025 (Tier I & II)',
                'subtitle' => '(DP SI & CAPF) (New Pattern)',
                'users' => '488.3k',
                'total_tests' => '1809',
                'free_tests' => '6',
                'languages' => ['English', 'Hindi'],
                'features' => ['3 🟢 Exam Day Special', '1 🔴 Live Test', '66 PYP - Tier I (New Pattern)'],
                'more_count' => '+1739 more tests',
            ],
            [
                'title' => 'RRB Group D Mock Test Series 2024-25',
                'subtitle' => '(Updated Pattern)',
                'users' => '2291.8k',
                'total_tests' => '2104',
                'free_tests' => '48',
                'languages' => ['English', 'Hindi', 'Bengali', 'Marathi', '+7 More'],
                'features' => [
                    '6 Official Mock Based Full Test',
                    '24 Exam Day Special',
                    '158 विज्ञान Express Mahapack',
                ],
                'more_count' => '+1916 more tests',
            ],
            [
                'title' => 'Delhi Police Constable (Executive) 2025',
                'users' => '1002.4k',
                'total_tests' => '1163',
                'free_tests' => '30',
                'languages' => ['English', 'Hindi'],
                'features' => ['29 🔴 Ultimate Live Test', '17 रक्षक Revision Series', '146 Most Saved CTs'],
                'more_count' => '+971 more tests',
            ],
        ];

        // 3. STATS DATA (Light Theme Icons)
        $stats = [
            [
                'count' => '53,567',
                'label' => 'Total Selections',
                'icon' => '🏆',
                'color' => 'text-yellow-600',
                'bg' => 'bg-yellow-100',
            ],
            [
                'count' => '19,054',
                'label' => 'Selections in SSC',
                'icon' => '🏛️',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-100',
            ],
            [
                'count' => '18,921',
                'label' => 'Selections in Banking',
                'icon' => '🏦',
                'color' => 'text-green-600',
                'bg' => 'bg-green-100',
            ],
            [
                'count' => '7,087',
                'label' => 'Selections in Railways',
                'icon' => '🚆',
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-100',
            ],
            [
                'count' => '8,505',
                'label' => 'Other Govt Exams',
                'icon' => '🎖️',
                'color' => 'text-purple-600',
                'bg' => 'bg-purple-100',
            ],
        ];

        // 4. MOCK TESTS TABS
        $popularTabs = ['Engineering', 'Civil Services', 'Banking', 'Teaching', 'SSC', 'Railways'];

        $mockTests = [
            'Engineering' => [
                [
                    'title' => 'AE SE Group A Mock Test 2',
                    'subtitle' => 'Revised Pattern April 25',
                    'price' => 100,
                    'users' => '12.5k',
                    'tags' => ['Civil', 'MPSC'],
                ],
                [
                    'title' => 'BMC SUB ENGINEER (Civil)',
                    'subtitle' => 'Full Length Test Series',
                    'price' => 100,
                    'users' => '8.2k',
                    'tags' => ['BMC', 'Civil'],
                ],
                [
                    'title' => 'JUNIOR ENGINEER MOCK TEST 1',
                    'subtitle' => 'Comprehensive JE Pack',
                    'price' => 200,
                    'users' => '25k',
                    'tags' => ['JE', 'Tech'],
                ],
                [
                    'title' => 'GATE ME 2026 Foundation',
                    'subtitle' => 'Chapter-wise Tests',
                    'price' => 499,
                    'users' => '5k',
                    'tags' => ['GATE', 'Mech'],
                ],
                [
                    'title' => 'RRB JE Electrical',
                    'subtitle' => 'Previous Year Papers',
                    'price' => 150,
                    'users' => '18k',
                    'tags' => ['RRB', 'Elec'],
                ],
                [
                    'title' => 'SSC JE Civil Mains',
                    'subtitle' => 'Mains Special Batch',
                    'price' => 299,
                    'users' => '9k',
                    'tags' => ['SSC', 'Civil'],
                ],
            ],
            'Civil Services' => [
                [
                    'title' => 'MPSC Rajyaseva Prelims',
                    'subtitle' => 'GS Paper 1 + CSAT',
                    'price' => 299,
                    'users' => '50k',
                    'tags' => ['MPSC', 'GS'],
                ],
                [
                    'title' => 'UPSC CSE GS Mock 1',
                    'subtitle' => 'All India Rank Test',
                    'price' => 0,
                    'users' => '1.2L',
                    'tags' => ['UPSC', 'Free'],
                ],
                [
                    'title' => 'BPSC 70th Prelims',
                    'subtitle' => 'Bihar Special GK Included',
                    'price' => 199,
                    'users' => '30k',
                    'tags' => ['BPSC', 'State'],
                ],
                [
                    'title' => 'UPPSC RO/ARO Series',
                    'subtitle' => 'Hindi + GS',
                    'price' => 149,
                    'users' => '22k',
                    'tags' => ['UPPSC', 'RO'],
                ],
            ],
            'Banking' => [
                [
                    'title' => 'SBI PO Prelims 2025',
                    'subtitle' => 'Speed Booster Tests',
                    'price' => 199,
                    'users' => '85k',
                    'tags' => ['SBI', 'PO'],
                ],
                [
                    'title' => 'IBPS Clerk Mains',
                    'subtitle' => 'Full Length Mocks',
                    'price' => 149,
                    'users' => '60k',
                    'tags' => ['IBPS', 'Clerk'],
                ],
                [
                    'title' => 'RBI Grade B Phase 1',
                    'subtitle' => 'General Awareness Special',
                    'price' => 399,
                    'users' => '15k',
                    'tags' => ['RBI', 'Officer'],
                ],
                [
                    'title' => 'RRB Office Assistant',
                    'subtitle' => 'Prelims + Mains',
                    'price' => 129,
                    'users' => '45k',
                    'tags' => ['RRB', 'Assistant'],
                ],
            ],
            'default' => [
                [
                    'title' => 'General Awareness Booster',
                    'subtitle' => 'Current Affairs 2025',
                    'price' => 49,
                    'users' => '2L',
                    'tags' => ['GK', 'All Exams'],
                ],
                [
                    'title' => 'Quantitative Aptitude',
                    'subtitle' => 'Topic Wise Tests',
                    'price' => 99,
                    'users' => '1.5L',
                    'tags' => ['Maths', 'Practice'],
                ],
                [
                    'title' => 'English Language Master',
                    'subtitle' => 'Grammar + Vocab',
                    'price' => 99,
                    'users' => '1.2L',
                    'tags' => ['English', 'Lang'],
                ],
                [
                    'title' => 'Reasoning Ability',
                    'subtitle' => 'Puzzle & Seating Arrangement',
                    'price' => 99,
                    'users' => '1.3L',
                    'tags' => ['Logic', 'Reasoning'],
                ],
            ],
        ];

        // 5. SEO LINKS FOOTER DATA (Full Test Series List)
        $allTestSeries = [
            'Popular' => [
                'JEE Main Mock Test 2025',
                'CUET Mock Test 2025',
                'NEET Mock Test 2025',
                'SSC GD Constable Mock Test',
                'RRB NTPC Mock Test',
                'IBPS Clerk Mock Test',
                'NDA Mock Test',
            ],
            'Engineering' => [
                'JEE Advanced Mock Test 2025',
                'GATE 2025',
                'NHPC JE',
                'DFCCIL Executive',
                'ISRO Scientist',
                'BARC',
                'DRDO STA',
                'NIMCET',
                'COMEDK UGET',
                'KCET',
                'WB JEE',
                'TCS NQT',
            ],
            'Banking' => [
                'SBI PO',
                'IBPS PO',
                'IBPS RRB PO',
                'RBI Grade B',
                'LIC AAO',
                'NABARD Development Assistant',
                'ECGC PO',
                'RBI Assistant',
                'BSPHCL Junior Accounts Clerk',
                'LIC ADO',
            ],
            'SSC & Railways' => [
                'SSC CGL',
                'SSC CHSL',
                'SSC MTS',
                'SSC CPO',
                'SSC Stenographer',
                'RRB Group D',
                'RPF SI',
                'RPF Constable',
                'Delhi Police Driver',
                'SSC Selection Post',
            ],
            'Teaching' => [
                'CTET Mock Test 2025',
                'UGC NET Paper 1',
                'CSIR NET Life Science',
                'KVS',
                'NVS',
                'REET',
                'UPTET',
                'Bihar Teacher',
                'EMRS TGT',
                'UGC NET English',
                'UGC NET Commerce',
            ],
            'State Exams' => [
                'UPSSSC Junior Assistant',
                'BPSC AEDO',
                'MP GK',
                'RPSC',
                'MPSC',
                'Haryana CET',
                'Bihar Police',
                'UP Police',
                'Rajasthan Judiciary',
                'OSSC CGL',
                'BSSC Inter Level',
            ],
        ];
    @endphp

    <!-- Navbar with Mega Menu -->
    <header x-data="{ mobileOpen: false, scrolled: false, megaMenu: null }" @scroll.window="scrolled = (window.pageYOffset > 20)"
        class="fixed top-0 z-50 w-full transition-all duration-300"
        :class="scrolled ? 'glass-nav shadow-sm py-2' : 'bg-transparent py-4'">

        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Left: Logo & Mega Menu Trigger -->
                <div class="flex items-center gap-8">
                    <!-- Logo -->
                    <a href="/" class="flex items-center gap-2 group">
                        <div
                            class="flex items-center justify-center w-10 h-10 text-xl font-extrabold text-white transition-transform duration-300 shadow-lg bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl group-hover:rotate-12">
                            E
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-slate-800">Exam<span
                                class="text-blue-600">Babu</span></span>
                    </a>

                    <!-- Desktop Mega Menu Trigger -->
                    <nav class="hidden gap-1 md:flex">
                        <!-- Exams Mega Menu -->
                        <div class="relative" @mouseenter="megaMenu = 'exams'" @mouseleave="megaMenu = null">
                            <button
                                class="flex items-center gap-1 px-4 py-2 text-sm font-bold transition-all rounded-full text-slate-700 hover:text-blue-600 hover:bg-slate-100"
                                :class="megaMenu === 'exams' ? 'bg-slate-100 text-blue-600' : ''">
                                Exams
                                <svg class="w-4 h-4 transition-transform duration-300"
                                    :class="megaMenu === 'exams' ? 'rotate-180 text-blue-600' : 'text-slate-400'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- The Mega Menu Container -->
                            <!-- FIX APPLIED HERE: x-data moved to parent container -->
                            <div x-show="megaMenu === 'exams'" x-data="{ activeCat: 'Police Exams' }"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-4"
                                class="absolute left-0 top-full mt-2 w-[950px] bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50 flex mega-menu-enter h-[600px]"
                                style="left: -150px;">

                                <!-- Left Sidebar (Categories) -->
                                <!-- FIX APPLIED HERE: x-data removed from this div -->
                                <div class="w-1/3 py-3 overflow-y-auto border-r bg-slate-50 border-slate-100">
                                    @foreach ($examCategories as $catName => $data)
                                        <button @mouseenter="activeCat = '{{ $catName }}'"
                                            class="flex items-center w-full gap-3 px-5 py-3 text-sm font-bold text-left transition-all duration-200 border-l-4"
                                            :class="activeCat === '{{ $catName }}' ?
                                                'bg-white text-blue-700 border-blue-600 shadow-sm' :
                                                'text-slate-600 border-transparent hover:bg-slate-100'">
                                            <span class="text-base">{{ $data['icon'] }}</span>
                                            {{ $catName }}
                                            <svg x-show="activeCat === '{{ $catName }}'"
                                                class="w-4 h-4 ml-auto text-blue-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Right Content (Exams Grid) -->
                                <div class="w-2/3 p-6 overflow-y-auto bg-white">
                                    @foreach ($examCategories as $catName => $data)
                                        <div x-show="activeCat === '{{ $catName }}'" class="flex flex-col h-full">
                                            <div
                                                class="flex items-center justify-between pb-2 mb-6 border-b border-slate-100">
                                                <h3
                                                    class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                                                    {{ $data['icon'] }} Popular {{ $catName }}
                                                </h3>
                                                <a href="#"
                                                    class="flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">View
                                                    All <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg></a>
                                            </div>

                                            <!-- Check if Grouped (Like Police Exams) -->
                                            @if (isset($data['grouped']) && $data['grouped'])
                                                <div class="space-y-6">
                                                    @foreach ($data['groups'] as $groupName => $exams)
                                                        <div>
                                                            <h4
                                                                class="pb-1 mb-2 text-xs font-bold tracking-wider uppercase border-b text-slate-400 border-slate-100">
                                                                {{ $groupName }}</h4>
                                                            <div class="grid grid-cols-2 gap-2">
                                                                @foreach ($exams as $exam)
                                                                    <a href="#"
                                                                        class="flex items-center gap-2 p-2 transition-all rounded hover:bg-blue-50 group">
                                                                        <div
                                                                            class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-500 transition-colors">
                                                                        </div>
                                                                        <span
                                                                            class="text-sm font-medium truncate text-slate-700 group-hover:text-blue-700"
                                                                            title="{{ $exam }}">{{ $exam }}</span>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="grid content-start grid-cols-2 gap-3">
                                                    @foreach ($data['exams'] as $exam)
                                                        <a href="#"
                                                            class="flex items-center gap-3 p-3 transition-all border border-transparent rounded-xl hover:bg-blue-50 hover:border-blue-100 group">
                                                            <div
                                                                class="flex items-center justify-center w-8 h-8 text-xs font-bold transition-colors rounded-lg shrink-0 bg-slate-100 text-slate-500 group-hover:bg-blue-600 group-hover:text-white">
                                                                {{ substr($exam, 0, 1) }}
                                                            </div>
                                                            <span
                                                                class="text-sm font-semibold text-slate-700 group-hover:text-blue-700">{{ $exam }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <a href="#"
                            class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:text-blue-600 hover:bg-slate-100">Test
                            Series</a>
                        <a href="#"
                            class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:text-blue-600 hover:bg-slate-100">Super
                            Coaching</a>
                        <a href="#"
                            class="px-4 py-2 text-sm font-bold transition-colors rounded-full text-slate-700 hover:text-blue-600 hover:bg-slate-100">Pass
                            Pro</a>
                    </nav>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-4">
                    <div class="relative hidden lg:flex group">
                        <input type="text" placeholder="Search exams..."
                            class="pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent rounded-full text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-blue-200 w-64 transition-all duration-300 shadow-inner">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5 group-focus-within:text-blue-500 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="font-bold text-slate-700 hover:text-blue-600">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="hidden px-4 text-sm font-bold sm:block text-slate-700 hover:text-blue-600">Log in</a>
                        <a href="{{ route('register') }}"
                            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-blue-500/30 hover:-translate-y-0.5 transition-all transform">Get
                            Started</a>
                    @endauth

                    <!-- Mobile Hamburger -->
                    <button @click="mobileOpen = !mobileOpen" class="p-2 md:hidden text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div x-show="mobileOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto bg-white md:hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100">
                <span class="text-xl font-bold">Menu</span>
                <button @click="mobileOpen = false" class="p-2"><svg class="w-6 h-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
            </div>
            <div class="p-4 space-y-4">
                <a href="#" class="block text-lg font-medium text-slate-800">Exams</a>
                <a href="#" class="block text-lg font-medium text-slate-800">Test Series</a>
                <a href="#" class="block text-lg font-medium text-slate-800">Super Coaching</a>
                <div class="h-px my-4 bg-slate-100"></div>
                <a href="{{ route('login') }}"
                    class="block w-full py-3 font-bold text-center border border-slate-200 rounded-xl">Log in</a>
                <a href="{{ route('register') }}"
                    class="block w-full py-3 font-bold text-center text-white bg-blue-600 rounded-xl">Sign up Free</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative z-10 px-4 pt-32 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <!-- Left: Text -->
                <div class="space-y-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 200)">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-blue-700 border border-blue-200 rounded-full shadow-sm bg-white/80 backdrop-blur-md">
                        <span class="live-dot"></span> #1 Trusted Exam Platform
                    </div>

                    <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 leading-[1.1] tracking-tight"
                        x-show="show" x-transition:enter="transition ease-out duration-1000"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Crack Your <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">Dream
                            Job</span>
                    </h1>

                    <p class="max-w-lg text-xl font-medium leading-relaxed text-slate-600" x-show="show"
                        x-transition:enter="transition ease-out duration-1000 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Join <b>2 Crore+ students</b> preparing for SSC, Banking, Railways & Engineering exams with
                        India's best Super Teachers.
                    </p>

                    <div class="flex flex-col gap-4 sm:flex-row" x-show="show"
                        x-transition:enter="transition ease-out duration-1000 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="{{ route('register') }}"
                            class="px-8 py-4 text-lg font-bold text-center text-white transition-all bg-blue-600 shadow-xl hover:bg-blue-700 rounded-xl shadow-blue-500/30 hover:shadow-2xl hover:-translate-y-1">
                            Start Free Mock Test
                        </a>
                    </div>
                </div>

                <!-- Right: Hero Card Slider -->
                <div class="hidden lg:block h-[450px] relative w-full" x-data="{ active: 0 }"
                    x-init="setInterval(() => active = (active + 1) % 3, 3500)">
                    <!-- Slide 1 -->
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 0 ? 'opacity-100 translate-x-0 scale-100 z-30' :
                            'opacity-0 translate-x-10 scale-95 z-0'">
                        <div
                            class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-[2rem] p-10 shadow-2xl text-white h-full relative overflow-hidden card-3d flex flex-col justify-center border-0">
                            <!-- Background SVG/Graphic -->
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full text-white" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="M10 10 H90 V90 H10 Z" fill="none" stroke="currentColor"
                                        stroke-width="0.5" />
                                    <circle cx="50" cy="50" r="30" fill="none"
                                        stroke="currentColor" stroke-width="0.5" />
                                    <path d="M50 20 L80 80 L20 80 Z" fill="none" stroke="currentColor"
                                        stroke-width="0.5" />
                                </svg>
                            </div>
                            <!-- Floating Icons for Animation -->
                            <div class="absolute text-6xl top-10 right-10 opacity-30 animate-bounce"
                                style="animation-duration: 3s">🏛️</div>
                            <div class="absolute text-5xl bottom-10 right-20 opacity-30 animate-pulse">🇮🇳</div>

                            <span
                                class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">TRENDING
                                NOW</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">SSC CGL 2025</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-indigo-100">Target 350+ Score with
                                India's most attempted mock series.</p>
                            <button
                                class="relative z-10 px-6 py-3 font-bold text-indigo-700 transition bg-white shadow-lg rounded-xl w-fit hover:bg-indigo-50">View
                                Test Series</button>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 1 ? 'opacity-100 translate-x-0 scale-100 z-30' :
                            'opacity-0 translate-x-10 scale-95 z-0'">
                        <div
                            class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-[2rem] p-10 shadow-2xl text-white h-full relative overflow-hidden card-3d flex flex-col justify-center border-0">
                            <!-- Background SVG/Graphic -->
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full text-white" viewBox="0 0 100 100" fill="none"
                                    stroke="currentColor">
                                    <path d="M0 50 Q50 0 100 50 T200 50" stroke-width="1" fill="none" />
                                    <circle cx="20" cy="20" r="10" stroke-width="1" />
                                    <rect x="60" y="60" width="20" height="20" stroke-width="1" />
                                </svg>
                            </div>
                            <!-- Floating Icons for Animation -->
                            <div class="absolute text-6xl top-20 right-10 opacity-30 animate-bounce"
                                style="animation-duration: 4s">🚆</div>
                            <div class="absolute text-5xl bottom-20 left-10 opacity-30 animate-pulse">🔧</div>

                            <span
                                class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">NEW
                                BATCH</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">RRB ALP 2025</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-blue-100">Complete Technical + Non-Tech
                                coverage for Assistant Loco Pilot.</p>
                            <button
                                class="relative z-10 px-6 py-3 font-bold text-blue-700 transition bg-white shadow-lg rounded-xl w-fit hover:bg-blue-50">Enroll
                                Now</button>
                        </div>
                    </div>
                    <!-- Slide 3 -->
                    <div class="absolute inset-0 transition-all duration-700 ease-out"
                        :class="active === 2 ? 'opacity-100 translate-x-0 scale-100 z-30' :
                            'opacity-0 translate-x-10 scale-95 z-0'">
                        <div
                            class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-[2rem] p-10 shadow-2xl text-white h-full relative overflow-hidden card-3d flex flex-col justify-center border-0">
                            <!-- Background SVG/Graphic -->
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3 3h18v18H3z" fill="none" stroke="currentColor"
                                        stroke-width="0.5" />
                                    <path d="M12 2L2 22h20L12 2z" fill="none" stroke="currentColor"
                                        stroke-width="0.5" />
                                </svg>
                            </div>
                            <!-- Floating Icons for Animation -->
                            <div class="absolute text-6xl top-10 right-20 opacity-30 animate-bounce"
                                style="animation-duration: 2.5s">🏦</div>
                            <div class="absolute text-5xl bottom-10 right-10 opacity-30 animate-pulse">📊</div>

                            <span
                                class="bg-white/20 backdrop-blur w-fit text-xs font-bold py-1.5 px-4 rounded-full border border-white/20 mb-6 relative z-10">ADMISSIONS
                                OPEN</span>
                            <h3 class="relative z-10 mb-4 text-4xl font-bold">Banking Elite</h3>
                            <p class="relative z-10 max-w-xs mb-8 text-lg text-emerald-100">One Pass for SBI PO, IBPS
                                PO, Clerk & RBI Grade B.</p>
                            <button
                                class="relative z-10 px-6 py-3 font-bold transition bg-white shadow-lg text-emerald-700 rounded-xl w-fit hover:bg-emerald-50">Get
                                Started</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Exams with Tabs (Dynamic & Interactive) -->
    <section class="py-20 bg-white" x-data="{ currentTab: 'Engineering' }">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-extrabold lg:text-4xl text-slate-900">Popular Mock Tests</h2>
                <p class="text-lg text-slate-500">Attempt free mock tests curated by experts.</p>
            </div>

            <!-- Tabs -->
            <div class="flex flex-wrap justify-center gap-2 mb-12">
                @foreach ($popularTabs as $tab)
                    <button @click="currentTab = '{{ $tab }}'"
                        class="px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300"
                        :class="currentTab === '{{ $tab }}' ?
                            'bg-blue-600 text-white shadow-lg shadow-blue-500/30 scale-105' :
                            'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>

            <!-- Grid Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 min-h-[400px]">
                @foreach ($mockTests as $category => $tests)
                    <template
                        x-if="currentTab === '{{ $category }}' || (currentTab !== '{{ $category }}' && !{{ json_encode(array_key_exists($category, $mockTests) && in_array($category, $popularTabs)) }} && '{{ $category }}' === 'default' && !['Engineering', 'Civil Services', 'Banking'].includes(currentTab))">
                        {{-- Logic placeholder for Alpine --}}
                    </template>
                @endforeach

                <!-- Engineering Tests -->
                @foreach ($mockTests['Engineering'] as $test)
                    <div x-show="currentTab === 'Engineering'" x-transition:enter="transition ease-out duration-300"
                        class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative flex-1 p-6">
                            <div
                                class="absolute top-0 right-0 p-4 transition-opacity opacity-10 group-hover:opacity-20">
                                <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                                </svg>
                            </div>
                            <div class="flex gap-2 mb-3">
                                @foreach ($test['tags'] as $tag)
                                    <span
                                        class="px-2 py-1 text-xs font-bold tracking-wider text-blue-600 uppercase rounded-md bg-blue-50">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <h3
                                class="mb-2 text-xl font-bold transition-colors text-slate-800 group-hover:text-blue-600">
                                {{ $test['title'] }}</h3>
                            <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                                <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg> 60 Mins</span>
                                <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg> {{ $test['users'] }} Users</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                            <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }} <span
                                    class="text-xs font-normal line-through text-slate-400">₹{{ $test['price'] * 2 }}</span>
                            </div>
                            <button
                                class="px-4 py-2 text-sm font-bold text-blue-600 transition-all bg-white border border-blue-600 rounded-lg shadow-sm hover:bg-blue-600 hover:text-white">Attempt
                                Now</button>
                        </div>
                    </div>
                @endforeach

                <!-- Civil Services Tests -->
                @foreach ($mockTests['Civil Services'] as $test)
                    <div x-show="currentTab === 'Civil Services'"
                        x-transition:enter="transition ease-out duration-300"
                        class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative flex-1 p-6">
                            <div
                                class="absolute top-0 right-0 p-4 transition-opacity opacity-10 group-hover:opacity-20">
                                <svg class="w-16 h-16 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                                </svg>
                            </div>
                            <div class="flex gap-2 mb-3">
                                @foreach ($test['tags'] as $tag)
                                    <span
                                        class="px-2 py-1 text-xs font-bold tracking-wider text-orange-600 uppercase rounded-md bg-orange-50">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <h3
                                class="mb-2 text-xl font-bold transition-colors text-slate-800 group-hover:text-orange-600">
                                {{ $test['title'] }}</h3>
                            <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                                <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg> 120 Mins</span>
                                <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg> {{ $test['users'] }} Users</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                            <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }} <span
                                    class="text-xs font-normal line-through text-slate-400">₹{{ $test['price'] * 2 }}</span>
                            </div>
                            <button
                                class="px-4 py-2 text-sm font-bold text-orange-600 transition-all bg-white border border-orange-600 rounded-lg shadow-sm hover:bg-orange-600 hover:text-white">Attempt
                                Now</button>
                        </div>
                    </div>
                @endforeach

                <!-- Fallback for other tabs (Using Default Data) -->
                <div x-show="!['Engineering', 'Civil Services'].includes(currentTab)"
                    class="py-12 text-center col-span-full">
                    <p class="mb-4 text-slate-400">Showing top picks for <span x-text="currentTab"
                            class="font-bold text-slate-600"></span></p>
                    <div class="grid grid-cols-1 gap-8 text-left md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($mockTests['default'] as $test)
                            <div
                                class="flex flex-col overflow-hidden transition-all duration-300 bg-white border shadow-sm group rounded-2xl border-slate-100 hover:shadow-xl hover:-translate-y-1">
                                <div class="relative flex-1 p-6">
                                    <div class="flex gap-2 mb-3">
                                        @foreach ($test['tags'] as $tag)
                                            <span
                                                class="px-2 py-1 text-xs font-bold tracking-wider text-purple-600 uppercase rounded-md bg-purple-50">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                    <h3
                                        class="mb-2 text-xl font-bold transition-colors text-slate-800 group-hover:text-purple-600">
                                        {{ $test['title'] }}</h3>
                                    <p class="mb-4 text-sm text-slate-500">{{ $test['subtitle'] }}</p>
                                </div>
                                <div
                                    class="flex items-center justify-between p-4 border-t border-slate-50 bg-slate-50/50">
                                    <div class="text-lg font-bold text-slate-900">₹{{ $test['price'] }}</div>
                                    <button
                                        class="px-4 py-2 text-sm font-bold text-purple-600 transition-all bg-white border border-purple-600 rounded-lg shadow-sm hover:bg-purple-600 hover:text-white">Attempt
                                        Now</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="mt-12 text-center">
                <button
                    class="px-8 py-3 font-bold transition bg-white border shadow-sm border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:border-slate-300">View
                    All Test Series</button>
            </div>
        </div>
    </section>

    <!-- Testbook Pass Banner Section -->
    <section class="px-4 py-12">
        <div class="mx-auto max-w-7xl">
            <div
                class="relative p-8 overflow-hidden text-white shadow-2xl bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl md:p-12">
                <div
                    class="absolute top-0 right-0 -mt-20 -mr-20 bg-blue-500 rounded-full w-96 h-96 opacity-20 blur-3xl">
                </div>

                <div class="relative z-10 grid items-center gap-8 md:grid-cols-2">
                    <div>
                        <div
                            class="inline-block px-3 py-1 mb-4 text-xs font-bold text-white transform rounded-md -rotate-2 bg-gradient-to-r from-amber-400 to-orange-500">
                            PREMIUM</div>
                        <h2 class="mb-4 text-3xl font-extrabold md:text-4xl">Enroll in Test Series for <span
                                class="text-blue-400">670+ exams</span></h2>
                        <p class="mb-8 text-lg text-slate-300">Get unlimited access to the most relevant Mock Tests on
                            India's Structured Online Test series platform.</p>
                        <button
                            class="px-8 py-3 font-bold text-white transition-all bg-blue-600 shadow-lg rounded-xl hover:bg-blue-700 shadow-blue-600/30 hover:scale-105">Explore
                            Testbook Pass</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">🏆</div>
                            <div class="text-sm font-bold">All India Rank</div>
                            <div class="text-xs text-slate-400">Compete with lakhs</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">📝</div>
                            <div class="text-sm font-bold">Latest Patterns</div>
                            <div class="text-xs text-slate-400">Updated questions</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">📊</div>
                            <div class="text-sm font-bold">In-depth Analysis</div>
                            <div class="text-xs text-slate-400">Performance report</div>
                        </div>
                        <div class="p-4 border bg-white/10 backdrop-blur-sm rounded-xl border-white/10">
                            <div class="mb-2 text-2xl">🗣️</div>
                            <div class="text-sm font-bold">Multilingual</div>
                            <div class="text-xs text-slate-400">English, Hindi + more</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Test Series Section (Detailed) -->
    <section class="relative py-20 bg-slate-50">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900">Popular Test Series</h2>
                    <p class="mt-2 text-slate-500">Attempt free tests from our most popular packages.</p>
                </div>
                <a href="#" class="items-center hidden gap-1 font-bold text-blue-600 md:flex hover:underline">
                    Explore all Test Series <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
                @foreach ($popularTestSeries as $series)
                    <div
                        class="p-6 transition-all duration-300 bg-white border shadow-sm rounded-2xl hover:shadow-xl border-slate-100 card-3d group">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3
                                    class="text-xl font-bold transition-colors text-slate-800 group-hover:text-blue-600">
                                    {{ $series['title'] }}</h3>
                                @if (isset($series['subtitle']))
                                    <p class="text-sm font-medium text-slate-500">{{ $series['subtitle'] }}</p>
                                @endif
                            </div>
                            <div
                                class="flex items-center gap-1 px-2 py-1 text-xs font-bold text-green-700 border border-green-100 rounded bg-green-50">
                                <span class="live-dot w-1.5 h-1.5 bg-green-500"></span> LIVE
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-6 text-xs font-semibold text-slate-500">
                            <span class="flex items-center gap-1"><svg class="w-4 h-4 text-slate-400"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z">
                                    </path>
                                </svg> {{ $series['users'] }} Users</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>{{ $series['total_tests'] }} Tests</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-green-600">{{ $series['free_tests'] }} Free Tests</span>
                        </div>

                        <div class="mb-6">
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($series['languages'] as $lang)
                                    <span
                                        class="text-[10px] uppercase font-bold px-2 py-1 bg-slate-100 text-slate-500 rounded border border-slate-200">{{ $lang }}</span>
                                @endforeach
                            </div>
                            <div class="space-y-2">
                                @foreach ($series['features'] as $feature)
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $feature }}
                                    </div>
                                @endforeach
                                <div class="pl-6 text-xs font-bold text-blue-600 cursor-pointer hover:underline">
                                    {{ $series['more_count'] }}</div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button
                                class="w-full py-3 text-sm font-bold text-blue-600 transition-all border border-blue-600 shadow-sm rounded-xl hover:bg-blue-600 hover:text-white">View
                                Test Series</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center md:hidden">
                <a href="#"
                    class="inline-block px-6 py-3 font-bold bg-white border rounded-lg shadow-sm border-slate-300 text-slate-700">Explore
                    all Test Series</a>
            </div>
        </div>
    </section>

    <!-- Stats Section (Light Mode) -->
    <section class="relative py-20 overflow-hidden bg-slate-50">
        <div class="relative z-10 px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
            <h2 class="mb-6 text-3xl font-extrabold text-slate-900 md:text-4xl">Don't just take our word for it,<br>our
                results speak for themselves.</h2>
            <p class="max-w-2xl mx-auto mb-16 text-lg text-slate-500">We are proud to have partnered with lakhs of
                students in securing their dream job.</p>

            <div class="grid grid-cols-2 gap-6 md:grid-cols-5">
                @foreach ($stats as $stat)
                    <div
                        class="p-6 transition-all duration-300 bg-white border shadow-sm border-slate-100 rounded-2xl stats-card-light hover:shadow-xl hover:-translate-y-2">
                        <div
                            class="flex items-center justify-center w-12 h-12 mx-auto mb-4 text-2xl rounded-full {{ $stat['bg'] }} stats-icon transition-transform duration-300">
                            {{ $stat['icon'] }}
                        </div>
                        <div class="mb-1 text-2xl font-extrabold md:text-3xl {{ $stat['color'] }}">
                            {{ $stat['count'] }}</div>
                        <div class="text-xs font-bold tracking-wider uppercase text-slate-400">{{ $stat['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Choose Us (3D Cards) -->
    <section class="py-20 bg-white">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="text-3xl font-extrabold text-slate-900">Why Exam Babu?</h2>
                <p class="mt-2 text-slate-500">The smart way to prepare for government exams.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-4">
                <!-- Card 1 -->
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-blue-100 rounded-2xl group-hover:scale-110">
                        🎯</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Exam Oriented</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Content designed purely based on latest exam
                        patterns and syllabus.</p>
                </div>
                <!-- Card 2 -->
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-green-100 rounded-2xl group-hover:scale-110">
                        📊</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Smart Analytics</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Get detailed report cards, strong/weak area
                        analysis after every test.</p>
                </div>
                <!-- Card 3 -->
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-purple-100 rounded-2xl group-hover:scale-110">
                        🗣️</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Bilingual</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Switch between English and Hindi (or Marathi)
                        anytime during the test.</p>
                </div>
                <!-- Card 4 -->
                <div
                    class="p-8 transition-all duration-300 bg-white border shadow-sm rounded-3xl hover:shadow-2xl group hover:-translate-y-2 border-slate-100">
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-6 text-3xl transition-transform bg-orange-100 rounded-2xl group-hover:scale-110">
                        💸</div>
                    <h3 class="mb-2 text-xl font-bold text-slate-900">Affordable</h3>
                    <p class="text-sm leading-relaxed text-slate-500">Premium quality education at the most affordable
                        prices in India.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SEO Links Section (Bottom Grid) -->
    <section class="py-16 border-t bg-slate-50 border-slate-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-6">
                @foreach ($allTestSeries as $title => $items)
                    <div>
                        <h4 class="mb-4 text-sm font-bold tracking-wider uppercase text-slate-900">{{ $title }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach ($items as $item)
                                <li><a href="#"
                                        class="text-xs transition-colors text-slate-500 hover:text-blue-600 hover:underline">{{ $item }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-16 pb-8 text-white border-t bg-slate-900 border-slate-800">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 mb-12 md:grid-cols-4">
                <div class="col-span-1 md:col-span-1">
                    <a href="/" class="flex items-center gap-2 mb-6">
                        <div class="flex items-center justify-center w-8 h-8 font-bold bg-blue-600 rounded-lg">E</div>
                        <span class="text-xl font-bold">ExamBabu</span>
                    </a>
                    <p class="mb-6 text-sm leading-relaxed text-slate-400">
                        India's most trusted platform for government exam preparation. We help you turn your dreams into
                        reality with structured courses and mock tests.
                    </p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="flex items-center justify-center w-8 h-8 text-sm transition rounded-full bg-white/10 hover:bg-blue-600">fb</a>
                        <a href="#"
                            class="flex items-center justify-center w-8 h-8 text-sm transition rounded-full bg-white/10 hover:bg-blue-400">tw</a>
                        <a href="#"
                            class="flex items-center justify-center w-8 h-8 text-sm transition rounded-full bg-white/10 hover:bg-pink-600">in</a>
                        <a href="#"
                            class="flex items-center justify-center w-8 h-8 text-sm transition rounded-full bg-white/10 hover:bg-red-600">yt</a>
                    </div>
                </div>

                <div>
                    <h4 class="mb-6 text-lg font-bold">Company</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#" class="transition hover:text-white">About Us</a></li>
                        <li><a href="#" class="transition hover:text-white">Careers</a></li>
                        <li><a href="#" class="transition hover:text-white">Teach Online</a></li>
                        <li><a href="#" class="transition hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="transition hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-6 text-lg font-bold">Popular Goals</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#" class="transition hover:text-white">SSC CGL 2025</a></li>
                        <li><a href="#" class="transition hover:text-white">SBI PO</a></li>
                        <li><a href="#" class="transition hover:text-white">RRB NTPC</a></li>
                        <li><a href="#" class="transition hover:text-white">CTET</a></li>
                        <li><a href="#" class="transition hover:text-white">UPSC Civil Services</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-6 text-lg font-bold">Get App</h4>
                    <div class="p-5 border bg-slate-800 rounded-2xl border-slate-700">
                        <p class="mb-4 text-xs leading-relaxed text-slate-300">Download our App for free mock tests,
                            live classes and daily current affairs.</p>
                        <button
                            class="w-full flex items-center justify-center gap-3 bg-white text-slate-900 py-2.5 rounded-xl font-bold text-sm hover:bg-blue-50 transition">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm11.455 11.23l5.808 3.235-5.87-5.88-2.58 2.58 2.642 2.645zm1.258-3.887l-5.868-5.867 5.868 3.22a1 1 0 010 1.748l5.808 3.235-5.808-2.336z" />
                            </svg>
                            <span>Google Play</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800 flex justify-between items-center px-6">
                <p class="text-sm text-slate-500">&copy; 2025 Exam Babu. All rights reserved. Made with ❤️ in India.</p>
                <p class="text-sm text-slate-500">
                    Design and Developed By <a href="https://www.digiemperor.com" class="text-blue-500 hover:underline">Digi Emperor</a>
                </p>
            </div>
        </div>
    </footer>
</body>

</html>
