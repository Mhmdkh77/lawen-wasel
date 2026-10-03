<?php

namespace App\Livewire;

use App\Models\Rating;
use Livewire\Component;
use Livewire\WithPagination;

class RatingsIndex extends Component
{
    use WithPagination;

    public $ratingFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function updatingRatingFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $sortField = in_array($this->sortField, ['created_at', 'rating']) ? $this->sortField : 'created_at';

        $ratings = Rating::query()
            ->with(['ratedUser', 'ratingUser', 'ride'])
            ->when($this->ratingFilter, function ($query) {
                $query->where('rating', $this->ratingFilter);
            })
            ->orderBy($sortField, $this->sortDirection)
            ->simplePaginate(20);

        return view('livewire.ratings-index', [
            'ratings' => $ratings,
        ]);
    }
}
