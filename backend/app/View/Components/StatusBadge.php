<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $text;
    public string $bg;
    public string $textColor;

    public function __construct(string $status, ?string $label = null)
    {
        $this->text = $label ?? ucwords(str_replace('_', ' ', $status));

        [$this->bg, $this->textColor] = match ($status) {
            'pending', 'driver_offered' => ['bg-status-pending-bg', 'text-status-pending-text'],
            'active', 'accepted' => ['bg-status-active-bg', 'text-status-active-text'],
            'completed', 'verified' => ['bg-status-completed-bg', 'text-status-completed-text'],
            'canceled', 'cancelled', 'passenger_canceled', 'ride_canceled', 'rejected' => ['bg-status-cancelled-bg', 'text-status-cancelled-text'],
            'expired', 'unverified' => ['bg-status-expired-bg', 'text-status-expired-text'],
            default => ['bg-gray-100', 'text-gray-600'],
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.status-badge');
    }
}
