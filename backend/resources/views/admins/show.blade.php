<x-layout>
    <x-page-header title="Admin Profile" :back="route('admin.admins.index')" />
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-6 rounded-lg border border-gray-200 max-w-2xl">
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Name</h3>
                <p class="text-ink-900 capitalize">{{ $admin->name }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Email</h3>
                <p class="text-ink-900">{{ $admin->email }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Gender</h3>
                <p class="text-ink-900">{{ $admin->gender ? ucfirst($admin->gender) : 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Joined</h3>
                <p class="text-ink-900">{{ $admin->created_at->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
</x-layout>
