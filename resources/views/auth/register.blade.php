<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ExamBabu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* ... Existing Styles ... */
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

        .circles li:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
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

<body class="relative flex items-center justify-center min-h-screen p-4 bg-blue-50">

    <div class="relative z-10 flex flex-row w-full max-w-4xl overflow-hidden bg-white border border-blue-100 shadow-2xl animate-card rounded-2xl"
        style="min-height: 520px;">

        <div class="relative flex-col justify-center hidden p-8 overflow-hidden md:flex md:w-1/2 bg-brand-blue lg:p-12">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <div class="relative z-20">
                <h2 class="mb-4 text-xs font-bold tracking-widest text-blue-100 uppercase">JOIN EXAMBABU</h2>
                <h1 class="mb-3 text-3xl font-bold leading-tight text-white"
                    style="font-family: 'Playfair Display', serif;">Start Your <br>Success Journey</h1>
                <p class="mb-8 text-sm italic leading-relaxed text-blue-100">Join thousands of students and get access
                    to the best tools for your exam preparation.</p>
                <div class="flex flex-col flex-wrap items-start">
                    <span class="bullet-tag">🚀 Interactive Mock Tests</span>
                    <span class="bullet-tag">📊 Detailed Performance Reports</span>
                    <span class="bullet-tag">📚 Comprehensive Study Material</span>
                </div>
            </div>
            <img src="https://placehold.co/400x400/2563eb/2563eb?text="
                class="absolute bottom-0 right-0 z-0 object-contain h-auto w-60 opacity-40 mix-blend-multiply">
            <div
                class="absolute bottom-0 left-0 w-full pointer-events-none h-28 bg-gradient-to-t from-blue-800/50 to-transparent">
            </div>
        </div>

        <div class="relative flex flex-col justify-center w-full p-6 bg-white md:w-1/2 lg:p-10">

            <div class="mb-5 text-center">
                <div
                    class="flex items-center justify-center w-10 h-10 mx-auto mb-3 text-xl font-bold text-white shadow-lg bg-brand-blue rounded-xl shadow-blue-200">
                    E</div>
                <h2 class="text-xl font-bold text-gray-900">Create Account</h2>
                <p class="mt-1 text-xs text-gray-500">Sign up in just 2 minutes</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-3">
                @csrf

                <div class="flex gap-3">
                    <div class="w-1/2">
                        <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">First
                            Name</label>
                        <input type="text" name="first_name" :value="old('first_name')" required autofocus
                            class="block w-full px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                            placeholder="John">
                        <x-input-error :messages="$errors->get('first_name')" class="mt-1 text-xs text-red-500" />
                    </div>
                    <div class="w-1/2">
                        <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Last
                            Name</label>
                        <input type="text" name="last_name" :value="old('last_name')"
                            class="block w-full px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                            placeholder="Doe">
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1 text-xs text-red-500" />
                    </div>
                </div>

                <div x-data="{
                    username: '{{ old('user_name') }}',
                    status: '',
                    message: '',
                    checkUsername() {
                        if (this.username.length < 3) {
                            this.status = '';
                            this.message = '';
                            return;
                        }
                        fetch('{{ route('check.username') }}?username=' + this.username)
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'taken') {
                                    this.status = 'error';
                                    this.message = 'Username is already taken';
                                } else {
                                    this.status = 'success';
                                    this.message = 'Username is available';
                                }
                            });
                    }
                }">
                    <label
                        class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Username</label>
                    <div class="relative">
                        <input type="text" name="user_name" x-model="username"
                            @input.debounce.500ms="checkUsername()" required
                            class="block w-full px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                            :class="{ 'border-red-500 focus:ring-red-500': status === 'error', 'border-green-500 focus:ring-green-500': status === 'success' }"
                            placeholder="jhondeo123">

                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i x-show="status === 'success'" class="text-green-500 fa-solid fa-circle-check"></i>
                            <i x-show="status === 'error'" class="text-red-500 fa-solid fa-circle-xmark"></i>
                        </div>
                    </div>
                    <p x-show="message" x-text="message" class="mt-1 text-xs"
                        :class="status === 'error' ? 'text-red-500' : 'text-green-600'"></p>
                    <x-input-error :messages="$errors->get('user_name')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Email
                        Address</label>
                    <input type="email" name="email" :value="old('email')" required
                        class="block w-full px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent"
                        placeholder="student@example.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-500" />
                </div>

                <div x-data="{ show: false }">
                    <label
                        class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                            class="block w-full px-3 py-2 text-sm text-gray-900 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent pr-9"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 cursor-pointer hover:text-brand-blue">
                            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
                </div>

                <div x-data="{ showConfirm: false }">
                    <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase tracking-wide">Confirm
                        Password</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                            class="block w-full px-3 py-2 text-sm text-gray-900 transition-all border border-gray-200 rounded-lg shadow-sm outline-none bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent pr-9"
                            placeholder="••••••••">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 cursor-pointer hover:text-brand-blue">
                            <i class="fa-regular" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-500" />
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-blue hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all shadow-lg shadow-blue-200 transform hover:-translate-y-0.5">
                        Create Account
                    </button>
                </div>

                <div class="mt-3 text-center">
                    <p class="text-xs text-gray-500">
                        Already have an account? <a href="{{ route('login') }}"
                            class="font-bold text-brand-blue hover:underline">Log In</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
