<div x-data="{ editMode: false }" class="px-6 py-6 pb-40">

    {{-- 1. Hero Card Summary --}}
    <div class="bg-gradient-to-br from-[#0f172a] to-[#334155] rounded-xl p-6 text-white shadow-xl mb-6 relative overflow-hidden">
        {{-- Decorative Circle --}}
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full blur-xl"></div>

        <div class="relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] text-blue-200 uppercase tracking-widest font-bold mb-1">Current Plan</p>
                    {{-- ✅ DATA FIX: Check if Plan exists --}}
                    <h3 class="text-xl font-bold leading-tight">{{ $subscription->plan->name ?? 'Unknown Plan' }}</h3>

                    <div class="flex items-center mt-3 space-x-2 text-sm text-gray-300">
                        {{-- ✅ DATA FIX: User Initial (Checks 'name' OR 'first_name') --}}
                        <div class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center text-white font-bold text-xs uppercase">
                            {{ substr($subscription->user->name ?? $subscription->user->first_name ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex flex-col">
                            {{-- ✅ DATA FIX: User Name (Checks 'name' OR 'first_name + last_name') --}}
                            <span class="text-white font-medium text-sm leading-none">
                                {{ $subscription->user->name ?? trim(($subscription->user->first_name ?? '') . ' ' . ($subscription->user->last_name ?? '')) ?: 'No Name' }}
                            </span>
                            <span class="text-blue-200 text-xs">{{ $subscription->user->email ?? 'No Email' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Status Badge --}}
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm border
                    {{ $subscription->status == 'active' ? 'bg-emerald-500/20 text-emerald-100 border-emerald-500/30' : '' }}
                    {{ $subscription->status == 'expired' ? 'bg-rose-500/20 text-rose-100 border-rose-500/30' : '' }}
                    {{ $subscription->status == 'cancelled' ? 'bg-slate-500/20 text-slate-100 border-slate-500/30' : '' }}
                    {{ $subscription->status == 'created' ? 'bg-blue-500/20 text-blue-100 border-blue-500/30' : '' }}
                    {{ $subscription->status == 'pending' ? 'bg-yellow-500/20 text-yellow-100 border-yellow-500/30' : '' }}">
                    {{ $subscription->status }}
                </span>
            </div>

            {{-- Code Section --}}
            <div class="mt-5 pt-4 border-t border-white/10 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-blue-200">ID:</span>
                    <span class="font-mono text-xs text-white bg-black/20 px-2 py-0.5 rounded select-all">
                        {{ $subscription->code ?? 'N/A' }}
                    </span>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $subscription->code }}')" class="text-[10px] bg-white/10 hover:bg-white/20 px-2 py-1 rounded text-white transition-colors flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-8a2 2 0 012-2z"></path></svg>
                    Copy
                </button>
            </div>
        </div>
    </div>

    {{-- 2. Details Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-8">
        {{-- Start Date --}}
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
            <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wide">Started On</p>
            <div class="flex items-center text-slate-700">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <p class="text-sm font-bold">
                    {{ $subscription->starts_at ? \Carbon\Carbon::parse($subscription->starts_at)->format('M d, Y') : 'Not Started' }}
                </p>
            </div>
        </div>

        {{-- End Date --}}
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
            <p class="text-[10px] uppercase font-bold text-slate-400 mb-1 tracking-wide">Expires On</p>
            <div class="flex items-center text-slate-700">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-bold">
                    {{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('M d, Y') : 'Lifetime / N/A' }}
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Manage Subscription Section --}}
    <div class="border-t border-slate-100 pt-6 mb-8">
        <div class="flex justify-between items-end mb-5">
            <div>
                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Manage Subscription</h4>
                <p class="text-xs text-slate-400 mt-1">Update status or extend validity</p>
            </div>

            <button @click="editMode = !editMode"
                class="text-xs font-bold transition-all uppercase border px-4 py-2 rounded-lg shadow-sm flex items-center"
                :class="editMode ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'">
                <svg x-show="!editMode" class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                <svg x-show="editMode" class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span x-text="editMode ? 'Close' : 'Edit'"></span>
            </button>
        </div>

        {{-- Edit Form (Expandable) --}}
        <div x-show="editMode" x-transition.opacity class="bg-blue-50/50 rounded-xl p-6 border border-blue-100 shadow-inner">
            <form action="{{ route('admin.subscriptions.update', $subscription->id) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-blue-900 uppercase mb-2">New Status</label>
                    <div class="relative">
                        <select name="status" class="block w-full text-sm border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-3 px-4 bg-white shadow-sm">
                            <option value="active" {{ $subscription->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $subscription->status == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ $subscription->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="created" {{ $subscription->status == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="pending" {{ $subscription->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-blue-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-900 uppercase mb-2">Expiry Date</label>
                    <input type="datetime-local" name="ends_at"
                        value="{{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('Y-m-d\TH:i') : '' }}"
                        class="block w-full text-sm border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-3 px-4 bg-white text-slate-700 shadow-sm">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold rounded-lg py-3 shadow-md hover:bg-blue-700 hover:shadow-lg transition-all transform active:scale-95 text-sm uppercase tracking-wide">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- 4. Payment / Invoice Section --}}
    @if($subscription->payment_id)
        <div class="bg-slate-900 rounded-xl p-5 text-white flex items-center justify-between shadow-lg ring-1 ring-white/10">
            <div class="flex items-center space-x-4">
                <div class="bg-emerald-500/10 p-2.5 rounded-lg border border-emerald-500/20">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Payment ID</p>
                    <p class="text-sm font-mono text-white font-medium">{{ $subscription->payment_id }}</p>
                </div>
            </div>
            <a href="{{ route('admin.subscriptions.invoice', $subscription->payment_id) }}"
               class="pl-3 pr-4 py-2 bg-white text-slate-900 text-xs font-bold rounded-lg hover:bg-blue-50 transition-colors flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Invoice
            </a>
        </div>
    @else
        <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-center text-amber-800 text-sm">
            <svg class="w-5 h-5 mr-3 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="font-medium">Manual Subscription (No Payment Record)</span>
        </div>
    @endif

</div>
