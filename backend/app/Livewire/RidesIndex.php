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
    public $sortField = 'drivers.name';
    public $sortDirection = 'asc';
    public $perPage = 50;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $rides = Ride::query()
            ->with(['driver', 'vehicle']) // Eager load relationships
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereHas('driver', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('vehicle', function ($q) {
                            $q->where('type', 'like', '%' . $this->search . '%')
                                ->orWhere('license_plate', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->join('users as drivers', 'rides.driver_id', '=', 'drivers.id')
            ->join('vehicles', 'rides.vehicle_id', '=', 'vehicles.id')
            ->select('rides.*') // Avoid column conflicts
            ->orderBy($this->sortField, $this->sortDirection)
            ->simplePaginate($this->perPage);


        return view('livewire.rides-index', [
            'rides' => $rides,
            'sortDirection' => $this->sortDirection,
            'sortField' => $this->sortField
        ]);
    }
}
