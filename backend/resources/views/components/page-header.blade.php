@props(['title', 'back' => null])
<div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-200">
    <div class="flex items-center gap-3">
        @if ($back)
            <a href="{{ $back }}" class="text-ink-700 hover:text-ink-900">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        @endif
        <h1 class="text-xl font-semibold text-ink-900">{{ $title }}</h1>
    </div>
    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>
</div>
