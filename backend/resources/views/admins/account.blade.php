<x-layout>
    <x-page-header title="My account" />

    <div class="grid max-w-5xl gap-6 p-6 lg:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-ink-900">Account details</h2>
            <p class="mt-1 text-sm text-gray-500">Your name and sign-in email.</p>
            <form action="{{ route('admin.account.update') }}" method="POST" class="mt-6 space-y-5">
                @csrf @method('PATCH')
                <div>
                    <label for="account-name" class="mb-2 block text-sm font-medium text-ink-900">Name</label>
                    <input id="account-name" name="name" type="text" value="{{ old('name', $admin->name) }}" required autocomplete="name" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="account-email" class="mb-2 block text-sm font-medium text-ink-900">Email</label>
                    <input id="account-email" name="email" type="email" value="{{ old('email', $admin->email) }}" required autocomplete="email" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="account-current-password" class="mb-2 block text-sm font-medium text-ink-900">Current password <span class="font-normal text-gray-500">(required to change email)</span></label>
                    <input id="account-current-password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-ink-900 hover:bg-brand-400">Save details</button>
            </form>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-ink-900">Change password</h2>
            <p class="mt-1 text-sm text-gray-500">You will sign in again after changing it.</p>
            <form action="{{ route('admin.account.password') }}" method="POST" class="mt-6 space-y-5">
                @csrf @method('PATCH')
                <div>
                    <label for="password-current" class="mb-2 block text-sm font-medium text-ink-900">Current password</label>
                    <input id="password-current" name="password_current" type="password" required autocomplete="current-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('password_current') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password-new" class="mb-2 block text-sm font-medium text-ink-900">New password</label>
                    <input id="password-new" name="password" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password-confirmation" class="mb-2 block text-sm font-medium text-ink-900">Confirm new password</label>
                    <input id="password-confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-ink-900 hover:bg-brand-400">Update password</button>
            </form>
        </section>
    </div>
</x-layout>
