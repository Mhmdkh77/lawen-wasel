@props(['field', 'sortField', 'sortDirection'])

<th {{ $attributes->merge(['scope' => 'col', 'class' => 'px-6 py-3 cursor-pointer select-none']) }}
  wire:click="sortBy('{{ $field }}')">
  <div class="flex items-center gap-1">
    {{ $slot }}

    @if ($sortField === $field)
      @if ($sortDirection === 'asc')
      <i class="fa-solid fa-sort-down text-brand-600"></i>
      @else
      <i class="fa-solid fa-sort-up text-brand-600"></i>
      @endif
  @else
    <i class="fa-solid fa-sort text-gray-300"></i>
  @endif
  </div>
</th>
