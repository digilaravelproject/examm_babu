@extends('layouts.student')

@section('content')
    {{-- Custom Styles for Exam Babu Theme --}}
    <style>
        .verification-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-top: 5px solid var(--brand-blue);
            width: 100%;
            max-width: 450px;
            /* Thoda compact width taaki mobile pe bhi sahi lage */
        }

        .btn-exam-babu {
            background-color: var(--brand-blue);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(7, 119, 190, 0.2);
        }

        .btn-exam-babu:hover {
            background-color: #055a91;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(7, 119, 190, 0.3);
        }

        .link-logout {
            color: #64748b;
            text-decoration: underline;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .link-logout:hover {
            color: var(--brand-pink);
        }

        .icon-circle {
            height: 70px;
            width: 70px;
            background-color: #e0f2fe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem auto;
        }

        .icon-svg {
            color: var(--brand-blue);
            width: 35px;
            height: 35px;
        }
    </style>

    {{--
    Updates for Positioning:
    1. min-h-[80vh]: Screen ka 80% height lega, taaki sidebar layout me center me dikhe.
    2. px-4: Mobile pe edges se chipkega nahi.
    3. w-full: Container full width lega taaki flex center kaam kare.
--}}
    <div class="w-full flex flex-col items-center justify-center min-h-[80vh] px-4 py-6 bg-gray-50">

        <div class="p-6 verification-card sm:p-8">
            {{-- Logo Section --}}
            <div class="mb-5 text-center">
                <img src="{{ asset('storage/site_images/logo1dotcom.png') }}" alt="Logo"
                    class="object-contain h-12 mx-auto mb-2 sm:h-14">
            </div>

            {{-- Illustration Icon --}}
            <div class="icon-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            {{-- Heading --}}
            <h2 class="mb-2 text-xl font-bold text-center text-gray-800 sm:text-2xl">Verify your email</h2>

            <div class="mb-6 text-sm leading-relaxed text-center text-gray-600">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
            </div>

            {{-- Status Message --}}
            @if (session('status') == 'verification-link-sent')
                <div
                    class="p-3 mb-6 text-xs font-medium text-center text-green-700 border border-green-200 rounded-lg sm:text-sm bg-green-50">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col gap-4 mt-4">
                {{-- Resend Button --}}
                <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="flex items-center justify-center w-full gap-2 text-sm btn-exam-babu sm:text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('Resend Verification Email') }}
                    </button>
                </form>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="link-logout">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Footer Text --}}
        <div class="mt-8 text-xs text-center text-gray-400">
            &copy; {{ date('Y') }} Exam Babu. All rights reserved.
        </div>
    </div>
@endsection
