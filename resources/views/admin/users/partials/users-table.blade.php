<div class="overflow-x-auto min-h-[400px]">
    <table class="w-full text-sm text-left border-collapse">
        <thead class="text-xs font-bold text-gray-700 uppercase border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-6 py-4">Name</th>
                <th class="px-6 py-4">User Name</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="transition-colors hover:bg-blue-50/30 group">

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center text-xs font-bold text-indigo-700 bg-indigo-100 rounded-full h-9 w-9 ring-1 ring-indigo-200">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </div>
                            <div class="font-medium text-gray-900">{{ $user->full_name }}</div>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 font-mono text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded">
                            {{ $user->user_name }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                        {{ $user->email }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        @foreach($user->roles as $role)
                            @php
                                $colorClass = match($role->name) {
                                    'admin' => 'bg-red-50 text-red-700 border-red-100',
                                    'student' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'instructor' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    default => 'bg-gray-50 text-gray-700 border-gray-100',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border uppercase tracking-wide {{ $colorClass }}">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>

                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div x-data="{ active: {{ $user->is_active ? 'true' : 'false' }} }" class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="checkbox" class="sr-only peer"
                                    x-model="active"
                                    @change="toggleUserStatus({{ $user->id }}, '{{ $user->first_name }}', $el)">

                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                Actions
                                <svg class="w-4 h-4 ml-1 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak class="absolute right-0 z-50 w-32 mt-2 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600">Edit</a>
                                    <button onclick="deleteUser({{ $user->id }}, '{{ $user->first_name }}')" class="block w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50">Delete</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <p>No users found matching your search.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
    <div class="text-xs text-gray-500">
        {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}
    </div>
    <div>{{ $users->links() }}</div>
</div>
