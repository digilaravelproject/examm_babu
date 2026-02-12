@extends('layouts.admin')

@section('content')
    {{-- Toast Notification --}}
    <div x-data="{
        show: false,
        message: '',
        init() {
            @if (session('successMessage')) this.showToast('{{ session('successMessage') }}'); @endif
            @if (session('errorMessage')) this.showToast('{{ session('errorMessage') }}'); @endif
        },
        showToast(msg) {
            this.message = msg;
            this.show = true;
            setTimeout(() => { this.show = false }, 3000);
        }
    }" x-init="init()" class="fixed top-5 right-5 z-[100]">
        <div x-show="show" x-transition
            class="flex items-center gap-3 px-6 py-3 bg-white border-l-4 border-[var(--brand-green)] shadow-2xl rounded-xl">
            <div class="p-1 bg-[var(--brand-green)]/10 text-[var(--brand-green)] rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-sm font-black tracking-tight text-gray-800 uppercase" x-text="message"></span>
        </div>
    </div>

    {{-- CKEditor CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable_inline {
            min-height: 150px;
        }

        /* Custom Toggle Switch */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #1a3c60;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #1a3c60;
        }
    </style>

    <div class="relative px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- HEADER --}}
        <div class="flex flex-col items-center justify-between gap-4 mb-6 md:flex-row">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">User Groups</h1>
                <p class="text-sm text-gray-500">Manage user access groups & permissions</p>
            </div>
            <button onclick="openCreateDrawer()" style="background-color: var(--brand-blue);"
                class="flex items-center gap-2 px-6 py-2.5 font-bold text-white transition-all rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-95 uppercase text-xs tracking-wider">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                New Group
            </button>
        </div>

        {{-- FILTERS --}}
        <div class="flex flex-col gap-4 p-4 mb-6 bg-white border border-gray-200 shadow-sm rounded-xl md:flex-row">
            <div class="relative w-full md:w-1/3">
                <input type="text" id="searchInput" placeholder="Search by name or code..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50/50 outline-none focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)] transition-all">
                <svg class="absolute w-5 h-5 text-gray-400 left-3 top-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="w-full md:w-48">
                <select id="statusFilter"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50/50 outline-none focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)]">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="w-full md:w-48">
                <select id="visibilityFilter"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50/50 outline-none focus:ring-2 focus:ring-[var(--brand-blue)]/20 focus:border-[var(--brand-blue)]">
                    <option value="">All Visibility</option>
                    <option value="0">Public</option>
                    <option value="1">Private</option>
                </select>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div id="tableContainer" class="mb-8 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            @include('admin.user-groups._table')
        </div>
    </div>

    {{-- DRAWER --}}
    <div id="drawerBackdrop" onclick="closeDrawer()"
        class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[99] hidden transition-opacity opacity-0"></div>
    <div id="drawerPanel"
        class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-[100] transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 id="drawerTitle" class="text-lg font-black tracking-tight text-gray-800 uppercase">New User Group</h2>
            <button onclick="closeDrawer()"
                class="p-2 text-gray-400 transition-colors rounded-full hover:text-gray-600 hover:bg-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="userGroupForm" action="{{ route('admin.user-groups.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            <div id="methodInputContainer"></div>

            {{-- Name --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold tracking-widest text-gray-500 uppercase">Group Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50/50 outline-none focus:bg-white focus:ring-4 focus:ring-[var(--brand-blue)]/10 focus:border-[var(--brand-blue)] transition-all @error('name') border-red-500 @enderror"
                    placeholder="e.g. Batch A Students">
                @error('name')
                    <span class="text-xs font-bold text-red-500">{{ $message }}</span>
                @enderror
            </div>

            {{-- Code removed from here as per requirement --}}

            {{-- Description --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold tracking-widest text-gray-500 uppercase">Description (Optional)</label>
                <div class="overflow-hidden border border-gray-200 rounded-xl">
                    <textarea id="descriptionEditor" name="description">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- Status Toggle --}}
            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl bg-gray-50">
                <div>
                    <label class="block text-sm font-bold text-gray-800">Active Status</label>
                    <span class="text-xs text-gray-500">Enable or disable this group.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--brand-blue)]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--brand-blue)]"></div>
                </label>
            </div>

            {{-- Visibility Toggle --}}
            <div class="flex items-center justify-between p-4 border border-gray-100 rounded-xl bg-gray-50">
                <div>
                    <label class="block text-sm font-bold text-gray-800">Private Group</label>
                    <span class="text-xs text-gray-500">Only admins can see this group.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="visibility" value="0">
                    <input type="checkbox" name="visibility" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                </label>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-100">
                <button type="submit" id="submitBtn" style="background-color: var(--brand-blue);"
                    class="w-full px-6 py-3.5 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-95 transition-all">Create Group</button>
            </div>
        </form>
    </div>

    <script>
        const drawerPanel = document.getElementById('drawerPanel');
        const drawerBackdrop = document.getElementById('drawerBackdrop');
        const form = document.getElementById('userGroupForm');
        const drawerTitle = document.getElementById('drawerTitle');
        const submitBtn = document.getElementById('submitBtn');
        const methodInputContainer = document.getElementById('methodInputContainer');
        let myEditor;

        // Init Editor
        ClassicEditor.create(document.querySelector('#descriptionEditor'), {
            toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo']
        }).then(editor => {
            myEditor = editor;
        }).catch(error => {
            console.error(error);
        });

        function openDrawer() {
            drawerBackdrop.classList.remove('hidden');
            setTimeout(() => drawerBackdrop.classList.remove('opacity-0'), 10);
            drawerPanel.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            drawerPanel.classList.add('translate-x-full');
            drawerBackdrop.classList.add('opacity-0');
            setTimeout(() => drawerBackdrop.classList.add('hidden'), 300);
            document.body.style.overflow = 'auto';
        }

        // Auto Open Drawer if Validation Errors
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                openDrawer();
            });
        @endif

        function openCreateDrawer() {
            form.reset();
            form.action = "{{ route('admin.user-groups.store') }}";
            methodInputContainer.innerHTML = '';

            // Clear name input specifically (reset doesn't always clear 'value' attribute)
            form.querySelector('input[name="name"]').value = '';

            if (myEditor) myEditor.setData('');

            form.querySelector('input[type="checkbox"][name="status"]').checked = true;
            form.querySelector('input[type="checkbox"][name="visibility"]').checked = false;

            drawerTitle.innerText = "New User Group";
            submitBtn.innerText = "CREATE GROUP";

            // Clear any error styles
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            document.querySelectorAll('.text-red-500').forEach(el => el.remove());

            openDrawer();
        }

        function editUserGroup(id) {
            form.reset();
            // Clear errors
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            document.querySelectorAll('.text-red-500').forEach(el => el.remove());

            fetch(`/admin/user-groups/${id}/edit`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    form.querySelector('input[name="name"]').value = data.name;
                    // Note: Code input is removed, so we don't populate it.

                    if (myEditor) myEditor.setData(data.description || '');

                    form.querySelector('input[type="checkbox"][name="status"]').checked = (data.status == 1);
                    form.querySelector('input[type="checkbox"][name="visibility"]').checked = (data.visibility == 1);

                    form.action = data.update_url;
                    methodInputContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    drawerTitle.innerText = "Edit User Group";
                    submitBtn.innerText = "UPDATE GROUP";
                    openDrawer();
                })
                .catch(err => {
                    alert('Error loading group data.');
                    console.error(err);
                });
        }

        // Filters Logic
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const visibilityFilter = document.getElementById('visibilityFilter');
            const tableContainer = document.getElementById('tableContainer');
            let debounceTimer;

            function fetchResults() {
                const url = "{{ route('admin.user-groups.index') }}";
                const params = new URLSearchParams();
                if (searchInput.value) params.set('search', searchInput.value);
                if (statusFilter.value) params.set('status', statusFilter.value);
                if (visibilityFilter.value) params.set('visibility', visibilityFilter.value);

                fetch(`${url}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.text()).then(html => {
                        tableContainer.innerHTML = html;
                        // Re-attach pagination listeners after content update
                        // (You can move pagination listener logic here if using a common function)
                    });
            }

            searchInput.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchResults, 300);
            });
            statusFilter.addEventListener('change', fetchResults);
            visibilityFilter.addEventListener('change', fetchResults);
        });

        // Pagination Event Delegation (Better than re-attaching)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination-wrapper a')) {
                e.preventDefault();
                const link = e.target.closest('a');
                fetch(link.getAttribute('href'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text()).then(html => {
                    document.getElementById('tableContainer').innerHTML = html;
                });
            }
        });
    </script>
@endsection
