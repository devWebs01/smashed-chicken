<?php

namespace App\View\Components;

use Closure;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class App extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $setting = Setting::first();

        return view('livewire.layouts.app', compact('setting'));
    }
}
