<x-layout>
    <x-page-header title="Add admin" :back="route('admin.admins.index')" />

    <form action="{{ route('admin.admins.store') }}" method="POST" class="max-w-xl space-y-5 p-6">
        @csrf
        <div>
            <label for="new-admin-name" class="mb-2 block text-sm font-medium text-ink-900">Name</label>
            <input id="new-admin-name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="new-admin-email" class="mb-2 block text-sm font-medium text-ink-900">Email</label>
            <input id="new-admin-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="new-admin-password" class="mb-2 block text-sm font-medium text-ink-900">Initial password</label>
            <input id="new-admin-password" name="password" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="new-admin-password-confirmation" class="mb-2 block text-sm font-medium text-ink-900">Confirm password</label>
            <input id="new-admin-password-confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
            <input name="is_super_admin" type="checkbox" value="1" @checked(old('is_super_admin')) class="mt-1 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
            <span><span class="block text-sm font-medium text-ink-900">Super admin access</span><span class="mt-1 block text-sm text-gray-500">Can create admins and change their access or status.</span></span>
        </label>
        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-ink-900 hover:bg-brand-400">Create admin</button>
    </form>
</x-layout>
