<li>
    <a href="{{ $href }}"
        class="flex items-center gap-3 px-3.5 py-2.5 text-sm rounded-lg mx-2 border-l-4 transition-colors
            {{ $active ? 'bg-brand-50 text-ink-900 font-semibold border-brand-500' : 'text-ink-700 border-transparent hover:bg-gray-100' }}"
        @click="sidebarOpen = false">
        <i class="{{ $icon }} w-4 text-center"></i>
        <span>{{ $slot }}</span>
    </a>
</li>
