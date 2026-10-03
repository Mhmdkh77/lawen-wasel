@if (session('success') || session('error') || session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
        class="mx-6 mt-4 rounded-lg border px-4 py-3 text-sm flex items-start justify-between gap-4
            {{ session('error') ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' }}">
        <span>{{ session('success') ?? session('status') ?? session('error') }}</span>
        <button @click="show = false" class="opacity-60 hover:opacity-100 leading-none">&times;</button>
    </div>
@endif
