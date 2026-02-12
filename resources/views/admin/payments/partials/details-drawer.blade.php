<div x-data="{ editMode: false }" class="px-6 py-6 pb-40">

    {{-- 1. Header Card --}}
    <div
        class="bg-gradient-to-br from-[#0f172a] to-[#334155] rounded-xl p-6 text-white shadow-xl mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>

        <div class="relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] text-blue-200 uppercase tracking-widest font-bold mb-1">Total Amount</p>
                    <h3 class="text-2xl font-bold leading-tight">{{ $payment->currency ?? 'INR' }} {{ $payment->amount }}
                    </h3>
                    <p class="text-sm text-gray-300 mt-1">{{ $payment->plan->name ?? 'Unknown Plan' }}</p>
                </div>

                {{-- Status Badge --}}
                <span
                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm border
                    {{ $payment->status == 'success' ? 'bg-emerald-500/20 text-emerald-100 border-emerald-500/30' : '' }}
                    {{ $payment->status == 'failed' ? 'bg-rose-500/20 text-rose-100 border-rose-500/30' : '' }}
                    {{ $payment->status == 'pending' ? 'bg-yellow-500/20 text-yellow-100 border-yellow-500/30' : '' }}">
                    {{ $payment->status }}
                </span>
            </div>

            <div class="mt-5 pt-4 border-t border-white/10 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-blue-200">ID:</span>
                    <span class="font-mono text-xs text-white bg-black/20 px-2 py-0.5 rounded select-all">
                        {{ $payment->payment_id }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Details Grid --}}
    <div class="grid grid-cols-1 gap-4 mb-8">
        {{-- User Info --}}
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex justify-between items-center">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wide">User</p>
                <p class="text-sm font-bold text-slate-800">{{ $payment->user->first_name ?? 'User' }}
                    {{ $payment->user->last_name ?? '' }}</p>
                <p class="text-xs text-slate-500">{{ $payment->user->email }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- Date --}}
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wide">Date</p>
                <p class="text-sm font-bold text-slate-700">
                    {{ $payment->created_at->format('M d, Y') }}
                </p>
            </div>
            {{-- Method --}}
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wide">Method</p>
                <p class="text-sm font-bold text-slate-700 uppercase">
                    {{ $payment->method }}
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Manage Status (Equivalent to Edit Form in Vue) --}}
    <div class="border-t border-slate-100 pt-6 mb-8">
        <div class="flex justify-between items-end mb-5">
            <div>
                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Update Status</h4>
            </div>
            <button @click="editMode = !editMode"
                class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors uppercase">
                <span x-text="editMode ? 'Cancel' : 'Edit'"></span>
            </button>
        </div>

        <div x-show="editMode" class="bg-blue-50/50 rounded-xl p-6 border border-blue-100 shadow-inner">
            <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-blue-900 uppercase mb-2">Status</label>
                    <select name="status"
                        class="block w-full text-sm border-blue-200 rounded-lg focus:ring-blue-500 py-3 px-4 bg-white">
                        <option value="success" {{ $payment->status == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="pending" {{ $payment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ $payment->status == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ $payment->status == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold rounded-lg py-3 shadow-md hover:bg-blue-700 text-sm uppercase">
                    Update
                </button>
            </form>
        </div>

        <div x-show="!editMode" class="bg-gray-50 p-4 rounded-lg text-sm text-gray-600">
            Current Status: <span class="font-bold uppercase">{{ $payment->status }}</span>
        </div>
    </div>

    {{-- 4. Invoice Section --}}
    @if ($payment->invoice_no || $payment->payment_id)
        <div class="bg-slate-900 rounded-xl p-5 text-white flex items-center justify-between shadow-lg">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400">Invoice No</p>
                <p class="text-sm font-mono text-white font-medium">{{ $payment->invoice_no ?? $payment->payment_id }}
                </p>
            </div>
            {{-- Aapka wahi download route jo subscription me tha --}}
            <a href="{{ route('admin.payments.invoice', $payment->id) }}" target="_blank"
                class="px-4 py-2 bg-white text-slate-900 text-xs font-bold rounded-lg hover:bg-blue-50 flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download PDF
            </a>
        </div>
    @endif

</div>
