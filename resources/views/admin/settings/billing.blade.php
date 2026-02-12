@extends('layouts.admin')
@section('title', 'Billing Settings')
@section('content')

    @include('admin.settings._nav')

    <div class="max-w-4xl p-6 mx-auto bg-white border border-gray-200 shadow-sm rounded-xl">
        <h2 class="pb-2 mb-4 text-lg font-bold text-gray-900 border-b">Invoicing Details</h2>

        <form action="{{ route('admin.settings.update-billing') }}" method="POST">
            @csrf

            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" name="enable_invoicing" id="enable_invoicing" value="1"
                        {{ $settings->enable_invoicing ? 'checked' : '' }}
                        class="rounded border-gray-300 text-[#0777be] shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                    <label for="enable_invoicing" class="ml-2 text-sm font-medium text-gray-700">Enable Invoices for
                        Payments</label>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Vendor Name</label>
                    <input type="text" name="vendor_name" value="{{ $settings->vendor_name }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" value="{{ $settings->invoice_prefix }}" placeholder="INV-"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" rows="2"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">{{ $settings->address }}</textarea>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" value="{{ $settings->city }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">State</label>
                    <input type="text" name="state" value="{{ $settings->state }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Zip Code</label>
                    <input type="text" name="zip" value="{{ $settings->zip }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <input type="text" name="country" value="{{ $settings->country }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ $settings->phone_number }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700">VAT/GST Number</label>
                    <input type="text" name="vat_number" value="{{ $settings->vat_number }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0777be] focus:ring focus:ring-[#0777be] focus:ring-opacity-50">
                </div>

            </div>

            <div class="flex justify-end pt-4 mt-8 border-t">
                <button type="submit"
                    class="bg-[#0777be] text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:bg-[#0777be]/90 transition">
                    Save Billing Details
                </button>
            </div>
        </form>
    </div>
@endsection
