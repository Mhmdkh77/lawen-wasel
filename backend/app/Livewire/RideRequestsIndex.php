<?php

namespace App\Livewire;

use App\Models\RideRequest;
use Livewire\Component;
use Livewire\WithPagination;

class RideRequestsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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
        $sortField = in_array($this->sortField, ['created_at', 'nb_seats_requested', 'status', 'type'])
            ? $this->sortField
            : 'created_at';

        $rideRequests = RideRequest::query()
            ->join('passengers', 'ride_requests.passenger_id', '=', 'passengers.id')
            ->join('users', 'passengers.user_id', '=', 'users.id')
            ->select('ride_requests.*')
            ->with(['passenger.user', 'institutionLocation'])
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'like', $searchTerm)
                        ->orWhereHas('institutionLocation', fn($q2) => $q2->where('name', 'like', $searchTerm));
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('ride_requests.status', $this->statusFilter);
            })
            ->orderBy('ride_requests.' . $sortField, $this->sortDirection)
            ->simplePaginate(20);

        return view('livewire.ride-requests-index', [
            'rideRequests' => $rideRequests,
        ]);
    }
}
