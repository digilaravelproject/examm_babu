<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExamBabu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('storage/site_images/logo1dotcom.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: 'var(--brand-blue)',   // Maps to #0777be
                            pink: 'var(--brand-pink)',   // Maps to #f062a4
                            green: 'var(--brand-green)', // Maps to #94c940
                            sky: 'var(--brand-sky)',     // Maps to #7fd2ea
                            sidebar: 'var(--sidebar-bg)' // Maps to #0f172a
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap');

        /* 2. ROOT COLORS DEFINITION */
        :root {
            --brand-blue: #0777be;
            --brand-pink: #f062a4;
            --brand-green: #94c940;
            --brand-sky: #7fd2ea;
            --sidebar-bg: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            background-color: #f8fafc;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translate3d(0, 30px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }

        .animate-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Floating Circles */
        .circles {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px; height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: floatUp 25s linear infinite;
            bottom: -150px;
            border-radius: 6px;
        }

        .circles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .circles li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .circles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .circles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }

        @keyframes floatUp {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        .bullet-tag {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #ffffff;
            /* Using CSS Variable directly here for the green border */
            border-left: 3px solid var(--brand-green);
            margin-bottom: 0.5rem;
            margin-right: 0.5rem;
            display: inline-block;
            backdrop-filter: blur(4px);
        }
    </style>
</head>

<body class="relative flex items-center justify-center min-h-screen p-4 bg-slate-50">

    <div class="relative z-10 flex flex-row w-full max-w-4xl overflow-hidden bg-white border border-gray-100 shadow-2xl animate-card rounded-2xl"
        style="min-height: 500px;">

        <div class="relative flex-col justify-center hidden p-8 overflow-hidden md:flex md:w-1/2 bg-brand-blue lg:p-12">
            <ul class="circles">
                <li></li> <li></li> <li></li> <li></li> <li></li> <li></li> <li></li>
            </ul>
            <div class="relative z-20">
                <h2 class="mb-4 text-xs font-bold tracking-widest uppercase text-brand-sky">EXAM ..NO WORRIES</h2>
                <h1 class="mb-2 text-3xl font-bold leading-tight text-white"
                    style="font-family: 'Playfair Display', serif;">Get Prepared <br>Before Exam</h1>
                <p class="mb-6 text-sm italic text-blue-100">Best Platform for Students</p>
                <div class="flex flex-col flex-wrap items-start">
                    <span class="bullet-tag">🚀 Start your preparation early</span>
                    <span class="bullet-tag">📚 Review and practice past papers</span>
                    <span class="bullet-tag">⏰ Plan your exam day & rest well</span>
                    <span class="bullet-tag">🧘 Stay positive and motivated</span>
                </div>
            </div>

            <div class="absolute bottom-0 left-0 w-full h-32 pointer-events-none bg-gradient-to-t from-brand-sidebar/50 to-transparent">
            </div>
        </div>

        <div class="relative flex flex-col justify-center w-full p-8 bg-white md:w-1/2 lg:p-12">

            <div class="mb-8 text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 overflow-hidden shadow-lg bg-brand-blue rounded-xl shadow-blue-200">
                    <img src="{{ asset('storage/site_images/logo1dotcom.png') }}" alt="Logo" class="object-cover w-full h-full">
                </div>

                <h2 class="text-2xl font-bold text-gray-900">Welcome Back!</h2>
                <p class="mt-1 text-sm text-gray-500">Please login to your account</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email Address</label>
                    <input name="email" type="email"
                        class="block w-full px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                        placeholder="student@example.com">
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password"
                            class="block w-full px-4 py-3 pr-10 text-sm text-gray-900 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 cursor-pointer hover:text-brand-blue">
                            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 border-gray-300 rounded cursor-pointer text-brand-blue focus:ring-brand-blue">
                        <span class="ml-2 text-sm text-gray-600 select-none">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-semibold text-brand-blue hover:text-brand-sidebar">Forgot Password?</a>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-brand-sidebar focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all shadow-lg shadow-blue-200 transform hover:-translate-y-0.5">
                        Log In
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        Don't have an account?
                        <a href="{{ route('register') }}"
                            class="font-bold transition-colors text-brand-blue hover:text-brand-pink">Sign Up</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
