<?php

namespace App\Livewire;

use App\Models\RideTemplateGroup;
use Livewire\Component;
use Livewire\WithPagination;

class RideTemplateGroupsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $activeFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActiveFilter()
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
        $sortField = in_array($this->sortField, ['name', 'is_active', 'created_at']) ? $this->sortField : 'name';

        $groups = RideTemplateGroup::query()
            ->join('drivers', 'ride_template_groups.driver_id', '=', 'drivers.id')
            ->join('users', 'drivers.user_id', '=', 'users.id')
            ->select('ride_template_groups.*')
            ->withCount('rideTemplates')
            ->with(['driver.user'])
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ride_template_groups.name', 'like', $searchTerm)
                        ->orWhere('users.name', 'like', $searchTerm);
                });
            })
            ->when($this->activeFilter !== '', function ($query) {
                $query->where('ride_template_groups.is_active', $this->activeFilter === '1');
            })
            ->orderBy('ride_template_groups.' . $sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.ride-template-groups-index', [
            'groups' => $groups,
        ]);
    }
}
