<x-layout>
    <x-page-header title="Admin profile" :back="route('admin.admins.index')" />

    <div class="space-y-6 p-6">
        <section class="max-w-3xl rounded-xl border border-gray-200 bg-gray-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $admin->is_super_admin ? 'Super administrator' : 'Administrator' }}</p>
            <h2 class="mt-2 text-xl font-semibold text-ink-900">{{ $admin->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Joined {{ $admin->created_at?->format('F Y') ?? 'recently' }}</p>
        </section>

        <section class="max-w-3xl rounded-xl border border-gray-200 bg-white p-5">
            <h3 class="text-base font-semibold text-ink-900">Account details</h3>
            <dl class="mt-4 grid gap-x-8 text-sm sm:grid-cols-2">
                <div class="border-t border-gray-100 py-4">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="mt-1 break-all font-medium text-ink-900">{{ $admin->email }}</dd>
                </div>
                <div class="border-t border-gray-100 py-4">
                    <dt class="text-gray-500">Gender</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ $admin->gender ? ucfirst($admin->gender) : 'Not provided' }}</dd>
                </div>
                <div class="border-t border-gray-100 py-4">
                    <dt class="text-gray-500">Joined</dt>
                    <dd class="mt-1 font-medium text-ink-900">{{ $admin->created_at?->format('F j, Y') ?? 'Not available' }}</dd>
                </div>
                <div class="border-t border-gray-100 py-4">
                    <dt class="text-gray-500">Account ID</dt>
                    <dd class="mt-1 font-medium text-ink-900">#{{ $admin->id }}</dd>
                </div>
                <div class="border-t border-gray-100 py-4">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="mt-1 font-medium {{ $admin->is_active ? 'text-green-700' : 'text-gray-600' }}">{{ $admin->is_active ? 'Active' : 'Disabled' }}</dd>
                </div>
            </dl>
        </section>

        @if ($admin->is(auth('admin')->user()))
            <a href="{{ route('admin.account.show') }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-ink-900 hover:bg-brand-400">Edit my account</a>
        @else
            <section class="max-w-3xl rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-base font-semibold text-ink-900">Manage access</h3>
                <p class="mt-1 text-sm text-gray-500">Changes to access take effect on the next request.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form action="{{ route('admin.admins.role', $admin) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-ink-900 hover:bg-gray-50">
                            {{ $admin->is_super_admin ? 'Remove super admin access' : 'Make super admin' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.admins.status', $admin) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-medium {{ $admin->is_active ? 'border-red-200 text-red-700 hover:bg-red-50' : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                            {{ $admin->is_active ? 'Disable account' : 'Enable account' }}
                        </button>
                    </form>
                </div>
            </section>
        @endif
    </div>
</x-layout>
