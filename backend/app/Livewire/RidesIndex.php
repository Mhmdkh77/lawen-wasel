<?php

namespace App\Livewire;

use App\Models\Ride;
use Livewire\Component;
use Livewire\WithPagination;

class RidesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
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
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $sortField = in_array($this->sortField, ['scheduled_time', 'type', 'booked_seats', 'available_seats', 'status', 'driver_name'])
            ? $this->sortField
            : 'scheduled_time';

        $query = Ride::query()
            ->leftJoin('vehicles', 'rides.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('drivers', 'vehicles.driver_id', '=', 'drivers.id')
            ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
            ->select('rides.*')
            ->with(['driver.user', 'vehicle']);

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->where('users.name', 'like', $searchTerm)
                    ->orWhere('vehicles.brand', 'like', $searchTerm)
                    ->orWhere('rides.type', 'like', $searchTerm)
                    ->orWhere('rides.status', 'like', $searchTerm);
            });
        }

        if ($this->statusFilter) {
            $query->where('rides.status', $this->statusFilter);
        }

        if ($sortField === 'driver_name') {
            $query->orderBy('users.name', $this->sortDirection);
        } else {
            $query->orderBy('rides.' . $sortField, $this->sortDirection);
        }

        $rides = $query->paginate($this->perPage);

        return view('livewire.rides-index', [
            'rides' => $rides,
        ]);
    }
}
