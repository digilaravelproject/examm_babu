@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    {{-- Main Container --}}
    <div x-data="paymentManager()" @open-drawer.window="openDrawer($event.detail.id)" class="space-y-8 h-full">

        {{-- Top Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Payments</h1>
                <p class="text-sm text-slate-500 mt-1">Manage transactions and approve manual payments.</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-sm grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Search ID --}}
            <div class="relative">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Payment ID</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="filters.payment_id" @input.debounce.500ms="applyFilters()"
                        class="w-full pl-10 text-sm border-slate-200 rounded-lg focus:ring-[#0f172a] focus:border-[#0f172a] bg-slate-50 focus:bg-white transition-colors py-2.5"
                        placeholder="Search Transaction ID...">
                </div>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Status</label>
                <div class="relative">
                    <select x-model="filters.status" @change="applyFilters()"
                        class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#0f172a] focus:border-[#0f172a] bg-slate-50 focus:bg-white transition-colors py-2.5 appearance-none">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div id="table-container"
            class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden ring-1 ring-black ring-opacity-5">
            @include('admin.payments.partials.table')
        </div>

        {{-- Drawer (Sidebar for Details) --}}
        <div x-show="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900 bg-opacity-60 transition-opacity backdrop-blur-sm"
                @click="closeDrawer()"></div>

            {{-- Sidebar Container --}}
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div
                    class="w-screen max-w-md transform transition ease-in-out duration-500 bg-white shadow-2xl flex flex-col h-full border-l border-slate-100">

                    {{-- Drawer Header --}}
                    <div class="px-6 py-5 bg-white border-b border-slate-100 flex justify-between items-center flex-none">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Payment Details</h2>
                            <p class="text-xs text-slate-400 mt-0.5">View transaction information</p>
                        </div>
                        <button @click="closeDrawer()"
                            class="rounded-full p-2 bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 focus:outline-none transition-all">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Drawer Content --}}
                    <div class="relative flex-1 overflow-y-auto bg-slate-50" id="drawer-content">
                        {{-- Loading Spinner --}}
                        <div class="flex flex-col justify-center items-center h-full text-[#0f172a]">
                            <svg class="animate-spin h-10 w-10 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-sm font-medium text-slate-500">Loading details...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function paymentManager() {
            return {
                filters: {
                    payment_id: '',
                    status: ''
                },
                drawerOpen: false,

                applyFilters() {
                    let params = new URLSearchParams(this.filters).toString();
                    fetch(`{{ route('admin.payments.index') }}?${params}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => document.getElementById('table-container').innerHTML = html);
                },

                openDrawer(id) {
                    this.drawerOpen = true;
                    // Reset to Spinner
                    document.getElementById('drawer-content').innerHTML =
                        '<div class="flex flex-col justify-center items-center h-full text-[#0f172a]"><svg class="animate-spin h-10 w-10 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm font-medium text-slate-500">Loading details...</span></div>';

                    fetch(`/admin/payments/${id}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => {
                            document.getElementById('drawer-content').innerHTML = html;
                        });
                },

                closeDrawer() {
                    this.drawerOpen = false;
                    setTimeout(() => {
                        document.getElementById('drawer-content').innerHTML = ''; // Clear content
                    }, 300);
                },

                approvePayment(id, action) {
                    let msg = action === 'approve' ? 'approve and activate subscription' : 'reject';
                    if (!confirm(`Are you sure you want to ${msg} this payment?`)) return;

                    fetch(`/admin/payments/${id}/authorize`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                status: action === 'approve' ? 'approved' : 'rejected'
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                window.location.reload();
                            } else {
                                alert(data.message || 'Something went wrong');
                            }
                        });
                },

                deletePayment(id) {
                    if (confirm('Are you sure? This will delete the payment and associated subscription.')) {
                        fetch(`/admin/payments/${id}`, { // Make sure destroy route exists in web.php
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(() => window.location.reload());
                    }
                }
            }
        }
    </script>
@endpush
