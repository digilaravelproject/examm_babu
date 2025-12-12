<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExamBabu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 30px, 0);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .animate-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Floating Circles (White with transparency) */
        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            margin: 0;
            padding: 0;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            animation: floatUp 25s linear infinite;
            bottom: -150px;
            border-radius: 6px;
        }

        .circles li:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .circles li:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .circles li:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }

        .circles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        .bullet-tag {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 0.5rem;
            margin-right: 0.5rem;
            display: inline-block;
            backdrop-filter: blur(4px);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: '#2563eb',
                            dark: '#1e40af',
                            light: '#eff6ff'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-blue-50 min-h-screen flex items-center justify-center p-4 relative">

    <div class="animate-card w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-row border border-blue-100 relative z-10"
        style="min-height: 500px;">

        <div class="hidden md:flex md:w-1/2 bg-brand-blue relative flex-col justify-center p-8 lg:p-12 overflow-hidden">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <div class="relative z-20">
                <h2 class="text-xs font-bold text-blue-100 uppercase tracking-widest mb-4">EXAM ..NO WORRIES</h2>
                <h1 class="text-3xl font-bold text-white mb-2 leading-tight"
                    style="font-family: 'Playfair Display', serif;">Get Prepared <br>Before Exam</h1>
                <p class="text-sm text-blue-100 italic mb-6">Best Platform for Students</p>
                <div class="flex flex-wrap flex-col items-start">
                    <span class="bullet-tag">🚀 Start your preparation early</span>
                    <span class="bullet-tag">📚 Review and practice past papers</span>
                    <span class="bullet-tag">⏰ Plan your exam day & rest well</span>
                    <span class="bullet-tag">🧘 Stay positive and motivated</span>
                </div>
            </div>
            <img src="https://placehold.co/400x400/2563eb/2563eb?text="
                class="absolute bottom-0 right-0 w-64 h-auto object-contain opacity-50 z-0 mix-blend-multiply">
            <div
                class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-blue-800/50 to-transparent pointer-events-none">
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 lg:p-12 bg-white flex flex-col justify-center relative">

            <div class="mb-8 text-center">
                <div
                    class="w-12 h-12 bg-brand-blue rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-4 shadow-lg shadow-blue-200 mx-auto">
                    E
                </div>

                <h2 class="text-2xl font-bold text-gray-900">Welcome Back!</h2>
                <p class="text-sm text-gray-500 mt-1">Please login to your account</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email
                        Address</label>
                    <input type="email"
                        class="block w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all shadow-sm placeholder-gray-400"
                        placeholder="student@example.com">
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'"
                            class="block w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all shadow-sm pr-10"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-brand-blue cursor-pointer">
                            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="h-4 w-4 text-brand-blue rounded border-gray-300 focus:ring-brand-blue cursor-pointer">
                        <span class="ml-2 text-sm text-gray-600 select-none">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-semibold text-brand-blue hover:text-blue-800">Forgot
                        Password?</a>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all shadow-lg shadow-blue-200 transform hover:-translate-y-0.5">
                        Log In
                    </button>
                </div>

                <div class="text-center mt-4">
                    <p class="text-sm text-gray-500">
                        Don't have an account? <a href="{{ route('register') }}"
                            class="font-bold text-brand-blue hover:underline">Sign
                            Up</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
