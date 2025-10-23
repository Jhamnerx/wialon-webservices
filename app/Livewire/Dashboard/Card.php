<?php

namespace App\Livewire\DashBoard;


use App\Models\Config;
use Livewire\Component;

class Card extends Component
{
    public function render()
    {

        $data = Config::first()->counterServices->data;

        return view('livewire.dashboard.card', compact('data'));
    }

    public function actualizarVista()
    {
        $this->render();
    }
}
