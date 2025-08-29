<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class LandingPage extends Component
{

    public $plans = [];

    public function mount(){
        $this->plans = Product::all();
    }

    public function render()
    {
        return view('livewire.landing-page', [
            'plans' => $this->plans
        ]);
    }
}
