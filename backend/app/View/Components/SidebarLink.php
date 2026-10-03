<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SidebarLink extends Component
{
    /**
     * Create a new component instance.
     */
    public string $href;
    public bool $active;
    public string $icon;

    public function __construct(string $route, string $icon = 'fa-solid fa-circle')
    {
        $this->href = route($route);
        $this->active = request()->routeIs($route) || request()->routeIs($route . '.*');
        $this->icon = $icon;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar-link');
    }
}
