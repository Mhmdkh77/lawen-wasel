<?php

namespace App\Livewire;

use App\Models\RideOffer;
use Livewire\Component;
use Livewire\WithPagination;

class RideOffersIndex extends Component
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
        $sortField = in_array($this->sortField, ['created_at', 'offered_price', 'status'])
            ? $this->sortField
            : 'created_at';

        $rideOffers = RideOffer::query()
            ->join('drivers', 'ride_offers.driver_id', '=', 'drivers.id')
            ->join('users', 'drivers.user_id', '=', 'users.id')
            ->select('ride_offers.*')
            ->with(['driver.user', 'rideRequest.passenger.user'])
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where('users.name', 'like', $searchTerm);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('ride_offers.status', $this->statusFilter);
            })
            ->orderBy('ride_offers.' . $sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.ride-offers-index', [
            'rideOffers' => $rideOffers,
        ]);
    }
}
