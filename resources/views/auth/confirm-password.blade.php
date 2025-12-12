<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password - ExamBabu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
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

        /* Floating Circles */
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
            padding: 0.35rem 0.9rem;
            border-radius: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 0.6rem;
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
        style="min-height: 480px;">

        <div class="hidden md:flex md:w-1/2 bg-brand-blue relative flex-col justify-center p-8 lg:p-12 overflow-hidden">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <div class="relative z-20">
                <h2 class="text-xs font-bold text-blue-100 uppercase tracking-widest mb-4">SECURE AREA</h2>
                <h1 class="text-3xl font-bold text-white mb-3 leading-tight"
                    style="font-family: 'Playfair Display', serif;">Security <br>Verification</h1>
                <p class="text-sm text-blue-100 italic mb-8 leading-relaxed">This is a protected area of the
                    application. Please confirm your identity to proceed.</p>

                <div class="flex flex-wrap flex-col items-start">
                    <span class="bullet-tag">🔒 End-to-End Encryption</span>
                    <span class="bullet-tag">🛡️ Identity Verification</span>
                    <span class="bullet-tag">⚡ Secure Session</span>
                </div>
            </div>
            <img src="https://placehold.co/400x400/2563eb/2563eb?text="
                class="absolute bottom-0 right-0 w-60 h-auto object-contain opacity-40 z-0 mix-blend-multiply">
            <div
                class="absolute bottom-0 left-0 w-full h-28 bg-gradient-to-t from-blue-800/50 to-transparent pointer-events-none">
            </div>
        </div>

        <div class="w-full md:w-1/2 p-6 lg:p-10 bg-white flex flex-col justify-center relative">

            <div class="mb-6 text-center">
                <div
                    class="w-10 h-10 bg-brand-blue rounded-xl flex items-center justify-center text-white text-xl font-bold mb-4 shadow-lg shadow-blue-200 mx-auto">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Confirm Password</h2>
            </div>

            <div class="mb-6 text-sm text-gray-500 text-center leading-relaxed">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
                @csrf

                <div x-data="{ show: false }">
                    <label
                        class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                            autocomplete="current-password"
                            class="block w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all shadow-sm pr-10"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-brand-blue cursor-pointer">
                            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all shadow-lg shadow-blue-200 transform hover:-translate-y-0.5">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
