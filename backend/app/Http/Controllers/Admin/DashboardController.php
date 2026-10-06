<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\Vehicle;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $weekEnd = $today->copy()->addDays(7);

        $overview = [
            'rides_today' => Ride::whereDate('scheduled_time', $today->toDateString())
                ->where('status', '!=', 'canceled')->count(),
            'rides_in_progress' => Ride::where('status', 'active')->count(),
            'active_bookings' => Booking::where('status', 'active')->count(),
            'open_requests' => RideRequest::whereIn('status', ['pending', 'driver_offered'])->count(),
        ];

        $attention = [
            'pending_requests' => RideRequest::where('status', 'pending')->count(),
            'driver_offered_requests' => RideRequest::where('status', 'driver_offered')->count(),
            'pending_offers' => RideOffer::where('status', 'pending')->count(),
            'unverified_drivers' => Driver::where('is_verified', false)->count(),
        ];

        $upcomingRides = Ride::with('vehicle.driver.user')
            ->where('scheduled_time', '>=', now())
            ->whereIn('status', ['pending', 'active'])
            ->orderBy('scheduled_time')
            ->limit(5)
            ->get();

        $recentRequests = RideRequest::with('passenger.user', 'institutionLocation')
            ->whereIn('status', ['pending', 'driver_offered'])
            ->latest()
            ->limit(4)
            ->get();

        $ridesByDay = Ride::selectRaw('date(scheduled_time) as day, count(*) as total')
            ->where('scheduled_time', '>=', $today)
            ->where('scheduled_time', '<', $weekEnd)
            ->whereIn('status', ['pending', 'active'])
            ->groupByRaw('date(scheduled_time)')
            ->pluck('total', 'day');

        $schedule = collect(range(0, 6))->map(function ($offset) use ($today, $ridesByDay) {
            $date = $today->copy()->addDays($offset);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'count' => (int) ($ridesByDay[$date->toDateString()] ?? 0),
            ];
        });
        $peak = max(1, $schedule->max('count'));
        $schedule = $schedule->map(fn ($day) => $day + [
            'height' => $day['count'] ? max(12, (int) round($day['count'] / $peak * 100)) : 3,
        ]);

        return view('index', [
            'overview' => $overview,
            'attention' => $attention,
            'upcomingRides' => $upcomingRides,
            'recentRequests' => $recentRequests,
            'schedule' => $schedule,
            'network' => [
                'rides' => Ride::count(),
                'drivers' => Driver::count(),
                'passengers' => Passenger::count(),
                'vehicles' => Vehicle::count(),
            ],
        ]);
    }
}
