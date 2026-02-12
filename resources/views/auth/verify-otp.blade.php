<x-guest-layout>
    <div class="w-full max-w-md mx-auto p-6">

        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Email Verification</h2>
            <p class="text-sm text-gray-500 mt-2">
                We have sent a verification code to
                <br>
                <span class="font-medium text-indigo-600">{{ Auth::user()->email }}</span>
            </p>
        </div>

        <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

        <form method="POST" action="{{ route('otp.check') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="otp" class="sr-only">Enter OTP</label>
                    <input id="otp" type="text" name="otp" maxlength="6"
                        class="block w-full text-center text-3xl font-bold tracking-[0.5em] h-14 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                        placeholder="••••••" required autofocus
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)" />
                    <x-input-error :messages="$errors->get('otp')" class="mt-2 text-center" />
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Verify Account
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Didn't receive the code?
                <button type="submit" form="resend-form"
                    class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                    Click to resend
                </button>
            </p>
        </div>
    </div>

    <form id="resend-form" method="POST" action="{{ route('otp.resend') }}" class="hidden">
        @csrf
    </form>
</x-guest-layout>
