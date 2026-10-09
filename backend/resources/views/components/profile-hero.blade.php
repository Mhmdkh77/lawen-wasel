@props(['name', 'role', 'image' => null, 'joined' => null, 'subtitle' => null])

<section class="relative overflow-hidden rounded-2xl bg-ink-900 px-6 py-6 text-white sm:px-8">
    <div class="absolute -right-12 -top-20 h-52 w-52 rounded-full bg-brand-400/10" aria-hidden="true"></div>
    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="h-24 w-24 shrink-0 rounded-full border-4 border-white/20 object-cover shadow-sm">
        @else
            <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/10 text-3xl font-semibold" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <span class="inline-flex rounded-full bg-brand-400/15 px-3 py-1 text-xs font-semibold text-brand-300">{{ $role }}</span>
            <h2 class="mt-2 break-words text-2xl font-semibold tracking-tight sm:text-3xl">{{ $name }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-200">{{ $subtitle }}</p>
            @endif
            @if ($joined)
                <p class="mt-2 text-xs text-gray-300">Joined {{ $joined }}</p>
            @endif
        </div>

        @if ($slot->isNotEmpty())
            <div class="flex shrink-0 flex-wrap items-center gap-3 sm:justify-end">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
