<x-layout>
    <x-page-header title="Admin Users">
        <a href="{{ route('admin.admins.create') }}" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-ink-900 hover:bg-brand-400">Add admin</a>
    </x-page-header>
    <div class="flex-1 overflow-auto">
        <table class="w-full min-w-[680px] text-left text-sm text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Access</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900">
                            <a href="{{ route('admin.admins.show', $admin) }}" class="hover:text-brand-600">{{ $admin->name }}</a>
                            @if ($admin->is(auth('admin')->user())) <span class="ml-1 text-xs font-normal text-gray-500">(you)</span> @endif
                        </td>
                        <td class="px-6 py-4">{{ $admin->email }}</td>
                        <td class="px-6 py-4">{{ $admin->is_super_admin ? 'Super admin' : 'Admin' }}</td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $admin->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $admin->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $admin->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            No admin users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $admins->links('pagination.admin') }}
    </div>
</x-layout>
