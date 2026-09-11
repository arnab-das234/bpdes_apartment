<?php

namespace App\Livewire;

use Livewire\Component;

class LandingPage extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.landing-page')
            ->layout('components.layouts.app', ['title' => 'BPDES - Smart Society Management Platform']);
    }
}
