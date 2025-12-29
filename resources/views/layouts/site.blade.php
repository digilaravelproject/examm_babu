<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exam Babu - India's #1 Learning Platform</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/favicon.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Exam Babu Brand Colors */
            --brand-blue: #0777be;
            --brand-pink: #f062a4;
            --brand-green: #94c940;
            --brand-sky: #7fd2ea;
            --sidebar-bg: #0f172a;

            /* Mapping to Theme Variables */
            --primary: var(--brand-blue);
            --primary-dark: #055a91; /* Darker version of brand blue */
            --secondary: var(--sidebar-bg);
            --accent: var(--brand-pink);

            --bg-body: #ffffff;
            --bg-card: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--secondary);
            overflow-x: hidden;
            position: relative;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }

        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Global Background Animation */
        .global-bg-animation {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1; overflow: hidden; pointer-events: none;
        }
        .floating-shape {
            position: absolute; filter: blur(60px); opacity: 0.4;
            animation: float 15s infinite ease-in-out;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -50px) rotate(10deg); }
            66% { transform: translate(-20px, 20px) rotate(-5deg); }
        }

        /* Glass Nav */
        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        /* Utility Helpers for Brand Colors */
        .text-brand-blue { color: var(--brand-blue); }
        .bg-brand-blue { background-color: var(--brand-blue); }
        .text-brand-pink { color: var(--brand-pink); }
        .text-brand-green { color: var(--brand-green); }

        /* 3D Card Effect */
        .card-3d {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-3d:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>

<body class="antialiased selection:bg-blue-100 selection:text-blue-900">

    <div class="global-bg-animation">
        <div class="floating-shape bg-blue-300 w-96 h-96 rounded-full top-[-10%] left-[-10%] mix-blend-multiply opacity-30"></div>
        <div class="floating-shape bg-purple-300 w-96 h-96 rounded-full top-[20%] right-[-10%] mix-blend-multiply opacity-30" style="animation-delay: -5s"></div>
        <div class="floating-shape bg-pink-300 w-96 h-96 rounded-full bottom-[-10%] left-[20%] mix-blend-multiply opacity-30" style="animation-delay: -10s"></div>
        <div class="floating-shape bg-yellow-200 w-64 h-64 rounded-full bottom-[40%] right-[30%] mix-blend-multiply opacity-20" style="animation-delay: -2s"></div>
    </div>

    @include('store.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('store.partials.footer')

</body>
</html>
