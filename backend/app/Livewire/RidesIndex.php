<?php

namespace App\Livewire;

use App\Models\Ride;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class RidesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'scheduled_time';
    public $sortDirection = 'asc';
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // toggle sort direction
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Ride::with(['driver.user', 'vehicle']);

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('driver', function ($q2) use ($searchTerm) {
                    $q2->whereHas('user', function ($q3) use ($searchTerm) {
                        $q3->where('name', 'like', $searchTerm);
                    });
                })
                    ->orWhereHas('vehicle', function ($q4) use ($searchTerm) {
                        $q4->where('brand', 'like', $searchTerm);
                    })
                    ->orWhere('type', 'like', $searchTerm)
                    ->orWhere('status', 'like', $searchTerm);
            });
        }

        // Sorting on simple columns only:
        if (in_array($this->sortField, ['scheduled_time', 'type', 'booked_seats', 'available_seats', 'status'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else if ($this->sortField === 'driver_name') {
            // We can't sort directly by related model, so skip or do manual sorting after fetching (optional)
        }

        $rides = $query->paginate($this->perPage);

        return view('livewire.rides-index', [
            'rides' => $rides,
        ]);
    }
}
