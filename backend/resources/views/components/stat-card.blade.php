@props(['label', 'value', 'icon', 'href' => null])
<a href="{{ $href ?? '#' }}"
    class="flex items-center gap-4 bg-white border border-gray-200 rounded-lg px-6 py-5 hover:border-brand-500 hover:shadow-sm transition">
    <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-lg shrink-0">
        <i class="{{ $icon }}"></i>
    </div>
    <div>
        <p class="text-sm text-ink-700">{{ $label }}</p>
        <p class="text-2xl font-semibold text-ink-900">{{ $value }}</p>
    </div>
</a>
