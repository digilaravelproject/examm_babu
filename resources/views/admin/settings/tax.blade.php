@extends('layouts.admin')
@section('title', 'Tax / GST Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="max-w-4xl p-6 mx-auto bg-white border border-gray-200 shadow-sm rounded-xl">
        <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Tax / GST Configuration</h2>
        <p class="mb-6 text-sm text-gray-500">Configure tax rates that apply to all plans at checkout. Taxes marked as <strong>Exclusive</strong> are added on top of the plan price.</p>

        <form action="{{ route('admin.settings.update-tax') }}" method="POST">
            @csrf

            {{-- ═══════════════════════════════════════════
                PRIMARY TAX (e.g., CGST)
            ═══════════════════════════════════════════ --}}
            <div class="p-5 mb-6 border border-gray-200 rounded-xl bg-slate-50/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-800">Primary Tax</h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enable_tax" value="1"
                            {{ $settings->enable_tax ? 'checked' : '' }}
                            class="sr-only peer" id="enable_tax_toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                        <span class="ml-2 text-xs font-medium text-gray-600">Enabled</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tax Name</label>
                        <input type="text" name="tax_name" value="{{ old('tax_name', $settings->tax_name) }}"
                            placeholder="e.g. CGST, GST, VAT"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rate (%)</label>
                        <input type="number" step="0.01" name="tax_amount" value="{{ old('tax_amount', $settings->tax_amount) }}"
                            placeholder="e.g. 9"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Calculation Type</label>
                        <select name="tax_type"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="exclusive" {{ $settings->tax_type === 'exclusive' ? 'selected' : '' }}>Exclusive (Added to price)</option>
                            <option value="inclusive" {{ $settings->tax_type === 'inclusive' ? 'selected' : '' }}>Inclusive (Included in price)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount Type</label>
                        <select name="tax_amount_type"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="percentage" {{ $settings->tax_amount_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ $settings->tax_amount_type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════
                ADDITIONAL TAX (e.g., SGST)
            ═══════════════════════════════════════════ --}}
            <div class="p-5 mb-6 border border-gray-200 rounded-xl bg-slate-50/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-800">Additional Tax (e.g. SGST)</h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enable_additional_tax" value="1"
                            {{ $settings->enable_additional_tax ? 'checked' : '' }}
                            class="sr-only peer" id="enable_additional_tax_toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0777be]"></div>
                        <span class="ml-2 text-xs font-medium text-gray-600">Enabled</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tax Name</label>
                        <input type="text" name="additional_tax_name" value="{{ old('additional_tax_name', $settings->additional_tax_name) }}"
                            placeholder="e.g. SGST, Service Tax"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rate (%)</label>
                        <input type="number" step="0.01" name="additional_tax_amount" value="{{ old('additional_tax_amount', $settings->additional_tax_amount) }}"
                            placeholder="e.g. 9"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Calculation Type</label>
                        <select name="additional_tax_type"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="exclusive" {{ $settings->additional_tax_type === 'exclusive' ? 'selected' : '' }}>Exclusive (Added to price)</option>
                            <option value="inclusive" {{ $settings->additional_tax_type === 'inclusive' ? 'selected' : '' }}>Inclusive (Included in price)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount Type</label>
                        <select name="additional_tax_amount_type"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                            <option value="percentage" {{ $settings->additional_tax_amount_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ $settings->additional_tax_amount_type === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- INFO BOX --}}
            <div class="p-4 mb-6 border border-blue-100 rounded-lg bg-blue-50">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 mt-0.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold">How GST Works Here</p>
                        <p class="mt-1 text-blue-700">For Indian GST, set <strong>Primary Tax = CGST (9%)</strong> and <strong>Additional Tax = SGST (9%)</strong>. Both set to <em>Exclusive + Percentage</em>. This will show a combined 18% GST on checkout.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t">
                <button type="submit"
                    class="bg-[#0777be] text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-[#0777be]/90 transition">
                    Save Tax Settings
                </button>
            </div>
        </form>
    </div>
@endsection
