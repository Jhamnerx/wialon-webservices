<?php

namespace App\Livewire\Logs;

use App\Models\Log;
use Livewire\Component;
use Livewire\Attributes\On;

class InfoLog extends Component
{

    public $showModal = false;
    public Log $log;

    public function render()
    {
        return view('livewire.logs.info-log');
    }

    #[On('open-modal-log')]
    public function openModal(Log $log)
    {
        $this->showModal = true;
        $this->log = $log;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function notifyClient($message)
    {
        $this->dispatch(
            'notify-toast',
            icon: 'success',
            title: 'COPIADO',
            mensaje: $message,
        );
    }
}
