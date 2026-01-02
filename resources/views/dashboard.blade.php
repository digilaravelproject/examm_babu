@php
    // Jaise hi yeh file load hogi, yeh code user ko 'home' route par redirect kar dega
    // aur baaki ka page load nahi hoga.
    return redirect()->route('home')->send();
    die();
@endphp
{{-- Yahan hum layout ko 'extend' kar rahe hain (Include wala style) --}}
@extends('layouts.app')

{{-- Yeh section 'content' ke andar ka maal masala hai --}}
@section('content')
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Hello, {{ Auth::user()->name }}! 👋</h1>
            <p class="mt-2 text-gray-600">Welcome to your Exam Preparation Hub.</p>

            <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-2">
                <!-- Start New Test Card -->
                <div class="p-4 transition border rounded cursor-pointer hover:shadow-lg">
                    <h3 class="text-lg font-bold">Start New Test</h3>
                    <p class="text-sm text-gray-500">Practice makes perfect.</p>
                </div>

                <!-- Performance Card -->
                <div class="p-4 transition border rounded cursor-pointer hover:shadow-lg">
                    <h3 class="text-lg font-bold">My Performance</h3>
                    <p class="text-sm text-gray-500">Check your analytics.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
