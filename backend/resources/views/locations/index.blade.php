<x-layout>
    <x-page-header title="Locations">
        <a href="{{ route('admin.locations.create') }}"
            class="inline-flex items-center gap-2 bg-brand-500 text-ink-900 text-sm font-semibold px-4 py-2 rounded-md hover:bg-brand-600 transition">
            <i class="fa-solid fa-plus"></i> New Location
        </a>
    </x-page-header>
    <livewire:location-index />
</x-layout>
