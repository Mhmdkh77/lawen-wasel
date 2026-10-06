<div class="flex flex-col h-full">
    <div class="p-4 flex flex-wrap items-center gap-3">
        <select wire:model.live="ratingFilter"
            class="text-sm border border-slate-200 rounded-md px-3 py-2 text-slate-700 focus:outline-none focus:border-brand-500">
            <option value="">All ratings</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
            @endfor
        </select>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3" style="width: 20%;">Passenger</th>
                    <th class="px-6 py-3" style="width: 20%;">Driver</th>
                    <th class="px-6 py-3" style="width: 15%;">Ride</th>
                    <x-table.th field='rating' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 15%;">Rating</x-table.th>
                    <th class="px-6 py-3" style="width: 30%;">Review</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($ratings as $rating)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">{{ $rating->ratingUser->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $rating->ratedUser->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.rides.show', $rating->ride_id) }}" class="text-brand-600 hover:underline">#{{ $rating->ride_id }}</a>
                        </td>
                        <td class="px-6 py-4 text-brand-500">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i > $rating->rating ? 'text-gray-200' : '' }}"></i>
                            @endfor
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $rating->review_text ?? 'No review' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            No ratings found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $ratings->links('pagination.admin', ['livewire' => true]) }}
    </div>
</div>
