<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class DriversIndex extends Component
{
    use WithPagination;

    public $role = 'driver';
    public $search = '';
    public $sortField = 'drivers.is_verified';
    public $sortDirection = 'asc';

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

        $users = User::where('role', $this->role)
            ->leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
            ->where(function ($query) {
                $query->where('users.name', 'like', '%' . $this->search . '%')
                    ->orWhere('users.email', 'like', '%' . $this->search . '%')
                    ->orWhere('users.phone', 'like', '%' . $this->search . '%')
                    ->orWhereHas('city', function ($cityQuery) {
                        $cityQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->select('users.*')
            ->with(['city', 'driver'])
            ->simplePaginate(50);

        return view('livewire.drivers-index', [
            'users' => $users,
            'sortDirection' => $this->sortDirection,
            'sortField' => $this->sortField,
        ]);
    }
}
