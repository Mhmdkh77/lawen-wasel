<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class BookingsIndex extends Component
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
        $sortField = in_array($this->sortField, ['created_at', 'nb_seats', 'price', 'status'])
            ? $this->sortField
            : 'created_at';

        $bookings = Booking::query()
            ->join('passengers', 'bookings.passenger_id', '=', 'passengers.id')
            ->join('users', 'passengers.user_id', '=', 'users.id')
            ->select('bookings.*')
            ->with(['passenger.user', 'ride'])
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'like', $searchTerm)
                        ->orWhere('bookings.ride_id', 'like', $searchTerm);
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('bookings.status', $this->statusFilter);
            })
            ->orderBy('bookings.' . $sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.bookings-index', [
            'bookings' => $bookings,
        ]);
    }
}
