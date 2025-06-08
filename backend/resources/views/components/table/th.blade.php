@props(['field', 'sortField', 'sortDirection'])

<th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('{{ $field }}')">
   <div class="flex items-center">
      {{ $slot }}

      @if ($sortField === $field)
        @if ($sortDirection === 'asc')
         <i class="ml-2 fa-solid fa-sort-down"></i>
       @else
         <i class="ml-2 fa-solid fa-sort-up"></i>
       @endif
     @else
        <i class="ml-2 fa-solid fa-sort"></i>
     @endif
   </div>
</th>