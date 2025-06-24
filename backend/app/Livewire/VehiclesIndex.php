<?php

namespace App\Livewire;

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class VehiclesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'driver_name';
    public $sortDirection = 'desc';

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
        $vehicles = Vehicle::query()
            ->select('vehicles.*')
            ->join('drivers', 'vehicles.driver_id', '=', 'drivers.id')
            ->join('users', 'drivers.user_id', '=', 'users.id')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('vehicles.plate_number', 'like', '%' . $this->search . '%')
                        ->orWhere('vehicles.brand', 'like', '%' . $this->search . '%')
                        ->orWhere('vehicles.color', 'like', '%' . $this->search . '%')
                        ->orWhere('users.name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy(
                $this->sortField === 'driver_name' ? 'users.name' : 'vehicles.' . $this->sortField,
                $this->sortDirection
            )
            ->simplePaginate(20);

        return view('livewire.vehicles-index', [
            'vehicles' => $vehicles,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ]);
    }
}
