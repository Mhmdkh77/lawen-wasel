<x-layout>
    <x-page-header title="{{ $rideTemplateGroup->name }}" :back="route('admin.ride-template-groups.index')">
        <x-status-badge :status="$rideTemplateGroup->is_active ? 'verified' : 'unverified'"
            :label="$rideTemplateGroup->is_active ? 'Active' : 'Inactive'" />
    </x-page-header>

    <div class="p-6 space-y-6 overflow-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-6 rounded-lg border border-gray-200">
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Driver</h3>
                <a href="{{ route('admin.drivers.show', $rideTemplateGroup->driver->user_id) }}"
                    class="text-brand-600 hover:underline">{{ $rideTemplateGroup->driver->user->name ?? 'N/A' }}</a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Locations</h3>
                <p class="text-ink-900">
                    {{ $rideTemplateGroup->locationGroup->locations->pluck('name')->join(', ') ?: 'No locations set' }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <h3 class="font-semibold text-ink-900 px-4 py-3 border-b border-gray-100">Scheduled Templates</h3>
            @if($rideTemplateGroup->rideTemplates->isNotEmpty())
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="py-2 px-4">Vehicle</th>
                            <th class="py-2 px-4">Type</th>
                            <th class="py-2 px-4">Time</th>
                            <th class="py-2 px-4">Recurring Days</th>
                            <th class="py-2 px-4">Status</th>
                            <th class="py-2 px-4">Last Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rideTemplateGroup->rideTemplates as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4">{{ $template->vehicle->brand ?? 'N/A' }} ({{ $template->vehicle->plate_number ?? 'N/A' }})</td>
                                <td class="py-2 px-4">{{ ucfirst(str_replace('_', ' ', $template->type)) }}</td>
                                <td class="py-2 px-4">{{ $template->scheduled_time }}</td>
                                <td class="py-2 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($template->recurring_days ?? [] as $day)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-xs capitalize">{{ substr($day, 0, 3) }}</span>
                                        @empty
                                            <span class="text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-2 px-4">
                                    <x-status-badge :status="$template->is_active ? 'verified' : 'unverified'" :label="$template->is_active ? 'Active' : 'Inactive'" />
                                </td>
                                <td class="py-2 px-4">{{ $template->last_generated_at?->format('M j, Y') ?? 'Never' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-400 p-4">No templates defined for this group.</p>
            @endif
        </div>
    </div>
</x-layout>
