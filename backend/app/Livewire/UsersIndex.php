<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination;

    public $role = '';
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

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
        $sortField = in_array($this->sortField, ['name', 'email', 'phone']) ? $this->sortField : 'name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $users = User::where('role', $this->role)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhereHas('city', function ($cityQuery) {
                        $cityQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy($sortField, $sortDirection)
            ->with('city')
            ->paginate(20);

        return view('livewire.users-index', [
            'users' => $users,
            'sortDirection' => $this->sortDirection,
            'sortField' => $this->sortField
        ]);
    }
}
