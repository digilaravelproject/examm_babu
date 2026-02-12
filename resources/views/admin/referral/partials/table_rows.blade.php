@forelse($users as $user)
<tr class="transition hover:bg-gray-50/50">
    {{-- User Info --}}
    <td class="px-6 py-4">
        <div class="font-bold text-gray-900">{{ $user->full_name }}</div> {{-- Used Accessor --}}
        <div class="text-xs text-gray-500">{{ $user->email }}</div>
        <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
            Code: {{ $user->referral_code }}
        </span>
    </td>

    {{-- Form Starts Here --}}
    <form action="{{ route('admin.referral.users.update', $user->id) }}" method="POST" class="ajax-update-form">
        @csrf

        {{-- New User Commission Input --}}
        <td class="px-6 py-4 text-center">
            <input type="number" step="0.01" name="commission_percentage"
                   value="{{ $user->referralSetting->commission_percentage ?? '' }}"
                   placeholder="Default"
                   class="w-24 text-center border-gray-300 rounded-lg focus:ring-[#0777be] text-sm py-1">
            <div class="text-[10px] text-gray-400 mt-1">Leave empty for Default</div>
        </td>

        {{-- Recurring Commission Input --}}
        <!--<td class="px-6 py-4 text-center">-->
        <!--    <input type="number" step="0.01" name="recurring_commission_percentage"-->
        <!--           value="{{ $user->referralSetting->recurring_commission_percentage ?? '' }}"-->
        <!--           placeholder="Default"-->
        <!--           class="w-24 text-center border-gray-300 rounded-lg focus:ring-[#0777be] text-sm py-1 bg-blue-50/30">-->
        <!--</td>-->
        <input type="hidden" value=0  name="recurring_commission_percentage">

        {{-- Save Button --}}
        <td class="px-6 py-4 text-center">
            <button type="submit" class="px-4 py-2 bg-[#0777be] text-white rounded-lg text-xs font-bold shadow hover:bg-blue-700 transition">
                Save
            </button>
        </td>
    </form>
</tr>
@empty
<tr>
    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No users found.</td>
</tr>
@endforelse

{{-- Hidden Pagination Data for JS to read --}}
<tr class="hidden">
    <td colspan="4" id="pagination-html">
        {{ $users->links() }}
    </td>
</tr>
