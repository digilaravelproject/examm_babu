@extends('layouts.admin')

@section('title', 'Subscriptions')

@section('content')
    {{-- Main Container --}}
    <div x-data="subscriptionManager()"
         @open-drawer.window="openDrawer($event.detail.type, $event.detail.id)"
         class="space-y-8 h-full">

        {{-- Top Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Subscriptions</h1>
                <p class="text-sm text-slate-500 mt-1">Manage and track all student subscriptions.</p>
            </div>

            <button @click="openDrawer('create')"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-md hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                ADD NEW SUBSCRIPTION
            </button>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-sm grid grid-cols-1 md:grid-cols-4 gap-5">
            {{-- Search Code --}}
            <div class="relative">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Subscription Code</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="filters.code" @input.debounce.500ms="applyFilters()"
                        class="w-full pl-10 text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors py-2.5"
                        placeholder="Search by code...">
                </div>
            </div>

            {{-- Search Payment --}}
            <div class="relative">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Payment Reference</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <input type="text" x-model="filters.payment_id" @input.debounce.500ms="applyFilters()"
                        class="w-full pl-10 text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors py-2.5"
                        placeholder="Transaction ID...">
                </div>
            </div>

            {{-- Search Status --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Status</label>
                <div class="relative">
                    <select x-model="filters.status" @change="applyFilters()"
                        class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors py-2.5 appearance-none">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Search Plan --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Plan</label>
                <div class="relative">
                    <select x-model="filters.plan_id" @change="applyFilters()"
                        class="w-full text-sm border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors py-2.5 appearance-none">
                        <option value="">All Plans</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div id="table-container" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden ring-1 ring-black ring-opacity-5">
            @include('admin.subscriptions.partials.table')
        </div>

        {{-- Drawer (Sidebar for Create/Edit) --}}
        <div x-show="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900 bg-opacity-60 transition-opacity backdrop-blur-sm"
                @click="closeDrawer()"></div>

            {{-- Sidebar Container --}}
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-md transform transition ease-in-out duration-500 bg-white shadow-2xl flex flex-col h-full border-l border-slate-100">

                    {{-- Drawer Header --}}
                    <div class="px-6 py-5 bg-white border-b border-slate-100 flex justify-between items-center flex-none">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800" x-text="drawerTitle"></h2>
                            <p class="text-xs text-slate-400 mt-0.5">Enter details below</p>
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
                        {{-- Default Loading Spinner --}}
                        <div class="flex flex-col justify-center items-center h-full text-blue-600">
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
        function subscriptionManager() {
            return {
                filters: {
                    code: '',
                    payment_id: '',
                    status: '',
                    plan_id: ''
                },
                drawerOpen: false,
                drawerTitle: '',

                applyFilters() {
                    let params = new URLSearchParams(this.filters).toString();
                    fetch(`{{ route('admin.subscriptions.index') }}?${params}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => document.getElementById('table-container').innerHTML = html);
                },

                openDrawer(type, id = null) {
                    this.drawerOpen = true;
                    this.drawerTitle = type === 'create' ? 'Create Subscription' : 'Subscription Details';

                    // Reset to Loading Spinner
                    document.getElementById('drawer-content').innerHTML =
                        '<div class="flex flex-col justify-center items-center h-full text-blue-600"><svg class="animate-spin h-10 w-10 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm font-medium text-slate-500">Loading details...</span></div>';

                    let url = type === 'create' ? `{{ route('admin.subscriptions.create') }}` :
                        `/admin/subscriptions/${id}/edit`;

                    fetch(url, {
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
                }
            }
        }

        // Handle Details/Edit Button
        function openEditDrawer(id) {
            window.dispatchEvent(new CustomEvent('open-drawer', {
                detail: {
                    type: 'edit',
                    id: id
                }
            }));
        }

        function deleteSubscription(id) {
            if (confirm('Are you sure you want to delete this subscription? This action cannot be undone.')) {
                fetch(`/admin/subscriptions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(r => r.json()).then(d => {
                    if (d.success) window.location.reload();
                    else alert(d.message || 'Error deleting subscription');
                });
            }
        }
    </script>
@endpush
