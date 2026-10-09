<x-layout>
    <x-page-header title="Driver profile" :back="route('admin.drivers.index')" />

    <div class="space-y-6 p-6">
        <x-profile-hero
            :name="$user->name"
            role="Driver"
            :image="$user->image ? Storage::url($user->image) : null"
            :subtitle="$user->city?->name"
            :joined="$user->created_at?->format('F Y')"
        >
            <span id="verification-status" role="status" class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $user->driver->is_verified ? 'bg-green-100 text-green-800' : 'bg-white/15 text-white' }}">
                {{ $user->driver->is_verified ? 'Verified' : 'Unverified' }}
            </span>
            <button id="verification-action" type="button" class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-ink-900 transition hover:bg-brand-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-400 disabled:cursor-wait disabled:opacity-60">
                {{ $user->driver->is_verified ? 'Revoke verification' : 'Verify driver' }}
            </button>
        </x-profile-hero>
        <p id="verification-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></p>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-base font-semibold text-ink-900">Account details</h3>
                <dl class="mt-4 divide-y divide-gray-100 text-sm">
                    <div class="py-3">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="mt-1 break-words font-medium text-ink-900">{{ $user->email }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">Gender</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->gender ? ucfirst($user->gender) : 'Not provided' }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">City</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->city?->name ?? 'Not provided' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-base font-semibold text-ink-900">Saved location</h3>
                    @if ($user->latitude !== null && $user->longitude !== null)
                        <span class="text-xs text-gray-500">{{ number_format((float) $user->latitude, 5) }}, {{ number_format((float) $user->longitude, 5) }}</span>
                    @endif
                </div>
                <x-profile-map :latitude="$user->latitude" :longitude="$user->longitude" :label="$user->name" />
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(240px,0.45fr)]">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-ink-900">Vehicles</h3>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $user->driver->vehicles->count() }}</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($user->driver->vehicles as $vehicle)
                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-gray-50">
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-900">{{ $vehicle->brand }}</span>
                                <span class="mt-1 block text-xs text-gray-500">{{ $vehicle->plate_number }} · {{ $vehicle->color }} · {{ $vehicle->capacity }} seats</span>
                            </span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-gray-400" aria-hidden="true"></i>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-sm text-gray-500">No vehicles registered.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-base font-semibold text-ink-900">Driver license</h3>
                <p class="mt-2 text-sm leading-6 text-gray-500">License document submitted with this driver account.</p>
                @if ($user->driver->driver_license)
                    <button id="open-license" type="button" class="mt-5 inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-semibold text-ink-900 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                        <i class="fa-regular fa-file-lines" aria-hidden="true"></i> View license
                    </button>
                @else
                    <p class="mt-5 text-sm font-medium text-gray-500">No license uploaded.</p>
                @endif
            </section>
        </div>
    </div>

    @if ($user->driver->driver_license)
        <dialog id="license-dialog" aria-label="Driver license" class="m-auto w-[min(92vw,52rem)] rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-ink-900/70">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="font-semibold text-ink-900">Driver license</h3>
                <button id="close-license" type="button" aria-label="Close license" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-ink-900">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div id="license-content" class="max-h-[75vh] overflow-auto p-5"></div>
        </dialog>
    @endif

    <script>
        const verificationButton = document.getElementById('verification-action');
        const verificationStatus = document.getElementById('verification-status');
        const verificationError = document.getElementById('verification-error');

        verificationButton.addEventListener('click', async () => {
            verificationButton.disabled = true;
            verificationError.classList.add('hidden');

            try {
                const response = await fetch(@json(route('admin.drivers.toggle-verification', $user->driver)), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                });
                if (!response.ok) throw new Error('Verification update failed');

                const verified = Boolean((await response.json()).status);
                verificationStatus.textContent = verified ? 'Verified' : 'Unverified';
                verificationStatus.className = verified
                    ? 'inline-flex rounded-full bg-green-100 px-3 py-1.5 text-xs font-semibold text-green-800'
                    : 'inline-flex rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white';
                verificationButton.textContent = verified ? 'Revoke verification' : 'Verify driver';
            } catch (error) {
                verificationError.textContent = 'Could not update verification. Please try again.';
                verificationError.classList.remove('hidden');
            } finally {
                verificationButton.disabled = false;
            }
        });

        @if ($user->driver->driver_license)
            const licenseDialog = document.getElementById('license-dialog');
            document.getElementById('open-license').addEventListener('click', () => {
                const url = @json(Storage::url($user->driver->driver_license));
                const content = document.getElementById('license-content');
                const viewer = document.createElement(url.toLowerCase().split('?')[0].endsWith('.pdf') ? 'iframe' : 'img');
                viewer.src = url;
                viewer.className = 'mx-auto max-h-[68vh] w-full rounded-lg object-contain';
                viewer.title = 'Driver license';
                if (viewer.tagName === 'IFRAME') {
                    viewer.style.height = '68vh';
                } else {
                    viewer.alt = 'Driver license';
                }
                content.replaceChildren(viewer);
                licenseDialog.showModal();
            });
            document.getElementById('close-license').addEventListener('click', () => licenseDialog.close());
        @endif
    </script>
</x-layout>
