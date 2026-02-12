@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    {{-- Main Container --}}
    <div x-data="paymentManager()" @open-drawer.window="openDrawer($event.detail.id)" class="h-full space-y-8">

        {{-- Top Header --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Payments</h1>
                <p class="mt-1 text-sm text-slate-500">Manage transactions and approve manual payments.</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="grid grid-cols-1 gap-5 p-5 bg-white border shadow-sm border-slate-200 rounded-xl md:grid-cols-3">
            {{-- Search ID --}}
            <div class="relative">
                <label class="block text-xs font-bold tracking-wide uppercase text-slate-500 mb-1.5">Payment ID</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="filters.payment_id" @input.debounce.500ms="applyFilters()"
                        class="w-full pl-10 text-sm transition-colors border-slate-200 rounded-lg focus:ring-[#0f172a] focus:border-[#0f172a] bg-slate-50 focus:bg-white py-2.5"
                        placeholder="Search Transaction ID...">
                </div>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block text-xs font-bold tracking-wide uppercase text-slate-500 mb-1.5">Status</label>
                <div class="relative">
                    <select x-model="filters.status" @change="applyFilters()"
                        class="w-full text-sm transition-colors border-slate-200 rounded-lg appearance-none focus:ring-[#0f172a] focus:border-[#0f172a] bg-slate-50 focus:bg-white py-2.5">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div id="table-container"
            class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl ring-1 ring-black ring-opacity-5">
            @include('admin.payments.partials.table')
        </div>

        {{-- Drawer (Sidebar for Details) --}}
        <div x-show="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;" x-cloak>
            <div class="absolute inset-0 transition-opacity bg-slate-900 bg-opacity-60 backdrop-blur-sm"
                @click="closeDrawer()"></div>

            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div
                    class="flex flex-col h-full w-screen max-w-md duration-500 transform bg-white shadow-2xl transition ease-in-out border-l border-slate-100">

                    {{-- Drawer Header --}}
                    <div class="flex items-center justify-between flex-none px-6 py-5 bg-white border-b border-slate-100">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Payment Details</h2>
                            <p class="text-xs text-slate-400 mt-0.5">View transaction information</p>
                        </div>
                        <button @click="closeDrawer()"
                            class="p-2 transition-all rounded-full bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Drawer Content --}}
                    <div class="relative flex-1 overflow-y-auto bg-slate-50" id="drawer-content">
                        <div class="flex flex-col justify-center items-center h-full text-[#0f172a]">
                            <svg class="mb-3 w-10 h-10 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
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
                    document.getElementById('drawer-content').innerHTML =
                        '<div class="flex flex-col justify-center items-center h-full text-[#0f172a]"><svg class="mb-3 w-10 h-10 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm font-medium text-slate-500">Loading details...</span></div>';

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
                        document.getElementById('drawer-content').innerHTML = '';
                    }, 300);
                },

                approvePayment(id, action) {
                    let msg = action === 'approve' ? 'Approve and activate subscription?' : 'Reject this payment?';
                    if (!confirm(msg)) return;

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
                                // Reload page to show updated status
                                window.location.reload();
                            } else {
                                alert(data.message || 'Something went wrong');
                            }
                        })
                        .catch(error => {
                            alert('Network error occurred');
                            console.error(error);
                        });
                },

                deletePayment(id) {
                    if (confirm('Are you sure? This will delete the payment record permanently.')) {
                        fetch(`/admin/payments/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                }
            }
        }
    </script>
@endpush