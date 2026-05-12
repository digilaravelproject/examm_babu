@extends('layouts.admin')
@section('title', 'AI Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="max-w-4xl">
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Gemini AI Configuration</h2>
            
            <form action="{{ route('admin.settings.update-ai') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gemini API Key</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="password" name="gemini_api_key" id="gemini_api_key" value="{{ $settings->gemini_api_key }}"
                                class="flex-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50"
                                placeholder="Enter your Gemini API key">
                            <button type="button" onclick="togglePassword()" class="ml-2 px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm hover:bg-gray-200">
                                Show/Hide
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">You can get your API key from the <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[#0777be] underline">Google AI Studio</a>.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Model Name</label>
                        <select name="model_name" id="model_name" onchange="checkCustomModel(this.value)"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <optgroup label="Flash Models (Cost-Optimized)">
                                <option value="gemini-2.0-flash" {{ $settings->model_name == 'gemini-2.0-flash' ? 'selected' : '' }}>Gemini 2.0 Flash (Recommended)</option>
                                <option value="gemini-2.0-flash-001" {{ $settings->model_name == 'gemini-2.0-flash-001' ? 'selected' : '' }}>Gemini 2.0 Flash-001</option>
                                <option value="gemini-1.5-flash" {{ $settings->model_name == 'gemini-1.5-flash' ? 'selected' : '' }}>Gemini 1.5 Flash (Legacy)</option>
                            </optgroup>
                            <optgroup label="Pro Models (Advanced)">
                                <option value="gemini-2.5-pro" {{ $settings->model_name == 'gemini-2.5-pro' ? 'selected' : '' }}>Gemini 2.5 Pro</option>
                                <option value="gemini-3.1-pro-preview" {{ $settings->model_name == 'gemini-3.1-pro-preview' ? 'selected' : '' }}>Gemini 3.1 Pro (Preview)</option>
                            </optgroup>
                            <option value="custom" {{ $settings->model_name == 'custom' ? 'selected' : '' }}>Enter Custom Model Name...</option>
                        </select>
                    </div>

                    <div id="custom_model_container" style="{{ $settings->model_name == 'custom' ? '' : 'display: none;' }}">
                        <label class="block text-sm font-medium text-gray-700">Custom Model Identifier</label>
                        <input type="text" name="custom_model" value="{{ $settings->custom_model }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50"
                            placeholder="e.g. gemini-3.0-ultra-preview">
                        <p class="mt-1 text-xs text-gray-400">Refer to Google AI documentation for valid model strings.</p>
                    </div>

                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Note on API Usage</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>The Gemini API key is used for the AI Question Import feature. Make sure your key has sufficient quota. The "Flash" models are generally recommended for high-volume question extraction.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit"
                        class="bg-[#0777be] text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-[#0777be]/90 transition shadow-sm">
                        Save AI Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("gemini_api_key");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }

        function checkCustomModel(val) {
            const container = document.getElementById('custom_model_container');
            if (val === 'custom') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    </script>
@endsection
