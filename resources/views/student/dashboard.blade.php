<x-candidate-layout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Hello, {{ $user->name }}! 👋</h1>
            <p class="mt-2 text-gray-600">Welcome to your Exam Preparation Hub.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border p-4 rounded hover:shadow-lg transition cursor-pointer">
                    <h3 class="font-bold text-lg">Start New Test</h3>
                    <p class="text-sm text-gray-500">Practice makes perfect.</p>
                </div>
                <div class="border p-4 rounded hover:shadow-lg transition cursor-pointer">
                    <h3 class="font-bold text-lg">My Performance</h3>
                    <p class="text-sm text-gray-500">Check your analytics.</p>
                </div>
            </div>
        </div>
    </div>
</x-candidate-layout>
